<?php

if ($template instanceof Template)
{
	$projectTemplate = new Template(SYS_TEMPLATE_FOLDER . 'html/home.xhtml');
	$template->assign('#content', $projectTemplate);

	$projects = getDB()->query('getRecentProjects', array('accessLevel' => getUser()?getUser()->data->access_level:3, 'id' => getUser()?getUser()->data->id:0, 'class' => getUser()?getUser()->data->class:0));
	//$template->addCSS('styles/css/color-' . $projects->dataObj->color . '.css');
	do
	{
		$projectTemplate->assignFromNew('projects', 'list/project.xhtml', array('name' => $projects->dataObj->name, 'owner' => $projects->dataObj->owner, 'date' => $projects->dataObj->upload_time, 'color' => $projects->dataObj->color, 'icon' => $projects->dataObj->id . '.jpg', 'id' => $projects->dataObj->id));
	}
	while ($projects->next());
}
?>