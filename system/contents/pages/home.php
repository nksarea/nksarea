<?php

if ($template instanceof Template)
{
	$projectTemplate = new Template(SYS_TEMPLATE_FOLDER . 'html/home.xhtml');
	$template->assign('#content', $projectTemplate);

	$template->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
	$template->addJS('scripts/pageScripts.js');

	$class = getUser()->data->class;
	if ($class == null)
		$class = 'bla';
	$projects = getDB()->query('getRecentProjects', array('accessLevel' => getUser()->data->access_level, 'id' => getUser()->data->id, 'class' => $class));
	$template->addCSS('styles/css/color-' . $projects->dataObj->color . '.css');
	do
	{
		$projectTemplate->assignFromNew('projects', 'list/project.xhtml', array('name' => $projects->dataObj->name, 'owner' => $projects->dataObj->owner, 'date' => $projects->dataObj->upload_time, 'color' => $projects->dataObj->color, 'icon' => $projects->dataObj->id . '.jpg'));
	}
	while ($projects->next());
}
?>