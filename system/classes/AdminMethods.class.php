<?php
include_once 'system/functions/parseEmail.fn.php';

/**
 * Stellt administrative Methoden zur Verfügung.
 *
 * Diese Methoden stehen nur zur Verfügung, falls der (eingeloggte) Benutzer
 * access_level = 0 hat (also Administrator ist).
 *
 * @author Cédric Neukom
 * @todo Funktionsprüfung addUser - online (wegen mail)
 * @uses getUser
 * @uses getDB
 */
class AdminMethods extends UserMethods implements Methods {

	public function __construct() {
		$this->minAccessLevel = 0;
		parent::__construct();
	}

	/** Setzt Zugriffslevel (access_level) für einen Benutzer.
	 *
	 * @param integer $uid id des Benutzers
	 * @param integer $level level (0: admin; 1: teacher; 2: student)
	 * @return bool Falls false wird eine Warnung oder ein Fehler erzeugt.
	 */
	public function setUserAccessLevel($uid, $level) {
		if(!$this->permitted)
			return false;

		$uid = intval($uid);
		$level = intval($level);
		if(!$uid)
			return $this->throwWarning('The user of which you want to change the access level does not exist.');
		if($level < 0 && $level > 2)
			return $this->throwWarning('You specified an invalid access level. It has not been set.');

		// Benutzerlevel Übernehmen
		$db = getDB();
		if(!$db->query('setUserAccessLevel', array(
			'accessLevel' => $level,	// neuer Benutzerlevel
			'uid' => $uid				// ID des betroffenen Benutzers
		)) || !$db->affected_rows)
			return $this->throwError('While setting the new access level of the user, there was an unknown technical error.');

		return true;
	}

	/** Setzt Accept für einen Benutzer.
	 *
	 * Das Datenbankfeld {accept} gibt an, ob der Benutzer sich einloggen darf
	 * oder nicht.
	 *
	 * @param integer $uid id des Benutzers
	 * @param integer $accept neuer Wert für accept (0: banned; 1: accept)
	 * @return bool Falls false wird eine Warnung oder ein Fehler erzeugt.
	 */
	public function setUserAccept($uid, $accept) {
		if(!$this->permitted)
			return false;

		$uid = intval($uid);
		$accept = intval($accept);
		if(!$uid)
			return $this->throwWarning('The user which you want to ban or unban does not exist.');
		if($accept < 0 || $accept > 1)
			return $this->throwWarning('The value of the field "Accept" is not correct. It has not been set.');

		// Accept Übernehmen
		$db = getDB();
		if(!$db->query('setUserAccept', array(
			'accept' => $accept,		// neuer Wert für accept
			'uid' => $uid				// ID des betroffenen Benutzers
		)) || !$db->affected_rows)
			return $this->throwError('While banning or enabling the user, a technical error occurred.');

		return true;
	}

	/** Fügt einen Benutzer hinzu.
	 *
	 * @param string $email Die E-Mail Adresse des Benutzers (zum Versenden der E-Mail benötigt)
	 * @param integer $class ID der zugehörigen Klasse
	 * @param integer $type Typ des Benutzers (access_level) (0: admin; 1: teacher; 2: user)
	 * @param string $realname Der "echte" Name des Benutzers
	 * @param float $valid Anzahl Wochen, die vergehen, bis die Registrierung abläuft
	 * @return bool false im Fehlerfall, es wird eine Warnung oder ein Fehler erzeugt
	 */
	public function addUser($email, $class = 0, $type = 2, $realname = null, $valid = 2) {
		if(!$this->permitted)
			return false;

		// Werte validieren
		$type = intval($type);
		$valid = floatval($valid);
		$class = intval($class);

		$error = false;
		if($type < 0 || $type > 2) {
			$this->throwWarning('The access level of the new user is invalid.');
			$error = true;
		}
		if($valid < 1 || $valid > 5) {
			$this->throwWarning('The user must be enabled or disabled. There was an invalid value.');
			$error = true;
		}
		$email = parseEmail($email);
		if(!$email) {
			$this->throwWarning('The given e-mail address is invalid.');
			$error = true;
		}

		// Prüfen ob Fehler aufgetreten
		if($error)
			return false;

		$valid = date('Y-m-d H:i:s', time()+$valid*630000);

		// Registrierungshash
		$hash = md5(uniqid(microtime(), true)).'-'.uniqid();

		// Benutzer in DB hinzufügen
		$db = getDB();
		if(!$db->query('addUser', array(
			'hash' => $hash,
			'accessLevel' => $type,
			'realName' => $realname,
			'email' => $email,
			'valid' => $valid,
			'class' => $class
		)))
			return $this->throwError('While creating the new user, a technical error occurred. The user wasn\'t created.');


		// Einladungsmail vorbereiten
		$link = new Template(SYS_MAIL_INVITE_LINK);
		$link->assign('hash', $hash);
		$link->assign('id', $db->insertID);

		$tmpl = new Template(SYS_MAIL_INVITE_MAIL);
		$tmpl->setTitle(SYS_MAIL_INVITE_SUBJECT);
		$tmpl->assign('name', $realname);
		$tmpl->assign('link', $link);

		// Einladungsmail senden
		if(!mail($email, SYS_MAIL_INVITE_SUBJECT, $tmpl, 'Content-Type: text/html; charset=utf-8'.PHP_EOL.'From: '.SYS_NOREPLY))
			return $this->throwError('A technical error occurred: The e-mail with the invitation link has not been sent. The user has not been invited.');

		return true;
	}

	/** Fügt eine neue Klasse hinzu.
	 *
	 * @param string $name Name der Klasse (bsp. "G2010E")
	 * @param string $nickname Spitzname der Klasse (bsp. "G1Einhörner")
	 * @return mixed Im Fehlerfall wird false zurück gegeben und eine Warnung oder
	 *				ein Fehler erzeugt. Andernfalls wird die ID der neuen Klasse
	 *				zurück gegeben.
	 */
	public function addClass($name, $nickname = '') {
		if(!$this->permitted)
			return false;

		// Parameter validieren
		$name = strval($name);
		$nickname = strval($nickname);
		if(!strlen($name) || strlen($name) > SYS_CLASSNAME_MAXLENGTH)
			return $this->throwError('The name of the new class is invalid. The length of it should not exceed '.SYS_CLASSNAME_MAXLENGTH.' characters.');

		if(strlen($nickname) > SYS_CLASSNICK_MAXLENGTH)
			return $this->throwError('The nick name of the new class is invalid. The length of it should not exceed '.SYS_CLASSNICK_MAXLENGTH.' characters.');

		// neue Klasse eintragen
		$db = getDB();
		if(!$db->query('addClass', array(
			'name' => $name,
			'nickname' => $nickname
		)))
			return $this->throwError('A technical error occurred: The class has not been added.');

		return $db->insertID;
	}

}