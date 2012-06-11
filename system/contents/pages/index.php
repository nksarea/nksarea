<?php
if($template instanceof Template) {
	$template->title = 'Home page';
	$content = new Template(SYS_UI_TMPL_DIR.'box/');
	$template->assign('#content', $content);

	$content->assignFromNew('content', 'h1.xhtml', array('content'=>'Welcome to '.SYS_NAME));
	$content->assignFromNew('content', 'paragraph.xhtml', array('content'=>'We love to project you.'));
}