<?php

/**
 * Benutzerklasse. Führt Login durch (falls SESSION vorhanden; ansonsten wird
 * Login anhand von POST versucht). Stellt Benutzerdaten (Tabelle users) und
 * access_level zur Verfügung, falls eingeloggt.
 *
 * Stellt ebenfalls Methoden zum Ändern von Eigenschaften zur Verfügung.
 *
 * @author Cédric Neukom
 */
class User extends base {

	private $data = null;
	private $loggedin = false;

	/** Zugriffslevel
	 *
	 * 2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;normaler Benutzer<br>
	 * 1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lehrperson<br>
	 * 0&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin<br>
	 * false Besucher / nicht eingeloggt
	 *
	 * @var mixed Zugriffslevel des Benutzers
	 */
	private $access_level = false;


	// static:

	/** Erstellt einen Hash des Passworts und gibt ihn zurück
	 *
	 * @param string $input Das Passwort bzw den zu hashenden String
	 * @return string Den Hash (Hex-Kodiert)
	 */
	public static function hashPwd($input) {
		/** Einfügort beim alten hash */
		$def = array(25, 9, 19, 1, 24, 31, 21, 0, 19, 24);

		// statische Strings um den Eingabewert
		$input = 'aMTOK_ZNO_/V'.$input.'hFHKVb?Ck>Jg';

		// Ausgangshash erzeugen
		$hash = hash('sha512', $input);

		for($r = 0; $r < 10; $r++)
			$hash = substr_replace($hash, hash('sha384', $hash), $def[$r], 96);
		return $hash;
	}
	

	// objective:

	/**
	 * Prüft Login (anhand von $_SESSION) und übernimmt Benutzerdaten
	 *
	 * Führt Login durch (falls noch nicht eingeloggt; anhand von $_POST), übernimmt Benutzerdaten und schreibt Session
	 */
	public function __construct() {
		// SessionCookie verifizieren und Login übernehmen
		session_start();

		// Prüft, ob bereits eingeloggt und übernimmt Sitzung
		if(!empty($_SESSION['user']) && !$this->check($_SESSION['user'], $_SESSION['pwd'])) {
			session_destroy();
			$this->throwError('You session is invalid. One possible reason for this problem is, that you were inactive for a too long timespan. Another reason would be, that somebody has stolen your Session-ID cookie and has now logged in to your account without your password.');
		}
	}

	public function __get($key) {
		switch($key) {
			case 'data':
			case 'loggedin':
			case 'access_level':
				return $this->$key;
			case 'error':
			case 'warning':
				return parent::__get($key);
		}
	}

	public function __set($key, $value) {
		if(!$this->loggedin)
			return;

		switch($key) {
			case 'name':
			case 'email':
			case 'realname':
				getDB()->query('setUserValue', array(
					'key' => $key,
					'value' => $value,
					'id' => $this->data->id
				));
				break;
			case 'password':
				getDB()->query('setUserValue', array(
					'key' => $key,
					'value' => self::hashPwd($value),
					'id' => $this->data->id
				));
				break;
		}
	}

	/** Versucht Benutzer einzuloggen
	 *
	 * @param string $user Benutzername
	 * @param string $pwd Passwort
	 * @return boolean Ob Benutzer eingeloggt werden konnte (ist auch false, falls
	 *			bereits eingeloggt)
	 */
	public function login($user, $pwd) {
		if(empty($user) || empty($pwd))
			return $this->throwError('The user has not been identified: no login data was given.');

		if(!$this->loggedin && $this->check($user, $pwd)) {
			$_SESSION['user'] = $user;
			$_SESSION['pwd'] = $pwd;
			return true;
		}
		return false;
	}

	/** Meldet den Benutzer ab und löscht die Sitzung
	 *
	 * @return boolean Ob die Sitzung gelöscht werden konnte
	 */
	public function logout() {
			$this->loggedin = false;
			$this->data = null;
			$this->access_level = false;
			return session_destroy();
	}

	/** Prüft Benutzername und Passwort
	 *
	 * Versucht Benutzer zu identifizieren und übernimmt Daten in $this->data
	 * und setzt $this->loggedin auf TRUE, falls dies erfolgreich war.
	 *
	 * @param string $user Der Benutzername
	 * @param string $password Das Passwort
	 * @return boolean Gibt TRUE zurück, falls die Überprüfung erfolgreich war
	 */
	private function check($user, $password) {
		if(empty($user) || empty($password))
			return false;

		$db = getDB();
		if(!($res = $db->query('getLogin', array(
			'user' => $user
		))) || !$res->dataLength)
			return $this->throwWarning('You couldn\'t be logged in: Your username or password seems to be wrong.');

		//Logindaten überprüfen
		if(self::hashPwd($password) == $res->dataObj->password
				&& $user == $res->dataObj->name
				&& $res->dataObj->accept) {
			$this->loggedin = true;
			$this->data = $res->dataObj;
			$this->access_level = (int)$this->data->access_level;

			//Datum der letzten Aktivität ( = jetzt) setzen
			$db->query('setActivity', array(
				'uid' => $this->data->id
			));
			return true;
		}
		return false;
	}

}