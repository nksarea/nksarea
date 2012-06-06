<?php
function search($order_by, $order_desc, $filter)
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
	$query['user'] = getUser()->id;
	$query['class'] = getUser()->class;
	$query['userAL'] = getUser()->access_level;

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
?>
