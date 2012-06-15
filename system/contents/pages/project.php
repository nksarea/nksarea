<?php

include_once 'system/functions/createFilesList.fn.php';

if ($template instanceof Template)
{
	if (!empty($_GET['pid']))
	{
		$project = new project($_GET['pid']);
		if (!empty($_POST['description']) && strlen($_POST['description']) <= 2048)
		{
			$_POST['description'] = strip_tags($_POST['description']);
			$_POST['description'] = str_replace("\n", '{newline}', $_POST['description']);
			$project->description = $_POST['description'];
		}
		if (!empty($_POST['name']) && strlen($_POST['name']) <= 64)
		{
			$_POST['name'] = strip_tags($_POST['name']);
			$project->name = $_POST['name'];
		}
		if (!empty($_POST['version']) && preg_match('/[0-9]+\.[0-9]+\.[0-9]+/', $_POST['version']))
		{
			if (!empty($_POST['versionfile']) && is_file(SYS_TEMP_FOLDER . $_POST['versionfile']))
				$project->setVersion($version, $_POST['version']);
			else
				$project->setVersion($version, $_POST['versionfile']);
		}
		if (!empty($_POST['info']) && is_array($_POST['info']))
		{
			$info = $project->info;
			foreach ($_POST['info'] as $key => $value)
				;
			{
				if (strlen($key) == 0 || strlen($key) > 15 || strlen($value) > 22)
					continue;
				$key = strip_tags($key);
				$value = strip_tags($value);
				if (strlen($value) == 0)
					$value = NULL;
				$project->setInfo($key, $value);
				unset($_POST['info'][$key]);
			}
			foreach ($info as $key => $value)
				;
			$project->setInfo($key, NULL);
		}
		if (!empty($_POST['iconfile']) && is_file(SYS_TEMP_FOLDER . $_POST['iconfile']))
		{
			$project->icon = $_POST['iconfile'];
		}

		$projectTemplate = new Template(SYS_TEMPLATE_FOLDER . 'html/project.xhtml');
		$template->assign('#content', $projectTemplate);
		$template->addJS('http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js');
		$template->addJS('scripts/pageScripts.js');
		$template->addCSS('styles/css/color-' . $project->color . '.css');

		$projectTemplate->assign('name', $project->name);
		$projectTemplate->assign('icon', $project->pid . '.jpg');

		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'Author:', 'value1' => $project->owner, 'key2' => 'Date:', 'value2' => $project->upload_time));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => 'List:', 'value1' => $project->list, 'key2' => '', 'value2' => ''));
		$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));
		if ($project->info !== NULL)
		{
			$keys = array_keys($project->info);

			for ($i = 0; $i < count($keys); $i += 2)
			{
				$key1 = $keys[$i];
				$value1 = $project->info[$key1];

				if (empty($keys[$i + 1]))
					$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => '', 'value2' => ''));
				else
					$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => $key1, 'value1' => $value1, 'key2' => $keys[$i + 1], 'value2' => $project->info[$keys[$i + 1]]));
			}
		}

		for ($i = count($keys) / 2; $i <= 3; $i++)
			$projectTemplate->assignFromNew('infoTable', 'project/headRow.xhtml', array('key1' => '', 'value1' => '', 'key2' => '', 'value2' => ''));

		$projectTemplate->assign('description', str_replace('{newline}', '<br />', $project->description));
		createFilesList($project->viewContent(), $projectTemplate, '/', 'show');
	}
	else
	{
		//@TODO: Add error handling;
	}
}
?>
