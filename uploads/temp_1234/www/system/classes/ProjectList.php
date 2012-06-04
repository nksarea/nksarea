<?php
include_once 'system/functions/zip_pack.fn.php';

/** Gibt an, dass kein Mitglied eingeloggt ist.									 */
define('LST_NOT_LOGGED_IN', 0);

/** Gibt an, dass der Typ der Liste invalid ist.								 */
define('LST_UNEXPECTED_TYPE', 2);

/** Gibt an, dass die Klassen-ID ungültig ist.									 */
define('LST_UNEXPECTED_CID', 3);

/** Gibt an, dass das Ablaufdatum ungültig ist.
 *
 * Gibt <b>nicht</b> an, wenn $deadline auf NULL gesetzt wurde, weil es kein
 * integer war!
 */
define('LST_UNEXPECTED_DEADLINE', 4);

/** Gibt an, dass $orderBy einen ungültigen Wert enthält						 */
define('LST_UNEXPECTED_ORDER', 5);

/** Gibt an, dass Einträge nach Namen sortiert zurückgegeben werden sollen		 */
define('LST_ORDER_NAME', 1);

/** Gibt an, dass Einträge nach Eigentümer sortiert zurückgegeben werden sollen	 */
define('LST_ORDER_OWNER', 2);

/** Gibt an, dass Einträge nach upload_time sortiert zurückgegeben werden sollen */
define('LST_ORDER_UPLOAD', 3);

/**
 * Projektlisten. Stellt Funktionen bezüglich Projektlisten zur Verfügung.
 *
 * <code>$plid = ProjectList::addProjectList();
 * $pl = new ProjectList($user, $plid);
 * $pl->removeProjectList();
 * </code>
 *
 * @author Cédric Neukom
 */
class ProjectList {

	/** Ob der Inizialisierte Benutzer berechtigt ist
	 *
	 * <b>Berechtigung umfasst</b>
	 * <table>
	 * 		<tr>
	 * 			<th>Berechtigung</th>
	 * 			<th>Listentyp</th>
	 * 		</tr>
	 * 		<tr>
	 * 			<td>Lesen / Ansehen der Projekte</td>
	 * 			<td>Projektliste</td>
	 * 		</tr>
	 * 		<tr>
	 * 			<td>Schreiben / Hinzufügen von Projekten</td>
	 * 			<td><i>Alle</i></td>
	 * 		</tr>
	 * </table>
	 *
	 * <b>Berechtigung umfasst <i>nicht</i></b><br>
	 * <ul>
	 * 	<li>Ändern von Einstellungen</li>
	 * 	<li>Löschen der Liste</li>
	 * </ul>
	 *
	 * @var boolean
	 */
	private $permitted = false;
	/** Typ - wirkt sich auf Berechtigungen aus
	 *
	 * 0 Projektliste<br>
	 * 1 Prüfungsliste
	 *
	 * @var integer|null
	 */
	private $type = null;
	/** Datenbankverbindung
	 *
	 * @var mysqli|null
	 */
	private $myc = null;
	/** Projektlistendaten
	 *
	 * @var mysqli|null
	 */
	private $data = null;
	/** Inizialisierter Benutzer
	 *
	 * @var User
	 */
	private $user = null;

	/** Erzeugt ein neues ProjektListen-Objekt und stellt somit die Funktionen
	 * zur Verfügung - je nach Berechtigungslevel und Listentyp.
	 *
	 * @param User $user Inizialisiertes User-Objekt
	 * @param mysqli $myc Datenbankverbindung
	 * @param integer $plid ID der Projektliste
	 */
	public function __construct($user, $myc, $plid) {
		if(get_class($user) !== 'User')
			throw new UnexpectedValueException('$user must be an instance of User');
		if(get_class($myc) !== 'mysqli')
			throw new UnexpectedValueException('$myc must be an instance of mysqli');
		if(!$user->loggedin)
			return;
		$plid = intval($plid);
		$plist = $myc->query('SELECT * FROM `lists` WHERE `id` = \''.$plid.'\' LIMIT 0,1');
		if(!$plist->num_rows)
			return;
		$this->data = $plist->fetch_object();
		$this->myc = $myc;
		$this->type = (int)$this->data->type;
		$this->user = $user;
		if($user->data->class == $this->data->class	   // Schüler der Klasse
				|| $user->access_level === 0		 // Admin
				|| $user->data->id == $this->data->owner)	  // Eigentümer
			$this->permitted = true;
	}

	public function __get($key) {
		if($this->data !== null && $this->permitted)
			switch($key) {
				case 'data':
				case 'type':
				case 'permitted':
					return $this->$key;
					break;
			}
	}

	/** Übernimmt Werte <b>ungeprüft</b>!
	 *
	 * Ausnahme: deadline ( > time() || > deadline)
	 *
	 * @todo Werte prüfen VOR __set
	 */
	public function __set($key, $value) {
		if($this->data !== null && $this->permitted
				&& ($this->user->access_level === 0		// Admin
				|| $this->user->data->id === $this->data->owner))	// Eigentümer
			switch($key) {
				case 'name':
					$this->myc->query('UPDATE `lists` SET `name` = \''.$this->myc->real_escape_string($key).'\' WHERE `id` = \''.$this->data->id.'\' LIMIT 1');
					break;
				case 'deadline':
					$value = intval($value);
					if($value > time() || $value > strtotime($this->data->deadline))
						$this->myc->query('UPDATE `lists` SET `deadline` = \''.$value.'\' WHERE `id` = \''.$this->data->id.'\' LIMIT 1');
					break;
				case 'class':
					$value = intval($value);
					if($value)
						$this->myc->query('UPDATE `lists` SET `class` = \''.$value.'\' WHERE `id` = \''.$this->data->id.'\' LIMIT 1');
					break;
			}
	}

	/** Gibt Einträge der Liste als Array zurück (Projekte bzw. Prüfungen)
	 *
	 * @param integer $orderBy Feld, nach dem sortiert werden soll
	 * 						(mögliche Werte: siehe LST_ORDER-Konstanten)
	 * @param bool $orderDesc Falls true werden die Einträge in umgekehrter
	 * 						Reihenfolge zurückgegeben.
	 * @return mixed Falls erfolgreich gibt diese Funktion ein Array zürck, das
	 * 				die Projekte der Liste (als Object) enthält.
	 * 				Andernfalls wird eine Fehlernummer oder false (von mysql_query)
	 * 				zurückgegeben.
	 */
	public function getProjects($orderBy = LST_ORDER_NAME, $orderDesc = false) {
		if(!$this->permitted || $this->data === null)
			return LST_NOT_LOGGED_IN;
		$return = array();
		$sql = new Template('system/template/sql/getProjects.sql');
		$sql->assign('list', $this->data->id);
		if($orderDesc)
			$sql->assign('desc', 'DESC');
		switch($orderBy) {
			case LST_ORDER_NAME:
				$sql->assign('orderby', 'p.`name`');
				break;
			case LST_ORDER_OWNER:
				$sql->assign('orderby', 'o.`name`');
				break;
			case LST_ORDER_UPLOAD:
				$sql->assign('orderby', 'p.`upload_time`');
				break;
			default:
				return LST_UNEXPECTED_ORDER;
		}
		$result = $this->myc->query($sql->create(true, false));
		if(!$result)
			return false;
		while(null !== ($entry = $result->fetch_object()))
			$return[] = $entry;
		return $return;
	}

	/** Löscht Liste mit Prüfungen (falls Prüfungsliste)
	 *
	 * @todo Löschen der Prüfungen auf der Liste (if Prüfungsliste)
	 * 		(nicht implementiert in minimal 1.0)
	 * @return boolean Bei Erfolg true ansonsten false
	 */
	public function removeProjectList() {
		if($this->permitted &&	// Erlaubt
				($this->user->access_level === 0		// Admin
				|| $this->user->data->id == $this->data->owner))	  // Eigentümer
			if($this->myc->query('DELETE FROM `lists` WHERE `id` = \''.intval($this->data->id).'\' LIMIT 1')) {
				if($this->data->type === 0)
					$this->myc->query('UPDATE `projects` SET `list` = NULL WHERE `list` = \''.$this->data->id.'\'');
				else if($this->data->type === 1) {
					//Prüfungen löschen (bzw verschieben nach /system/trash)
					exit('Not implemented yet! (delete Prüfungen from PrüfungsListe)');
				}
				$this->data = null;
				$this->permitted = false;
				return true;
			}
		return false;
	}

	/** Fasst alle in der Listen erfassten Einträge (Prüfungen bzw. Projekte) in
	 * einem ZIP-Archiv zusammen.
	 *
	 * Diese Funktion bricht ab und gibt false zurück, sobald <b>ein einzenler
	 * Eintrag</b> nicht hinzugefügt werden konnte.
	 *
	 * @return string|false Bei Erfolg true, andernfalls false
	 * @todo Prüfungslisten -> diese Funktion überprüfen!
	 *		(nich implementiert in minimal 1.0)
	 */
	public function archive() {
		$entries = $this->getProjects();
		if(is_array($entries)) {
			$file = SYS_TMP.'/'.uniqid().'.zip';
			$archive = new ZipArchive();
			$archive->open($file, ZipArchive::OVERWRITE);
			foreach($entries as $entry)
				if(!ZipPack($archive, SYS_SHARE_PROJECTS.'/'.$entry->id, empty($entry->owner_realname)?$entry->owner_name:$entry->owner_realname))
					return false;
		} else
			return false;
		return $file;
	}

	/** Erstellt eine neue Liste
	 *
	 * @param User $user Inizialisierter Benutzer
	 * @param mysqli $myc Datenbankverbindung
	 * @param string $name Name der Projektliste
	 * @param integer $type Typ (0: Projektliste; 1: Prüfungsliste)
	 * @param integer $class ID der zugeordneten Klasse
	 * @param integer $deadline Timestamp - Letzte Abgabemöglichkeit (Abgabe
	 * 							weiterhin möglich - hat mit Hervorhebung des
	 * 							Abgabedatums kompensiert zu werden)
	 * @return integer|bool Fehlernummer oder Rückgabewert von mysql query
	 */
	public static function addList($user, $myc, $name, $type, $class, $deadline = null) {
		if(get_class($user) !== 'User')
			throw new UnexpectedValueException('$user must be an instance of User');
		if(get_class($myc) !== 'mysqli')
			throw new UnexpectedValueException('$myc must be an instance of mysqli');
		if(!$user->loggedin)
			return LST_NOT_LOGGED_IN;
		$type = intval($type);
		if($type > 1 && $type < 0)
			return LST_UNEXPECTED_TYPE;
		if(intval($deadline))
			if(intval($deadline) > time())
				$deadline = intval($deadline);
			else
				return LST_UNEXPECTED_DEADLINE;
		else
			$deadline = null;
		$class = intval($class);
		if(!$class)
			return LST_UNEXPECTED_CID;
		$classes = $myc->query('SELECT * FROM `classes` WHERE `id` = \''.$class.'\' LIMIT 0,1');
		if(!$classes || !$classes->num_rows)
			return LST_UNEXPECTED_CID;
		return $myc->query(
				'INSERT INTO `lists` (`owner`, `name`, `creation_time`, `type`, `deadline`, `class`) '.
				'VALUES (\''.intval($user->data->id).'\', \''.
				$myc->real_escape_string($name).'\', \''.
				date('Y-m-d H:i:s').'\', \''.
				$type.'\', '.
				($deadline === null ? 'NULL' : date('\'Y-m-d H:i:s\'', $deadline)).', \''.
				$class.'\')'
		);
	}

}