<?php
if ($template instanceof Template)
{
	if (!empty($_GET['pid']))
	{
		$project = new project($_GET['pid']);
		$projectTemplate = new Template(SYS_TEMPLATE_FOLDER.'html/editProject.xhtml');
		$template->assign('#content', $projectTemplate);

		$projectTemplate->assign('projectName', $project->name);
		$projectTemplate->assign('projectIcon', $project->pid . '.jpg');
		$template->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
		$template->addJS('scripts/editProject.js');
		$template->addCSS('styles/css/color-' . $project->color . '.css');

		$projectTemplate->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => 'Author:', 'value1' => $project->owner, 'key2' => 'Date:', 'value2' => $project->upload_time));
		$projectTemplate->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => 'List:', 'value1' => $project->list, 'key2' => '', 'value2' => ''));
		$projectTemplate->assignFromNew('projectHeadContent', 'box/projectHeadRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));
		$keys = array_keys($project->info);
		
		for($i = 0; $i < count($keys); $i += 2)
		{
			$key1 = $keys[$i];
			$value1 = $project->info[$key1];
			
			if(empty($keys[$i + 1]))
				$projectTemplate->assignFromNew('projectHeadContent', 'box/editProjectHeadRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => '', 'value2' => ''));
			else
				$projectTemplate->assignFromNew('projectHeadContent', 'box/editProjectHeadRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => $keys[$i + 1], 'value2' => $project->info[$keys[$i + 1]]));
		}

		for($i = count($keys)/2; $i <= 3; $i ++)
			$projectTemplate->assignFromNew('projectHeadContent', 'box/editProjectHeadRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));
		
		foreach($project->versions as $value)
			$projectTemplate->assignFromNew('versions', 'box/option.xhtml', array('value' => $value));
		
		$projectTemplate->assign('version', $project->version);
		$projectTemplate->assign('projectDescriptionContent', $project->description);
	}
	else
	{
		//@TODO: Add error handling;
	}
}
?>
