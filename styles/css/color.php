<?php
include_once '../../system/config.php';
include_once '../../system/classes/base.class.php';

if(!empty($_GET['color']))
{
	header('Content-Type: text/css');
	$base = new base();
	echo $base->template('color.css', array('color' => $_GET['color']));

}
