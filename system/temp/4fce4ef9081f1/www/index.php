<?php
include_once 'system/config.php';
include_once 'system/functions/autoload.fn.php';
include_once 'system/functions/myc.fn.php';
$user = new User($myc);
$t = new Template('system/template/fw.xhtml');
// Test:
// TODO: entfernten
include 'test.php';
// :Test
$myc->close();