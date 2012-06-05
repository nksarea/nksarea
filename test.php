<?php
include_once 'system/config.php';
include_once 'system/functions/autoload.fn.php';
include_once 'system/functions/getDB.fn.php';
include_once 'system/functions/getRAR.fn.php';
include_once 'system/functions/getUser.fn.php';
include_once 'system/functions/getMethods.fn.php';

$warning = array();

var_dump(getDB()->query('refreshDataF', array('fid' => 1))->dataObj);

var_dump($warning);
?>
