<?php
if($template instanceof Template) {
	header('HTTP/1.1 404 Not Found');
	$err = new Template(SYS_TEMPLATE_FOLDER.'html/error.xhtml');
	$err->assign('errNo', '404');
	$err->assign('error', 'Not found');
	$err->assign('errDesc', 'The requested page was not found.');

	$template->assign('#content', $err);
}