<?php

define('ORDER_OWNER', 1);
define('ORDER_NAME', 2);
define('ORDER_LIST', 3);
define('ORDER_UPTIME', 4);

class project extends base
{

	private $user;
	private $data;
	private $pid;

	public function __construct($user, $pid)
	{
		$this->user = $user;
		$this->pid = $pid;

		$this->data = getDB()->query('refreshDataP', array('pid' => $this->pid));
		if($this->data->dataLength === null)
			$this->throwError('No project with given pid exists', $this->pid);
		$this->data = $this->data->dataObj;

		$fail = (($this->data->access_level == 0) ||
				($this->data->access_level == 1 && $this->user->access_level == 1) ||
				($this->data->access_level <= 2 && $this->data->class == $this->user->class && $this->user->access_level == 2)  ||
				($this->data->access_level <= 2 && $this->user->access_level == null)) && $this->data->owner != $this->user->id && $this->user->access_level != 0;

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
		if($user->data->id != $this->data->owner)
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

			var_dump(getRAR()->execute('prepareDownload', array('path' => SYS_SHARE_PROJECTS . $this->pid . '.rar', 'mask' => $mask, 'name' => $name, 'destination' => $path)));
			return array('path' => $path, 'name' => $name);
		}
	}

	public function removeProject()
	{
		//@todo
	}

	public static function getPid($order_by, $order_desc, $filter, $user)
	{
		if (!is_bool($order_desc))
			$this->throwError('$order_desc isn`t a bool');
		if (!is_array($filter))
			$this->throwError('$filter isn`t an array');

		switch ($order_by)
		{
			case ORDER_NAME:
				$query['order'] = 'name';
				break;
			case ORDER_OWNER:
				$query['order'] = 'owner';
				break;
			case ORDER_LIST:
				$query['order'] = 'list';
				break;
			case ORDER_UPTIME:
				$query['order'] = 'upload_time';
				break;
			default:
				$query['order'] = 'name';
				break;
		}

		$query['desc'] = '';
		$query['filter'] = $filter;
		$query['user'] = $user->id;
		$query['class'] = $user->class;
		$query['userAL'] = $user->access_level;

		if ($order_desc)
			$query['desc'] = 'DESC';

		if (!$query = getDB()->query('getPid', $query))
			return false;

		do
		{
			$result[] = $query->dataArray[0];
		}
		while ($query->next());

		return $result;
	}

	public static function addProject($user, $folder, $name, $access_level, $description = NULL, $list = NULL)
	{
		$access_level = intval($access_level);
		$query = array();

		if (!is_dir(SYS_TMP . $folder))
			$this->throwError('$folder isn`t a folder');
		if (!is_string($name))
			$this->throwError('$name isn`t a string');
		if ($access_level < 0 && $acces_level > 4)
			$this->throwError('$access_level isn`t valid');
		if (!is_string($description) && $description != NULL)
			$this->throwError('$description isn`t a string nor NULL');
		if (!is_int($list) && $list != NULL)
			$this->throwError('$list isn`t a string nor NULL');

		$query['owner'] = intval($user->id);
		$query['name'] = $name;
		$query['description'] = ($description === null ? 'NULL' : $description);
		$query['access_level'] = $access_level;
		$query['list'] = ($list === null ? 'NULL' : intval($list));

		$result = getDB()->query('addProject', $query);
		if (!$result)
			$this->throwError('MYSQLi hates you!');

		return getRAR()->execute('packProject', array('source' => SYS_TMP . $folder, 'destination' => SYS_SHARE_PROJECTS . getDB()->insert_id . '.rar'));
	}

}
