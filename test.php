<?php
include_once 'system/config.php';
include_once 'system/functions/autoload.fn.php';
include_once 'system/functions/getDB.fn.php';
include_once 'system/functions/getRAR.fn.php';
include_once 'system/functions/getUser.fn.php';
include_once 'system/functions/getMethods.fn.php';

$warning = array();

<<<<<<< HEAD
var_dump(getUser('admin', 'test'));
//var_dump(getDB()->query('refreshDataF', array('fid' => 1))->dataObj);

var_dump($warning);
?>
=======
var_dump(getDB());
>>>>>>> 52234e9b8f2d17f7950a69f8335135b11a19bbf1
