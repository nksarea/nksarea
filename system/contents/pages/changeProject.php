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

	print_r($_POST);
	
	// Kommentare entfernen
	if (!empty($_POST['removeComment']) && is_array($_POST['removeComment'])) 
	{
		$comments = new CommentList(CommentList::TYPE_PROJECT, $_GET['pid']);
		$comments->removeComments($_POST['removeComment']);
	}

	//Beschreibung ändern
	if (!empty($_POST['description']) && strlen($_POST['description']) <= 2048)
	{
		$_POST['description'] = strip_tags($_POST['description']);
		$_POST['description'] = str_replace("\n", '!newline!', $_POST['description']);
		$project->description = $_POST['description'];
	}
	
	//Projekt Name ändern ändern
	if (!empty($_POST['name']) && strlen($_POST['name']) <= 64)
	{
		$_POST['name'] = strip_tags($_POST['name']);
		$project->name = $_POST['name'];
	}
	
	//Version hinzufügen
	if (!empty($_POST['version']) && preg_match('/[0-9]+\.[0-9]+\.[0-9]+/', $_POST['version']))
	{
		if (!empty($_POST['versionfile']) && is_file(SYS_TEMP_FOLDER . $_POST['versionfile']))
			$project->setVersion($_POST['version'], $_POST['versionfile']);
		else
			$project->setVersion($_POST['version']);
	}
	
	//Info hinzufügen oder ändern
	if (!empty($_POST['info']) && is_array($_POST['info']))
	{
		$info = $project->info;
		foreach ($_POST['info'] as $key => $value)
		{
			if (strlen($key) == 0 || strlen($key) > 15 || strlen($value) > 22)
				continue;
			$key = strip_tags($key);
			$value = strip_tags($value);
			
			//Wurde das Feld leer gelassen wird die Info entfernt
			if (strlen($value) == 0)
				$value = NULL;
			$project->setInfo($key, $value);
			unset($info[$key]);
		}
		
		//Alle übrigen Infos die werden entfernt
		foreach ($info as $key => $value)
			$project->setInfo($key, NULL);
	}
	//Projekt Icons werden geändert oder entfernt
	if (!empty($_POST['iconfile']) && is_file(SYS_TEMP_FOLDER . $_POST['iconfile']))
	{
		$project->icon = $_POST['iconfile'];
	}

	// Projektseite anzeigen
	include(SYS_CNT_DIR . 'project.php');
}