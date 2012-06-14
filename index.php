<?php
error_reporting(E_ALL^E_NOTICE);

// Konfiguration und Kernfunktionen laden
include_once 'system/config.php';
include_once 'system/functions/autoload.fn.php';
include_once 'system/functions/getDB.fn.php';
include_once 'system/functions/getRAR.fn.php';
include_once 'system/functions/getUser.fn.php';
include_once 'system/functions/getMethods.fn.php';

//warnungs Variable inizialisieren
$warning = array();

// Template inizialisieren
$template = new LanguageTemplate(SYS_UI_TMPL_DIR);

// Seite ausführen
if(empty($_GET['page']))
	$_GET['page'] = 'index';

if(preg_match('/^[a-zA-Z]+$/', $_GET['page']))
	if(is_file(SYS_CNT_DIR.$_GET['page'].'.php'))
		include(SYS_CNT_DIR.$_GET['page'].'.php');
	else
		include(SYS_ERR_DIR.'404.php');

// Dokument im gewünschten Format 
if($template instanceof Template)
	if($_SERVER['HTTP_X_INTERFACE'] == 'xml') // JS contentLoader setzt X-Interface Header auf xml
		// schlankes Update als XML generieren
		$template->createXML();

	else {
		// Für HTML ganzes Dokument laden:
		//  standard Stylesheet- und Scriptdateien einbinden
		$template->addCSS('styles/css/main.css');
		$template->addCSS('styles/css/elements.css');
		$template->addCSS('styles/css/content.css');
		$template->addCSS('styles/css/slideshow.css');
		$template->addJS('scripts/main');

		//  und leere Felder füllen
		foreach($template->getEmptyFields() as $emptyField) {
			$emptyField = substr($emptyField, 1);
			if(is_file(SYS_FIELD_DIR.$emptyField.'.php'))
				include(SYS_FIELD_DIR.$emptyField.'.php');
		}
		$template->createHTML();
	}

else { // $template wurde unerlaubt überschrieben
	header('HTTP/1.1 500 Internal Server Error');
	exit('Systemfehler');
}