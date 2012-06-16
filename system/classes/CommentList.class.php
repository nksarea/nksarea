<?php

/**
 * Stellt Methoden rumd um Kommentare zur Verfügung.
 *
 * @author Cédric Neukom
 */
class CommentList extends base {

	/** Die ID des Objekts */
	protected $id;

	/** Der Typ des Objekts (als String; kann in Datenbankabfragen gebraucht werden) */
	protected $type;

	/** Anzahl Kommentare gesamt */
	protected $length;

	/** Anzahl Root-Kommentare (Kommentare, die nicht Antwort auf anderen Kommentar sind) */
	protected $threads;

	/** Der Eigentümer des Kommentierten Objektes */
	protected $owner;

	/** Erstellt ein neues CommentList-Objekt.
	 *
	 * @param integer $objType Eine TYPE-Konstante, die angibt, von welchem Typ
	 *				das Objekt ist, zu dem Kommentare verarbeitet werden sollen
	 * @param integer $objID Die ID des Objektes
	 */
	public function __construct($objType, $objID) {
		// Parameter validieren
		
		$this->id = (int)$objID;
		if(!$this->id)
			$this->throwError('The object id has to be an integer.');

		switch($objType) {
			case self::TYPE_PROJECT:
				$this->type = 'project';
				break;

			case self::TYPE_FILE:
				$this->type = 'file';
				break;

			case self::TYPE_LIST:
				$this->type = 'list';
				break;

			default:
				return $this->throwError('The type of the object is invalid. Make sure that it is a TYPE-Constant.');
		}

		// Anzahl Threads und Kommentare auslesen
		$result = getDB()->query('getCommentListInfo', array(
			'type' => $this->type,
			'id' => $this->id
		));
		if($result) {
			$this->length = $result->dataObj->length;
			$this->threads = $result->dataObj->threads;
			$this->owner = $result->dataObj->owner;
		}
	}

	public function __get($key) {
		switch($key) {
			case 'length':
			case 'threads':
				return $this->$key;
			case 'comments':
				return $this->getComments();
		}
	}

	/** Liest alle Kommentare aus der Datenbank und gibt sie strukturiert zurück.
	 *
	 * Diese Funktion cachet das Ergebnis nicht!
	 *
	 * @param boolean $reverseOrder gibt an, ob die Kommentare in umgekehrter
	 *				Reihenfolge ausgelesen werden sollen. Standard für die Sortierung
	 *				ist nach Datum aufsteigend (neuestes zu unterst).
	 */
	public function getComments($reverseOrder = false) {
		// Kommentare auslesen
		$result = getDB()->query('getComments', array(
			'type' => $this->type,
			'id' => $this->id,
			'order' => $reverseOrder?'DESC':'ASC'
		));

		if(!$result)
			return $this->throwError('A technical error occurred. No comments could be fetched.');

		else { // Kommentare strukturieren
			$byID = array();
			$comments = array();

			do {
				$comment = $result->dataObj;
				$comment->replies = array();
				if((int)$result->dataObj->parent && $byID[$result->dataObj->parent])
					$byID[$result->dataObj->parent]->replies[] = &$comment;
				else
					$comments[] = &$comment;
				$byID[$result->dataObj->id] = &$comment;
				unset($comment);
			} while($result->next());
		}
		return $comments;
	}

	/** Löscht die gewählten Kommentare
	 *
	 * @param array $commentIDs Die Kommentar IDs
	 */
	public function removeComments($commentIDs) {
		// int-cast für alle IDs
		foreach($commentIDs as &$id)
			$id = (int)$id;

		// Entferne die Kommentare
		return getDB()->query('removeComments', array(
			'type' => $this->type,
			'id' => $this->id,
			'remove' => implode(',', $commentIDs)
		));
	}
}