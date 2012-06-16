<?php
/*
 * Speichert Änderungen am Benutzer
 *
 * @author Cédric Neukom
 */
if(getUser()) {
	// E-Mail Adresse setzen
	if($_POST['email'])
		getUser()->email = $_POST['email'];

	// realname setzen
	if($_POST['realname'])
		getUser()->realname = $_POST['realname'];

	include(SYS_CNT_DIR.'editAdmin.php');
}