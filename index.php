<?php
	require_once 'autoload.fn.php';
	require_once 'getDB.fn.php';
	require_once 'getRAR.fn.php';

	define('SYS_PROJECT_FOLDER', 'C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\projects\\');
	define('SYS_DOWNLOAD_FOLDER', 'C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\downloads\\');
	define('SYS_UPLOAD_FOLDER', 'C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\uploads\\');
	define('SYS_TEMPLATE_FOLDER', 'C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\templates\\');
	define('SYS_PLUGIN_FOLDER', 'C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\classes\plugins\\');
	define('SYS_TEMP_FOLDER', 'C:\Users\Lorze\SkyDrive\Programming\php\nksarea1\temp\\');
	define('TEST_MODE', true);
	
	$warning = array();
	
	$user->id = 3;
	$user->access_level = 0;
	$user->class = 40;
	
	getRAR()->execute('viewContent', array('path' => SYS_PROJECT_FOLDER . '15.rar'));
	
	var_dump($warning);
?>