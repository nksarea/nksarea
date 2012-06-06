<?php

define('ORDER_OWNER', 1);
define('ORDER_NAME', 2);
define('ORDER_LIST', 3);
define('ORDER_UPTIME', 4);

class project extends base
{
	private $data;
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
}
