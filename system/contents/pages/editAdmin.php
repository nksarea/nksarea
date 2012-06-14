<?php
if ($template instanceof Template)
{
	if (getUser()->loggedin === true)
	{
		$projectTemplate = new Template(SYS_TEMPLATE_FOLDER . 'html/editAdmin.xhtml');
		$template->assign('#content', $projectTemplate);

		$template->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
		$template->addJS('scripts/pageScripts.js');
		$template->addCSS('styles/css/color-190.css');

		$projectTemplate->assign('name', getUser()->data->name);

		$projectTemplate->assignFromNew('infoTable', 'user/headRow.xhtml', array('key1' => 'E-mail:', 'value1' => (string) getUser()->data->email, 'data-input1' =>  'email', 'key2' => 'Name:', 'value2' => (string) getUser()->data->realname, 'data-input2' => 'realname'));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'Account:', 'value1' => (string) getUser()->data->access_level, 'key2' => 'Class:', 'value2' => (string) getUser()->data->class));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'Registration:', 'value1' => (string) getUser()->data->registrated, 'key2' => 'Activity:', 'value2' => getUser()->data->last_activity));

		$projects = getDB()->query('getUserProjects', array('id' => getUser()->data->id));
		if ($projects !== NULL)
		{
			do
			{
				$projectTemplate->assignFromNew('projects', 'list/project.xhtml', array('name' => $projects->dataObj->name, 'owner' => $projects->dataObj->owner, 'date' => $projects->dataObj->upload_time, 'color' => $projects->dataObj->color, 'icon' => $projects->dataObj->id . '.jpg'));
			}
			while ($projects->next());
		}


		$lists = getDB()->query('getUserLists', array('id' => getUser()->data->id));
		if ($lists !== NULL)
		{
			do
			{
				$projectTemplate->assignFromNew('lists', 'list/list.xhtml', array('name' => (string) $lists->dataObj->name, 'type' => (string) $lists->dataObj->owner, 'date' => (string) $lists->dataObj->creation_time));
			}
			while ($lists->next());
		}
	}
	else
	{
		//@TODO: Add error handling;
	}
}
?>
