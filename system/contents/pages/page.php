<?php
if($template instanceof Template) {
	$content = new Template(SYS_UI_TMPL_DIR.'box/');
	$template->assign('#content', $content);
	$content->assign('content', '<div data-fda-submit="test.php" style="width:100px;height:100px;border:1px solid #000;"></div>');
}