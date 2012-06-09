<?php
function createFilesList($files, $template, $path)
{
	$content = new Template(SYS_UI_TMPL_DIR, 'box/projectFilesTable.xhtml');
	$content->assign('height', count($files) * 28 . 'px');
	$content->assign('path', $path);
	foreach ($files as $key => $value)
	{
		if(is_array($value))
		{
			$content->assignFromNew('content', 'box/projectFilesRowDir.xhtml', array('name' => $key, 'date' => '20.12.2012', 'link' => $path . $key . '/'));
			createFilesList($value, $template, $path . $key . '/');
			continue;
		}
		$content->assignFromNew('content', 'box/projectFilesRowFile.xhtml', array('name' => $key, 'size' => '37`478 KB', 'date' => '20.12.2012'));
	}
	$template->assign('projectFilesTable', $content);
}
?>
