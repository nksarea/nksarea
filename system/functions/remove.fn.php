<?php
define('REMOVE_PROJECT', 1);
define('REMOVE_FILE', 2);
define('REMOVE_LIST', 3);

/** Bereitet das Löschen eines Objektes (Projekt, Datei, Liste) vor
 *
 * Diese Funktion entfernt einen Datensatz von einer gegebenen Tabelle und schreibt
 * ihn in die Lösch-Datei. Ein externes Programm wird diese Datei und die darin
 * aufgelisteten Dateien herunterladen (in Form einer Sicherungskopie) und löschen.
 *
 * Diese Funktion prüft die Berechtigung anhand des owner Feldes in der Datenbank.
 * Nur der Eigentümer eines Datensatzes und Administratoren dürfen Datensätze löschen.
 *
 * @param integer $what eine REMOVE_ Konstante, die angibt, von welcher Tabelle
 *				gelöscht werden soll
 * @param integer $id die ID des zu löschenden Eintrags
 * @return boolean Im Erfolgsfall true, andernfalls false
 * @author Cédric Neukom
 */
function remove($what, $id) {
	// Prüfen, ob Benutzer eingeloggt
	if(!($user = getUser()) instanceof User)
		return false;

	// Löschen von tabelle ...
	switch($what) {
		case REMOVE_PROJECT:
			$table = 'projects';
			break;

		case REMOVE_List:
			$table = 'lists';
			break;

		case REMOVE_FILE:
			$table = 'files';
			break;

		default:
			return false;
	}

	$dbc = getDB();
	if(!$dbc instanceof dbc)
		return false;

	// zu löschenden Datensatz auslesen
	$record = $dbc->query('getRecord', array(
		'table' => $table,
		'id' => $id
	));

	if(!$record->dataLength)
		return false;

	$record = $record->dataAssoc;
	if($record['owner'] == $user->data->id || // Eigentümer
			$user->access_level == 0) { // Admin

		// Datensatz ins Löschfile schreiben
		$record = array_reverse($record);
		$record[] = $table;
		$record = array_reverse($record);

		if(!$tf = fopen(SYS_TRASH_FILE, 'a'))
			return false;
		fputcsv($tf, $record);
		fclose($tf);

		// Datensatz von Tabelle löschen
		return $dbc->query('removeRecord', array(
			'table' => $table,
			'id' => $id
		));
	}
	return false;
}