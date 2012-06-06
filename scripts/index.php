<?php
// TODO implement this
header('Content-Type: application/javascript; charset=utf-8');

if(empty($_GET['lib']))
	exit('report(\'No library selected.\',5)');

if(!preg_match('/^[a-z]*$/', $_GET['lib']) ||
		!is_dir($_GET['lib'].'.js'))
	exit('report(\'Library "'.$_GET['lib'].'" not found\',5)');

foreach(scandir($_GET['lib'].'.js') as $file)
	readfile($_GET['lib'].'.js/'.$file);