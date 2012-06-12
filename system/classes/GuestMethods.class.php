<?php

/** Methoden für (nicht eingeloggte) Gäste
 *
 * @author Cédric Neukom
 */
class GuestMethods extends base implements Methods {

	protected $permitted;

	public function __construct() {
		$this->permitted = !getUser();
	}

	public function __get($key) {
		switch($key) {
			case 'permitted':
				return $this->$key;
		}
	}

	/** Gibt ID einer offenen Registrierung zurück.
	 *
	 * @param string $hash Registrierungshash
	 * @param string $email E-Mail Adresse des Mitglieds
	 * @return mixed ID der Registrierung oder false (erzeugt Fehler)
	 * @author Cédric Neukom
	 */
	public function getRegistration($hash, $email) {
		// Falls Login durchgeführt werden kann abbrechen
		if(!$this->permitted)
			return $this->throwError('You\'re already logged in. You can\'t register a new account.');

		if(!($res = getDB()->query('getRegistration', array(
			'email' => $email,
			'hash' => $hash
		))) || !$res->dataLength)
			return $this->throwError('Your registration ID could not be found.');

		return $res->dataObj->id;
	}

	/** Schliesst Registrierungsprozess ab.
	 *
	 * @param integer $id Registrierungs-ID
	 * @param string $hash Registrierungshash
	 * @param string $username Gewünschter Benutzername
	 * @param string $password Passwort
	 * @return mixed true bei Erfolg oder String mit Fehlermeldung, false falls
	 *				Datenbankabfrage fehlschlug
	 * @author Cédric Neukom
	 */
	public function completeRegistration($id, $hash, $username, $password) {
		// Falls login durchgeführt werden kann abbrechen
		if(!$this->permitted)
			return $this->throwError('You\'re already logged in. You can\'t register a new account.');

		$db = getDB();
		// Registrierung validieren
		$id = intval($id);
		$reg = $db->query('getRegistrationByID',
				array('id' => $id)
			);
		if(!$reg || !$reg->dataLength)
			return $this->throwError('Your registration could not be found.');

		if($reg->dataObj->password !== $hash)
			return $this->throwError('Your registration could not be found.');

		// Benutzername prüfen
		if(strlen($username) < SYS_USERNAME_MINLENGTH
				|| strlen($username) > SYS_USERNAME_MAXLENGTH)
			return $this->throwError('The length of your username must be between '.SYS_USERNAME_MINLENGTH.' and '.SYS_USERNAME_MAXLENGTH.' characters.');

		$names = $db->query('getUser',
				array('user' => $username)
			);
		if(!$names || $names->dataLength)
			return $this->throwError('The username you chose is already taken.');

		// Passwort prüfen
		if(strlen($password) < SYS_PASSWORD_MINLENGTH
				|| strlen($password) > SYS_PASSWORD_MAXLENGTH)
			return $this->throwError('The length of you password must be between '.SYS_PASSWORD_MINLENGTH.' and '.SYS_PASSWORD_MAXLENGTH.' characters.');

		// Registrierung abschliessen
		return $db->query('registerUser',
				array(
					'pwd' => self::hashPwd($password),
					'name' => $username,
					'id' => $id
				));
	}

}