<?php
if($template instanceof Template) {
	$content = new LanguageTemplate(SYS_UI_TMPL_DIR.'box/');
	$template->assign('#content', $content);
	$template->title = 'page höhö';
	$content->assignFromNew('content', 'a.xhtml', array('label'=>'This is a link back','PATH'=>'index'));
}