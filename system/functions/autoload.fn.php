<?php
/** Lädt Klassen und Interfaces wenn sie gebraucht werden. (Diese Funktion wird
 * von PHP automatisch aufgerufen.)
 *
 * @see http://php.net/manual/de/language.oop5.autoload.php
 * @author Cédric Neukom
 */
function __autoload($name) {
	if(preg_replace('/[a-z0-9]*/si', '', $name))
		return false;

	if(file_exists('system/classes/'.$name.'.class.php'))
		include_once 'system/classes/'.$name.'.class.php';

	else if(file_exists('system/interfaces/'.$name.'.iface.php'))
		include_once 'system/interfaces/'.$name.'.iface.php';
}