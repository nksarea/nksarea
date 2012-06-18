<?php
function createFilesList($files, $template, $path, $class = 'hidden')
{
	$content = new Template(SYS_UI_TMPL_DIR, 'project/filesTable.xhtml');
	$content->assign('path', $path);
	$content->assign('class', $class);
	if(count($files) == 0)
	{
		$content->assignFromNew('content', 'project/filesMessage.xhtml', array('message' => 'no Files'));
		$content->assign('height', '70px');
		$template->assign('filesTable', $content);
		return;
	}
	
	$content->assign('height', count($files) * 29 . 'px');
	foreach ($files as $key => $value)
	{
		if(is_array($value))
		{
			$content->assignFromNew('content', 'project/filesRowDir.xhtml', array('name' => (string)$key, 'date' => '20.12.2012', 'link' => $path . $key . '/'));
			createFilesList($value, $template, $path . $key . '/');
			continue;
		}
		$content->assignFromNew('content', 'project/filesRowFile.xhtml', array('file' => $key . $value,'name' => $key, 'size' => '37`478 KB', 'date' => '20.12.2012'));
	}
	$template->assign('filesTable', $content);
}
?>
