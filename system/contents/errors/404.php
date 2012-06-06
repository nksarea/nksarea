<?php
if($template instanceof Template) {
	header('HTTP/1.1 404 Not Found');

	$content = new LanguageTemplate(SYS_UI_TMPL_DIR.'box/');
	$content->assignFromNew('content', 'h1.xhtml', array('content'=>'Not found'));
	$content->assignFromNew('content', 'paragraph.xhtml', array('content'=>'The requested file couldn\'t be found.'));

	$template->title = 'Error 404 - '.SYS_NAME;
	$template->assign('#content', $content);
}