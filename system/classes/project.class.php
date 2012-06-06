<?php

define('ORDER_OWNER', 1);
define('ORDER_NAME', 2);
define('ORDER_LIST', 3);
define('ORDER_UPTIME', 4);

class project extends base
{
	private $data;
	private $versions;
	private $pid;

	public function __construct($pid)
	{
		$this->pid = $pid;

		$this->data = getDB()->query('refreshDataP', array('pid' => $this->pid));
		if($this->data->dataLength === null)
			$this->throwError('No project with given pid exists', $this->pid);
		$this->data = $this->data->dataObj;

		$fail = (($this->data->access_level == 0) ||
				($this->data->access_level == 1 && getUser()->access_level == 1) ||
				($this->data->access_level <= 2 && $this->data->class == getUser()->class && getUser()->access_level == 2)  ||
				($this->data->access_level <= 2 && getUser()->access_level == null)) && $this->data->owner != getUser()->id && getUser()->access_level != 0;

		if ($fail)
			return $this->throwWarning('$user has no access to the project');
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'pid':
				return $this->$name;
				break;
			case 'owner':
			case 'name':
			case 'version':
			case 'description':
			case 'access_level':
			case 'upload_time':
			case 'list':
				return $this->data->$name;
				break;
		}
	}

	public function __set($name, $value)
	{
		if(getUser()->data->id != $this->data->owner && getUser()->data->access_level != 0)
			return;
		
		switch ($name)
		{
			case 'name':
			case 'description':
			case 'access_level':
			case 'list':
				getDB()->query('setProject', array('id' => $this->pid, 'field' => $name, 'value' => $value));
				$this->$name = $value;
				break;
			case 'owner':
				getDB()->query('setProject', array('id' => $this->pid, 'field' => $name, 'value' => $value));
				$this->data = getDB()->query('refreshDataP', array('pid' => $this->pid));
				$this->data = $this->data->dataObj;
				break;
			default:
				return;
		}
	}

	public function viewContent()
	{
		if (!is_file(SYS_SHARE_PROJECTS . $this->pid . '.rar'))
			$this->throwError('there is no RAR data for the project');

		getRAR()->execute('viewContent', array('path' => SYS_SHARE_PROJECTS . $this->pid . '.rar'));
		return getRAR()->plugin('rar_parseContent');
	}

	public function prepareDownload($mask = false)
	{
		if (!is_file(SYS_SHARE_PROJECTS . $this->pid . '.rar'))
			$this->throwError('there is no RAR data for the project');
		if (!is_string($mask) && !is_bool($mask))
			$this->throwError('$filter is not a bool nor a string');

		if ($mask == false)
		{
			return array('path' => SYS_SHARE_PROJECTS . $this->pid . '.rar', 'name' => $this->data->name);
		}
		else
		{
			$path = SYS_TMP . uniqid();
			$name = explode('\\', $mask);
			$name = $name[count($name) - 1];

			getRAR()->execute('prepareDownload', array('path' => SYS_SHARE_PROJECTS . $this->pid . '.rar', 'mask' => $mask, 'name' => $name, 'destination' => $path));
			return array('path' => $path, 'name' => $name);
		}
	}

	public function removeProject()
	{
		//@todo
	}
	public function setVersion($version, $folder = false)
	{
		$versionFile = SYS_SHARE_PROJECTS . $this->pid . '-v' . $version;
	
		if(!is_file($versionFile) && ($folder === false || !is_file(SYS_TMP . $folder)))
			$this->throwError ('$version isn`t currently present');	
		if(rename(SYS_SHARE_PROJECTS . $pid . '.rar', SYS_SHARE_PROJECTS . $pid . '-v' . $this->data->version))
			$this->throwError ('couldn`t rename file');
		
		if(!is_file($versionFile))
		{
			getRAR()->execute('packProject', array('source' => SYS_TMP . $folder, 'destination' => $versionFile));
			$this->versions[] = $version;
//			Sorting array ->
		}
		
		$this->data->version = $version;
		getDB()->query('setProject', array('id' => $this->pid, 'field' => 'version', 'value' => $version));

		if(rename($versionFile, SYS_SHARE_PROJECTS . $pid . '.rar'))
			$this->throwError ('couldn`t rename file');
		return true;
	}
	
	public function getVersions()
	{
		if(is_array($this->versions))
			return $this->versions;

		$this->versions = array($this->data->version);
		
		$dir = opendir(SYS_SHARE_PROJECTS);
		while (($file = readdir($dir)) !== false)
		{
			if(strpos($file, $this->pid . '-v') !== 0)
				continue;
			
			$this->versions[] = str_replace($this->pid . '-v', '', $file);
		}
		closedir($dir);
		
//		Sorting array ->
		return $this->versions;
	}
}
