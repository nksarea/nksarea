<?php
include_once 'system/functions/createFilesList.fn.php';

if ($template instanceof Template)
{
	if (!empty($_GET['pid']))
	{
		$project = new project($_GET['pid']);
		$template->assign('projectName', $project->name);
		$template->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
		$template->addJS('scripts/project.js');
		$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => 'Author:', 'value1' => $project->owner, 'key2' => 'Date:', 'value2' => $project->upload_time));
		$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => 'List:', 'value1' => $project->list, 'key2' => '', 'value2' => ''));
		for($i = 1; $i <= 5; $i ++)
			$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));
		
		$template->assign('projectDescriptionContent', $project->description);
		createFilesList($project->viewContent(), $template, '/');
		
	}
	else
	{
		//@TODO: Add error handling;
	}
}
?>
