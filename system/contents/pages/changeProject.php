<?php

/*
 * Speichert Änderungen am Projekt und leitet auf die Projektseite zurück
 *
 * @author Cédric Neukom
 */
if ($template instanceof Template)
{
	// Projektinstanz erzeuten, um Änderungen zu speichern
	$project = new project($_GET['pid']);

	// Kommentare entfernen
	if ($_POST['removeComment'])
	{
		$comments = new CommentList(CommentList::TYPE_PROJECT, $_GET['pid']);
		$comments->removeComments($_POST['removeComment']);
	}

	if (!empty($_POST['description']) && strlen($_POST['description']) <= 2048)
	{
		$_POST['description'] = strip_tags($_POST['description']);
		$_POST['description'] = str_replace("\n", '!newline!', $_POST['description']);
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
			$project->setVersion($_POST['version'], $_POST['versionfile']);
		else
			$project->setVersion($_POST['version']);
	}
	if (!empty($_POST['info']) && is_array($_POST['info']))
	{
		$info = $project->info;
		foreach ($_POST['info'] as $key => $value)
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
			$project->setInfo($key, NULL);
	}
	if (!empty($_POST['iconfile']) && is_file(SYS_TEMP_FOLDER . $_POST['iconfile']))
	{
		$project->icon = $_POST['iconfile'];
	}

	// Projektseite anzeigen
	include(SYS_CNT_DIR . 'project.php');
}