<?php

function getRAR($connectionClass = 'command')
{
	static $rar;

	if (!is_object($rar))
		$rar = new $connectionClass();
		
	else if (!$rar instanceof $connectionClass)
		return false;

	return $rar;
}

?>
