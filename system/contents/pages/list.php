<?php
if ($template instanceof Template)
{
	if (!empty($_GET['lid']))
	{
		$list = new elist($_GET['lid']);
		$projectTemplate = new Template(SYS_TEMPLATE_FOLDER.'html/list.xhtml');
		$template->assign('#content', $projectTemplate);

		$template->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
		$template->addJS('scripts/pageScripts.js');
		$template->addCSS('styles/css/color-150.css');
		
		$projectTemplate->assign('name', $list->data->name);
		
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'Creator:', 'value1' => (string)$list->data->owner, 'key2' => 'Date:', 'value2' => (string)$list->data->creation_time));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'Class:', 'value1' => (string)$list->data->class, 'key2' => 'Type', 'value2' => (string)$list->type));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'Deadline:', 'value1' => (string)$list->data->deadline, 'key2' => '', 'value2' => ''));
		
		$projects = getDB()->query('getProjectsList', array('list' => $_GET['lid']));
		$deadline = new DateTime($list->data->deadline);
		do
		{
			$upload_time = new DateTime($projects->dataObj->upload_time);
			if($deadline >= $upload_time)
				$projectTemplate->assignFromNew('projects', 'list/project.xhtml', array('name' => $projects->dataObj->name, 'owner' => $projects->dataObj->owner, 'date' => $projects->dataObj->upload_time, 'color' => $projects->dataObj->color, 'icon' => $projects->dataObj->id . '.jpg'));
			else
				$projectTemplate->assignFromNew('projects', 'list/projectFail.xhtml', array('name' => $projects->dataObj->name, 'owner' => $projects->dataObj->owner, 'date' => $projects->dataObj->upload_time, 'icon' => $projects->dataObj->id . '.jpg'));
		} while ($projects->next());
	}
	else
	{
		//@TODO: Add error handling;
	}
}
?>