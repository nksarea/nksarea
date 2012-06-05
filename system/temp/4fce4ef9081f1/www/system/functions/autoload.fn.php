<?php
function __autoload($name) {
	if(preg_replace('/[a-z0-9]*/si', '', $name))
		return false;
	if(file_exists('system/classes/'.$name.'.class.php'))
		include_once 'system/classes/'.$name.'.class.php';
	else if(file_exists('system/classes/'.$name.'.php'))
		include_once 'system/classes/'.$name.'.php';
}