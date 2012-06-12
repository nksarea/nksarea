<?php

/** Methoden für Benutzer
 *
 * @author Cédric Neukom
 */
class UserMethods extends base implements Methods
{

	protected $permitted = false;
	protected $minAccessLevel = 2;


	public function __construct() {
		if (!$user = getUser())
			$this->throwError('You are not permitted to use these functions, because you\'re not logged in.');
		else if ($user->access_level >= $this->minAccessLevel)
			$this->throwError('Your access level isn\'t heigh enough to use these methods.');
		else
			$this->permitted = true;
	}

	public function __get($key) {
		switch ($key) {
			case 'permitted':
				return $this->$key;
		}
	}

	/** Erstellt eine neue Liste
	 *
	 * @param string $name Name der Projektliste
	 * @param integer $type Typ (0: Projektliste; 1: Prüfungsliste)
	 * @param integer $class ID der zugeordneten Klasse
	 * @param integer $deadline Timestamp - Letzte Abgabemöglichkeit (Abgabe
	 * 							weiterhin möglich - hat mit Hervorhebung des
	 * 							Abgabedatums kompensiert zu werden)
	 * @return mixed false im Fehlerfalll, andernfalls die ID der Liste
	 * @author Cédric Neukom
	 */

	public static function addList($name, $type, $class, $deadline = null) {
		if (!$this->permitted)
			return $this->throwError('You are not logged in. You can not create a list while you aren\'t logged in.');

		$type = intval($type);
		if ($type > 1 && $type < 0)
			return $this->throwError('The list type you specified is invalid. Please specify a valid list type.');

		if (($idl = intval($deadline)))
			if ($idl > time())
				$deadline = $idl;
			else
				return $this->throwError('The deadline isn\'t valid. Please specify a valid deadline.');
		else
			$deadline = null;

		$class = intval($class);
		if (!$class)
			return $this->throwError('The class you specified is invalid.');

		$db = getDB();
		$classes = $db->query('getClass', array('id' => $class));
		if (!$classes || !$classes->dataLength)
			return $this->throwError('The class you specified is invalid.');

		if ($db->query('addList', array(
					'owner' => getUser()->data->id,
					'name' => $name,
					'type' => $type,
					'deadline' => $deadline === null ? 'NULL' : $deadline,
					'class' => $class
				)))
			return $db->insertID;
		else
			return $this->throwError('A technical error occurred. The list has not been created.');
	}


	/** Erstellt ein nneues Projekt
	 *
	 * @param string $folder upload Ordner mit Projekt dateien
	 * @param string $name Name des Projekts
	 * @param integer $access_level (0:nur selber, 1:Klasse mit Lehrer, 2:Klasse ohne Lehrer, 3:alle)
	 * @param integer $description Beschreibung des Projekts
	 * @param integer $list Projektliste
	 * @return bool false im Fehlerfall, sonst true
	 * @author Lorze Widmer
	 */
	public function addProject($folder, $name, $access_level, $description = NULL, $list = NULL)
	{
		//überprüfen der Parameter
		$access_level = intval($access_level);
		$query = array();

		if (!is_dir(SYS_TMP . $folder))
			$this->throwError('$folder isn`t a folder');
		if (!is_string($name))
			$this->throwError('$name isn`t a string');
		if ($access_level < 0 && $acces_level > 4)
			$this->throwError('$access_level isn`t valid');
		if (!is_string($description) && $description != NULL)
			$this->throwError('$description isn`t a string nor NULL');
		if (!is_int($list) && $list != NULL)
			$this->throwError('$list isn`t a string nor NULL');

		//input für das sql-Template wird zusammengesetzt
		$query['owner'] = intval(getUser()->id);
		$query['name'] = $name;
		$query['description'] = ($description === null ? 'NULL' : $description);
		$query['access_level'] = $access_level;
		$query['list'] = ($list === null ? 'NULL' : intval($list));

		//template wird gefüllt und ausgeführt
		$result = getDB()->query('addProject', $query);
		if (!$result)
			$this->throwError('MYSQLi hates you!');

		//daten aus SYS_TMP/$folder werden gepackt und nach SYS_SHARE_PROJECTS/$pid.rar verschoben 
		return getRAR()->execute('packProject', array('source' => SYS_TMP . $folder, 'destination' => SYS_SHARE_PROJECTS . getDB()->insert_id . '.rar'));
	}


	/** Erstellt ein neues File
	 *
	 * @param string $name Name des Projekts
	 * @param integer $list Projektliste
	 * @param string $file Dateiname des Files
	 * @return bool false im Fehlerfall, sonst true
	 * @author Lorze Widmer
	 */
	function addFile($name, $list, $file)
	{
		//überprüfen der Parameter
		$query = array();

		if (!is_string($name))
			$this->throwError('$name isn`t a string', $name);
		if (!is_file(SYS_TMP . $file))
			$this->throwError('$file isn`t a file', $file);

		//input für das sql-Template wird zusammengesetzt
		$query['name'] = $name;
		$query['owner'] = intval(getUser()->id);
		$query['list'] = intval($list);
		$query['mime'] = mime_content_type($file);


		//template wird gefüllt und ausgeführt
		if (getDB()->query('addProject', $query))
			$this->throwError('MYSQLi hates you!');

		//daten aus SYS_TMP/$folder werden gepackt und nach SYS_SHARE_PROJECTS/$pid.rar verschoben 
		return getRAR()->execute('moveFile', array('source' => SYS_TMP . $file, 'destination' => SYS_SHARE_PROJECTS . getDB()->insert_id));
	}


	/** Bereitet das Löschen eines Objektes (Projekt, Datei, Liste) vor
	 *
	 * Diese Funktion entfernt einen Datensatz von einer gegebenen Tabelle und schreibt
	 * ihn in die Lösch-Datei. Ein externes Programm wird diese Datei und die darin
	 * aufgelisteten Dateien herunterladen (in Form einer Sicherungskopie) und löschen.
	 *
	 * Diese Funktion prüft die Berechtigung anhand des owner Feldes in der Datenbank.
	 * Nur der Eigentümer eines Datensatzes und Administratoren dürfen Datensätze löschen.
	 *
	 * @param integer $what eine base::TYPE_ Konstante, die angibt, von welcher Tabelle
	 * 				gelöscht werden soll
	 * @param integer $id die ID des zu löschenden Eintrags
	 * @return boolean Im Erfolgsfall true, andernfalls false
	 * @author Cédric Neukom
	 */
	public function remove($what, $id) {
		// Prüfen, ob Benutzer eingeloggt
		if (!$this->permitted)
			return $this->throwError('You are not logged in. You can not remove things without being logged in.');

		// Löschen von Tabelle ...
		switch ($what) {
			case self::TYPE_PROJECT:
				$table = 'projects';
				break;

			case self::TYPE_List:
				$table = 'lists';
				break;

			case self::TYPE_FILE:
				$table = 'files';
				break;

			default:
				return $this->throwError('The type of the object to delete is invalid.');
		}

		$dbc = getDB();
		if (!$dbc instanceof dbc)
			return false;

		// zu löschenden Datensatz auslesen
		$record = $dbc->query('getRecord', array(
			'table' => $table,
			'id' => $id
		));

		if (!$record->dataLength)
			return false;

		$record = $record->dataAssoc;
		if ($record['owner'] == getUser()->data->id || // Eigentümer
				getUser()->access_level == 0) { // Admin
			// Datensatz ins Löschfile schreiben
			$record = array_reverse($record);
			$record[] = $table;
			$record = array_reverse($record);

			if (!$tf = fopen(SYS_TRASH_FILE, 'a'))
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
}
