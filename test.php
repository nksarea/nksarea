<?php
include_once 'system/config.php';
include_once 'system/functions/autoload.fn.php';
include_once 'system/functions/getDB.fn.php';
include_once 'system/functions/getRAR.fn.php';
include_once 'system/functions/getUser.fn.php';
include_once 'system/functions/getMethods.fn.php';

$warning = array();

$user->id = 3;
$user->access_level = 0;
$user->class = 40;

getRAR()->execute('viewContent', array('path' => SYS_SHARE_PROJECTS . '15.rar'));

var_dump($warning);
?>
