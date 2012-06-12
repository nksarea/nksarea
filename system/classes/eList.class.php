<?php

/**
 * Projekt- & Prüfungslisten. Stellt Funktionen bezüglich Listen zur Verfügung.
 *
 * <code>$plid = UserMethods::addList();
 * $pl = new eList($plid);
 * $pl->remove();
 * </code>
 *
 * Hinweis: diese Klasse konnte nicht "List" genannt werden, da list ein
 * reserviertes Schlüsselwort ist.
 *
 * @author Cédric Neukom
 */
class eList extends base {

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
	 * 0 Projektliste<br/>
	 * 1 Prüfungsliste
	 *
	 * @var integer|null
	 */
	private $type = null;
	/** Projektlistendaten
	 *
	 * @var object|null
	 */
	private $data = null;

	/** Erzeugt ein neues ProjektListen-Objekt und stellt somit die Funktionen
	 * zur Verfügung - je nach Berechtigungslevel und Listentyp.
	 *
	 * @param integer $plid ID der Projektliste
	 * @uses getDB
	 * @uses getUser
	 */
	public function __construct($plid) {
		$user = getUser();
		$db = getDB();

		// Falls nicht angemeldet
		if(!$user) {
			$this->throwError('You are not logged in.');
			return;
		}

		$plid = intval($plid);
		$plist = $db->query('getList', array('id' => $plid));
		if(!$plist->dataLength) {
			$this->throwError('The list has not been found.');
			return;
		}

		$this->data = $plist->dataObj;
		$this->type = (int)$this->data->type;

		// Berechtigung prüfen
		if($user->data->class == $this->data->class	      // Schüler der Klasse
				|| $user->access_level === 0              // Admin
				|| $user->data->id == $this->data->owner) // Eigentümer
			$this->permitted = true;
	}

	public function __get($key) {
		if($this->data !== null && $this->permitted)
			switch($key) {
				case 'type':
				case 'permitted':
					return $this->$key;

				case 'id':
				case 'owner':
				case 'class':
					return (int)$this->data->$key;

				case 'name':
				case 'description':
				case 'creation_time':
				case 'deadline':
					return $this->data->$key;
			}
	}

	public function __set($key, $value) {
		// Berechtigung prüfen
		if($this->data !== null && $this->permitted
				&& ($this->user->access_level === 0               // Admin
				|| $this->user->data->id === $this->data->owner)) // Eigentümer
			switch($key) {
				case 'deadline':
				case 'class':
					$value = intval($value);
				case 'name':
				case 'description':
					getDB()->query('setListValue', array(
						'key' => $key,
						'value' => $value,
						'id' => $this->data->id
					));
					break;
			}
	}

	/** Löscht die Liste.
	 *
	 * Mit dem Löschen der Liste werden noch folgende Schritte ausgeführt:
	 *  Bei Prüfungslisten:
	 *		- Die Prüfungen werden für ein Backup archiviert
	 *	Bei Projektlisten:
	 *		- Die Projekte werden von der Liste entfernt
	 *
	 * @return boolean Bei Erfolg true, ansonsten false.
	 */
	public function remove() {
		switch($this->type) {
			case 0:
				if(!getDB()->query('removeListFromProjects', array(
					'id' => $this->data->id
				)))
					return $this->throwError('A technical error occurred. The list has not been removed.');
				break;
			
			case 1:
				// TODO prüfungen archvieren nach SYS_TRASH
				break;
		}

		$um = new UserMethods(); // UserMethods Objekt erhalten um Liste zu löschen
		if($um->remove(self::TYPE_LIST, $this->data->id)) {
			// Werte entfernen, da Objekt nicht zerstört werden kann
			$this->data = null;
			$this->permitted = false;
			$this->type = null;
			return true;
		}
		return $this->throwError('A technical error occurred. The list has not been removed.');
	}

}