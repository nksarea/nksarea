<?php

define('ORDER_OWNER', 1);
define('ORDER_NAME', 2);
define('ORDER_LIST', 3);
define('ORDER_UPTIME', 4);

class project extends base
{

	private $data;
	private $versions;
	private $info;
	private $pid;

	public function __construct($pid)
	{
		$this->pid = $pid;

		$this->data = getDB()->query('refreshDataP', array('pid' => $this->pid));
		if ($this->data === null)
			$this->throwError('No project with given pid exists', $this->pid);
		$this->data = $this->data->dataObj;

		$temp = getDB()->query('refreshDataProjectInfo', array('pid' => $this->pid));
		$this->info = $temp;
		if ($temp !== null)
		{
			$this->info = array();
			do
				$this->info[$temp->dataObj->key] = $temp->dataObj->value;
			while ($temp->next());
		}

		$fail = (($this->data->access_level == 0) ||
				($this->data->access_level == 1 && getUser()->access_level == 1) ||
				($this->data->access_level <= 2 && $this->data->class == getUser()->class && getUser()->access_level == 2) ||
				($this->data->access_level <= 2 && getUser()->access_level == null)) && $this->data->owner != getUser()->id && getUser()->access_level != 0;

		if ($fail)
			return $this->throwWarning('$user has no access to the project');
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'pid':
			case 'info':
				return $this->$name;
				break;
			case 'owner':
			case 'name':
			case 'version':
			case 'color':
			case 'description':
			case 'access_level':
			case 'upload_time':
			case 'list':
				return $this->data->$name;
				break;
			case 'versions':
				return $this->getVersions();
				break;
		}
	}

	public function __set($name, $value)
	{
		if (getUser()->data->id != $this->data->owner && getUser()->data->access_level != 0)
			return;

		switch ($name)
		{
			case 'name':
			case 'description':
			case 'access_level':
			case 'list':
				getDB()->query('setProject', array('id' => $this->pid, 'field' => $name, 'value' => $value));
				$this->data->$name = $value;
				break;
			case 'owner':
				getDB()->query('setProject', array('id' => $this->pid, 'field' => $name, 'value' => $value));
				$this->data = getDB()->query('refreshDataP', array('pid' => $this->pid));
				$this->data = $this->data->dataObj;
				break;
			case 'icon':
				$this->setIcon($value);
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
		if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $version))
			$this->throwError('$version isn`t a version number', $version);

		$versionFile = SYS_SHARE_PROJECTS . $this->pid . '-v' . $version;

		if (!rename(SYS_SHARE_PROJECTS . $this->pid . '.rar', SYS_SHARE_PROJECTS . $this->pid . '-v' . $this->data->version))
			$this->throwError('couldn`t rename file');

		if (!is_file($versionFile))
		{
			if (!is_dir(SYS_TMP . $folder))
				$this->throwError('$version isn`t currently present');
			getRAR()->execute('packProject', array('source' => SYS_TMP . $folder, 'destination' => $versionFile));
			$this->versions[] = $version;
//			Sorting array ->
		}

		$this->data->version = $version;
		getDB()->query('setProject', array('id' => $this->pid, 'field' => 'version', 'value' => $version));

		if (!rename($versionFile, SYS_SHARE_PROJECTS . $this->pid . '.rar'))
			$this->throwError('couldn`t rename file');
		return true;
	}

	public function setInfo($key, $value)
	{
		if (!is_string($key))
			$this->throwError('$key isn`t a string', $key);
		if (!is_string($value) && $value !== NULL)
			$this->throwError('$value isn`t a string nor NULL', $value);

		if ($value === NULL)
		{
			getDB()->query('removeProjectInfo', array('pid' => $this->pid, 'key' => $key));
			if (getDB()->affected_rows === 0)
				return false;
			unset($this->info[$key]);
			return true;
		}
		else if (!is_array($this->info) || empty($this->info[$key]))
			getDB()->query('addProjectInfo', array('pid' => $this->pid, 'key' => $key, 'value' => $value));
		else
			getDB()->query('setProjectInfo', array('pid' => $this->pid, 'key' => $key, 'value' => $value));
		$this->info[$key] = $value;
		return true;
	}

	private function getVersions()
	{
		if (is_array($this->versions))
			return $this->versions;

		$this->versions = array($this->data->version);

		$dir = opendir(SYS_SHARE_PROJECTS);
		while (($file = readdir($dir)) !== false)
		{
			if (strpos($file, $this->pid . '-v') !== 0)
				continue;

			$this->versions[] = str_replace($this->pid . '-v', '', $file);
		}
		closedir($dir);

//		Sorting array ->
		return $this->versions;
	}

	private function setIcon($file)
	{
		if (!is_file(SYS_TMP . $file))
			$this->throwError('$file isn`t a file');
		if (!$type = getimagesize(SYS_TMP . $file))
			$this->throwError('GD couldn`t read this image file');

		switch ($type)
		{
			case "1":
				$imorig = imagecreatefromgif(SYS_TMP . $file);
				break;
			case "2":
				$imorig = imagecreatefromjpeg(SYS_TMP . $file);
				break;
			case "3":
				$imorig = imagecreatefrompng(SYS_TMP . $file);
				break;
			default:
				$imorig = imagecreatefromjpeg(SYS_TMP . $file);
		}

		$width = imagesx($imorig);
		$height = imagesy($imorig);

		$im = imagecreatetruecolor(1, 1);
		imagecopyresampled($im, $imorig, 0, 0, 0, 0, 1, 1, $width, $height);

		$rgb = imagecolorat($im, 0, 0);
		$rgb = imagecolorsforindex($im, $rgb);

		$r = $rgb['red'] / 255;
		$g = $rgb['green'] / 255;
		$b = $rgb['blue'] / 255;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);

		switch ($max)
		{
			case 0:
				$h = 0;
				break;
			case $min:
				$h = 0;
				break;
			default:
				$delta = $max - $min;

				if ($r == $max)
					$h = 0 + ( $g - $b ) / $delta;
				else if ($g == $max)
					$h = 2 + ( $b - $r ) / $delta;
				else
					$h = 4 + ( $r - $g ) / $delta;

				$h *= 60;
				if ($h < 0)
					$h += 360;
		}
		$im = imagecreatetruecolor(196, 196);
		imagecopyresampled($im, $imorig, 0, 0, 0, 0, 196, 196, $width, $height);
		if (!imagejpeg($im, SYS_ICON_FOLDER . $this->pid . '.jpg'))
			$this->throwError('even your filesystem hates you and won`t save your image');

		getDB()->query('setProject', array('id' => $this->pid, 'field' => 'color', 'value' => $h));
		$this->data->color = $h;
	}

}
