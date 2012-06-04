<?php
define('USER_ERR_OK', 0);
define('USER_ERR_DATA_MISSING', 1);
define('USER_ERR_DATA_WRONG', 2);
define('USER_ERR_RAND', 3);
define('USER_RAND_MAX', 9);

define('REG_ERR_NOT_FOUND', 1);
define('REG_ERR_AUTHORISATION_FAILED', 2);
define('REG_ERR_USERNAME_LENGTH', 3);
define('REG_ERR_USERNAME_TAKEN', 4);
define('REG_ERR_PASSWORD', 5);

/**
 * Benutzerklasse. Führt Login durch (falls SESSION vorhanden; ansonsten wird
 * Login anhand von POST versucht). Stellt Benutzerdaten (Tabelle users) und
 * access_level zur Verfügung, falls eingeloggt.
 * 
 * Stellt ebenfalls Methoden zum Ändern von Eigenschaften zur Verfügung.
 * 
 * @author Cédric Neukom
 * @todo Funktionsprüfung
 */
class User {

	private $data = null;
	private $loggedin = false;
	private $myc;
	/**
	 * USER_ERR_DATA_MISSING: "Daten angeben"<br>
	 * USER_ERR_DATA_WRONG: &nbsp; "Daten falsch"
	 *
	 * @var integer Enthält den Fehler, welcher beim Login oder übernehmen der Session aufgetreten ist.
	 */
	private $error = USER_ERR_OK;
	/**
	 *
	 * 2&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;normaler Benutzer<br>
	 * 1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lehrperson<br>
	 * 0&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin<br>
	 * false Besucher
	 *
	 * @var mixed Zugriffslevel des Benutzers
	 */
	private $access_level = false;

	/**
	 * Prüft Login (anhand von $_SESSION) und übernimmt Benutzerdaten
	 *
	 * Führt Login durch (falls noch nicht eingeloggt; anhand von $_POST), übernimmt Benutzerdaten und schreibt Session
	 */
	public function __construct($myc) {
		if(get_class($myc) !== 'mysqli')
			throw new UnexpectedValueException('$myc must be an instance of mysqli');
		session_start();
		$this->myc = $myc;
		$this->checkSession();
		if(!$this->loggedin)
			$this->checkPost();
		if(isset($_GET['logout'])) {
			$this->loggedin = false;
			$this->data = null;
			$this->access_level = false;
			session_destroy();
		}
	}

	/** Prüft Benutzername und Passwort
	 *
	 * Versucht Benutzer zu identifizieren und übernimmt Daten in $this->data
	 * und setzt $this->loggedin auf TRUE, falls dies erfolgreich war.
	 *
	 * @param String $user Der Benutzername
	 * @param String $password Das Passwort
	 * @return Bool Gibt TRUE zurück, falls die Überprüfung erfolgreich war
	 */
	private function check($user, $password) {
		if(empty($user) || empty($password))
			return false;
		$res = $this->myc->query('SELECT * FROM `users` WHERE `name` = \''.$this->myc->real_escape_string($user).'\' AND `registrated` IS NOT NULL LIMIT 0,1');
		if(!$res)
			return false;
		$res = $res->fetch_object();
		if(md5($password) == $res->password && $user == $res->name && $res->accept) {
			$this->loggedin = true;
			$this->data = $res;
			$this->access_level = (int)$this->data->access_level;
			$this->myc->query('UPDATE `users` SET `last_activity` = NOW() WHERE `id` = \''.$this->data->id.'\' LIMIT 1');
			return true;
		}
		return false;
	}

	/** Versucht Identifizierung anhand der SESSION Variablen.
	 *
	 * @return Bool Ob Login anhand $_SESSION durchgeführt werden konnte.
	 */
	private function checkSession() {
		if($this->check($_SESSION['user'], $_SESSION['pass']))
			return true;
		else
			return false;
	}

	/** Versucht Identifizierung anhand der POST Variablen.
	 *
	 * Falls die Identifizierung erfolgreich war, wird Benutzername und
	 * Passwort in die SESSION Variablen übernommen.
	 *
	 * @return Bool Ob Login anhand $_POST durchgeführt werden konnte.
	 */
	private function checkPost() {
		if($_POST['login'] === 'Login') {
			if(empty($_POST['user']) || empty($_POST['user'])) {
				$this->error = USER_ERR_DATA_MISSING;
				return false;
			}
			if($this->check($_POST['user'], $_POST['pass'])) {
				if($this->data->accept == 2 && !rand(0, USER_RAND_MAX)) {
					$this->loggedin = false;
					$this->data = null;
					$this->access_level = false;
					$this->error = USER_ERR_RAND;
				} else {
					$_SESSION['user'] = $_POST['user'];
					$_SESSION['pass'] = $_POST['pass'];
				}
				return true;
			} else {
				$this->error = USER_ERR_DATA_WRONG;
				return false;
			}
		}
	}

	/** Gibt ID einer offenen Registrierung zurück.
	 *
	 * @param User $user User-Objekt der (nicht-)initialisierten Session
	 * @param mysqli $myc Datenbankverbindung
	 * @param string $hash Registrierungshash
	 * @param string $email E-Mail Adresse des Mitglieds
	 * @return integer|bool ID der Registrierung oder false
	 */
	public static function getRegistration($user, $myc, $hash, $email) {
		include_once('system/functions/parse_email.fn.php');
		if(get_class($user) !== 'User')
			throw new UnexpectedValueException('$user must be an instance of User');
		if(get_class($myc) !== 'mysqli')
			throw new UnexpectedValueException('$myc must be an instance of mysqli');
		if($user->loggedin)
			return false;
		$email = parse_email($email);
		if(!$email)
			return false;
		$reg = $myc->mysql_query('SELECT * FROM `users` WHERE `registrated` IS NULL AND `name` IS NULL AND `email` = \''.$myc->real_escape_string($email).'\' AND `password` = \''.$myc->real_escape_string($hash).'\' LIMIT 0,1');
		if(!$reg || !$reg->num_rows)
			return false;
		$reg = $reg->fetch_object();
		return $reg->id;
	}

	/** Schliesst Registrierungsprozess ab.
	 *
	 * <b>ACHTUNG!</b> Diese Methode kann <b>sowohl Zahlenwerte (die einen Fehler
	 * bedeuten)</b>, die als true gedeutet werden, <b>als auch der boolsche Wert
	 * true</b> (welches Erfolg bedeutet) zurück geben!!
	 *
	 * Diese Methode wird
	 *
	 * @param User $user User-Objekt der (nicht-)inizialisierten Session
	 * @param mysqli $myc Datenbankverbindung
	 * @param integer $id Registrierungs-ID
	 * @param string $hash Registrierungshash
	 * @param string $username Gewünschter Benutzername
	 * @param string $password Passwort
	 * @return boolean|integer true bei Erfolg oder Fehlernummer (!), false bei MySQL Fehler
	 */
	public static function completeRegistration($user, $myc, $id, $hash, $username, $password) {
		if(get_class($user) !== 'User')
			throw new UnexpectedValueException('$user must be an instance of User');
		if(get_class($myc) !== 'mysqli')
			throw new UnexpectedValueException('$myc must be an instance of mysqli');
		if($user->loggedin)
			return false;
		$id = intval($id);
		$reg = $myc->mysql_query('SELECT * FROM `users` WHERE `registrated` IS NULL AND `name` IS NULL AND `id` = \''.$id.'\' LIMIT 0,1');
		if(!$reg || !$reg->num_rows)
			return REG_ERR_NOT_FOUND;
		$reg = $reg->fetch_object();
		if($reg->password !== $hash)
			return REG_ERR_AUTHORISATION_FAILED;
		if(strlen($username) < SYS_USERNAME_MINLENGTH
				|| strlen($username) > SYS_USERNAME_MAXLENGTH)
			return REG_ERR_USERNAME_LENGTH;
		$names = $myc->query('SELECT * FROM `users` WHERE `name` LIKE \''.$myc->real_escape_string($name).'\' LIMIT 0,1');
		if(!$names || $names->num_rows)
			return REG_ERR_USERNAME_TAKEN;
		if(strlen($password) < SYS_PASSWORD_MINLENGTH
				|| strlen($password) > SYS_PASSWORD_MAXLENGTH)
			return REG_ERR_PASSWORD;
		$date = date('Y-m-d H:i:s');
		return $myc->query('UPDATE `users` SET `password` = \''.md5($password).'\', `name` = \''.$myc->real_escape_string($username).'\', `registrated` = \''.$myc->real_escape_string($date).'\', `last_activity` = \''.$date.'\' WHERE `id` = \''.$id.'\' LIMIT 1');
	}

	public function __get($name) {
		switch($name) {
			case 'data':
				//return $this->data;
			case 'loggedin':
				//return $this->loggedin;
			case 'error':
				//return $this->error;
			case 'access_level':
				//return $this->access_level;
				return $this->$name;
		}
	}

	/**
	 * Übernimmt Werte <b>ungeprüft</b>!
	 *
	 * @todo Werte prüfen VOR __set
	 */
	public function __set($key, $value) {
		switch($key) {
			case 'name':
			case 'email':
			case 'realname':
				$this->myc->query('UPDATE `users` SET `'.$key.'` = \''.$this->myc->real_escape_string($value).'\' WHERE `id` = '.$this->data->id.' LIMIT 1');
				break;
			case 'password':
				$this->myc->query('UPDATE `users` SET `password` = \''.md5($value).'\' WHERE `id` = '.$this->data->id.' LIMIT 1');
				break;
		}
	}

}