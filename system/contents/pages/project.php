<?php
include_once 'system/functions/createFilesList.fn.php';

if ($template instanceof Template)
{
	if (!empty($_GET['pid']))
	{
		$project = new project($_GET['pid']);
		$template->assign('projectName', $project->name);
		$template->assign('projectIcon', $project->pid . '.jpg');
		$template->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
		$template->addJS('scripts/project.js');
		$template->addCSS('styles/css/color-' . $project->color . '.css');

		$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => 'Author:', 'value1' => $project->owner, 'key2' => 'Date:', 'value2' => $project->upload_time));
		$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => 'List:', 'value1' => $project->list, 'key2' => '', 'value2' => ''));
		$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));
		$keys = array_keys($project->info);
		
		for($i = 0; $i < count($keys); $i += 2)
		{
			$key1 = $keys[$i];
			$value1 = $project->info[$key1];
			
			if(empty($keys[$i + 1]))
				$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => '', 'value2' => ''));
			else
				$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => $keys[$i + 1], 'value2' => $project->info[$keys[$i + 1]]));
		}

		for($i = count($keys)/2; $i <= 3; $i ++)
			$template->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));
		
		$template->assign('projectDescriptionContent', $project->description);
		createFilesList($project->viewContent(), $template, '/', 'show');
		
	}
	else
	{
		//@TODO: Add error handling;
	}
}
?>
