<?php
/*
 * Speichert Änderungen am Projekt und leitet auf die Projektseite zurück
 *
 * @author Cédric Neukom
 */
if($template instanceof Template) {
	// Projektinstanz erzeuten, um Änderungen zu speichern
	$project = new project($_GET['pid']);

	// Name des Projektes übernehmen
	if($_POST['title'])
		$project->name = $_POST['title'];

	// Beschreibung des Projektes übernehmen
	if($_POST['description'])
		$project->description = $_POST['description'];

	// Tags setzen
	foreach($_POST['infoKey'] as $k => $key)
		$project->setInfo($key, $_POST['infoValue'][$k]);

	// Kommentare entfernen
	if($_POST['removeComment']) {
		$comments = new CommentList(CommentList::TYPE_PROJECT, $_GET['pid']);
		$comments->removeComments($_POST['removeComment']);
	}

	// Projektseite anzeigen
	include(SYS_CNT_DIR.'project.php');
}