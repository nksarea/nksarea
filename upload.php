<?php
error_reporting(E_ALL^E_NOTICE);
/*
 * Nimmt hochgeladene Datei, unter der Bedingung, dass sie von einem eingeloggten
 * Benutzer hochgeladen wurde, entgegen und gibt ihren temporären Dateinamen aus.
 */

// Konfiguration und Kernfunktionen laden
include_once 'system/config.php';
include_once 'system/functions/autoload.fn.php';
include_once 'system/functions/getDB.fn.php';
include_once 'system/functions/getUser.fn.php';

// Benutzer eingeloggt?
if(!getUser()) {
	header('HTTP/1.1 403 Forbidden');
	exit('Since you are not logged in, you are not permitted to upload files.');
}

// Datei hochgeladen?
if(!isset($_FILES['file'])) {
	header('HTTP/1.1 400 Bas Request');
	exit('You didn\'t upload any file.');
}

// erfolgreich?
if((int)$_FILES['file']['error']) {
	header('HTTP/1.1 500 Internal Server Error');
	exit('While uploading the file, an error occurred.');
}

// Datei entgegen nehmen
list($fileExt) = array_reverse(explode('.', $_FILES['file']['name']));
$tmpName = uniqid().'-'.md5('__({FILE:'.(time()*rand()).'})__').'.'.$fileExt;
if(!move_uploaded_file($_FILES['file']['tmp_name'], SYS_TEMP_FOLDER.'/'.$tmpName)) {
	header('HTTP/1.1 500 Internal Server Error');
	exit('While storing the file, an error occurred.');
}

// Bei Erfolg temporärer Dateiname ausgeben:
exit($tmpName);