<?php

class file extends base
{

	private $user;
	private $data;
	private $fid;

	public function __construct($user, $fid)
	{
//		if (get_class($user) != 'User')
//			return $this->throwError('$user was not an instance of "User"');

		$this->user = $user;
		$this->fid = $fid;

		$this->data = getDB()->query('refreshData', array('pid' => $this->pid));
		$this->data = $this->data->dataObj;

		$ok = $user->data->class == $this->data->class
				|| $user->access_level === 0
				|| $user->data->id == $this->data->owner;

		if ($ok)
			return $this->throwError('$user has no access to the project');;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'fid':
				return $this->$name;
				break;
			case 'owner':
			case 'name':
			case 'upload_time':
			case 'list':
			case 'mime':
			case 'class':
				return $this->data->$name;
				break;
		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'owner':
			case 'name':
			case 'mime':
				getDB()->query('setFile', array('id' => $this->fid, 'field' => $name, 'value' => $value));
				$this->data->$name = $value;
				break;
			case 'list':
				getDB()->query('setFile', array('id' => $this->fid, 'field' => $name, 'value' => $value));
				$this->data = getDB()->query('refreshData', array('pid' => $this->pid));
				$this->data = $this->data->dataObj;
				break;
			default:
				return;
		}
	}
	static function addFile($name, $list, $file)
	{
		$query = array();
		
		if(!is_string($name))
			$this->throwError ('$name isn`t a string', $name);
		if(!is_file(SYS_TMP . $file))
			$this->throwError ('$file isn`t a file', $file);

		$query['name'] = $name;
		$query['owner'] = intval($this->user->id);
		$query['list'] = intval($list);
		$query['mime'] = mime_content_type($file);
		
		if (getDB()->query('addProject', $query))
			$this->throwError('MYSQLi hates you!');

		return getRAR()->execute('moveFile', array('source' => SYS_TMP . $file, 'destination' => SYS_SHARE_PROJECTS . getDB()->insert_id));
	}
	function removeFile()
	{
		//@todo
	}
}

?>
