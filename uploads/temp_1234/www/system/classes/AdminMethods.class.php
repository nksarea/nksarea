<?php
include_once 'system/functions/parse_email.fn.php';

define('ADM_ERR_NOT_ALLOWED', 3);
define('ADM_ERR_INVALID_USER', 4);
define('ADM_ERR_INVALID_VALUE', 8);
define('ADM_ERR_INVALID_EMAIL', 16);
define('ADM_ERR_INVALID_ARGUMENT', 32);

/**
 * Stellt administrative Methoden zur Verfügung.
 *
 * Diese Methoden stehen nur zur Verfügung, falls der (eingeloggte) Benutzer
 * access_level = 0 hat (also Administrator ist).
 *
 * @author Cédric Neukom
 * @todo Funktionsprüfung addUser - online (wegen mail)
 */
class AdminMethods {

	private $myc;
	private $user;

	/** Stellt Funktionen zur Verfügung, falls access_level 0 ist.
	 *
	 * Falls der eingeloggte Benutzer Administrator ist, werden $user und $myc
	 * für die anderen Methoden zur Verfügung gestellt.
	 *
	 * @param User $user Der eingeloggte Benutzer
	 * @param mysqli $myc Die Verbindung zur Datenbank
	 */
	public function __construct($user, $myc) {
		if(get_class($myc) !== 'mysqli')
			throw new UnexpectedValueException('$myc must be an instance of mysqli');
		if(get_class($user) !== 'User')
			throw new UnexpectedValueException('$user must be an instance of User');
		if($user->access_level !== 0 || $user->loggedin != true)
			return;
		$this->user = $user;
		$this->myc = $myc;
	}

	/** Setzt Zugriffslevel (access_level) für einen Benutzer.
	 *
	 * @param integer $uid id des Benutzers
	 * @param integer $level level (0: admin; 1: teacher; 2: student)
	 * @return integer|bool Fehlernummer oder Boolean (von mysql query)
	 */
	public function setUserAccessLevel($uid, $level) {
		if($this->user->access_level !== 0)
			return ADM_ERR_NOT_ALLOWED;
		$uid = intval($uid);
		$level = intval($level);
		if(!$uid)
			return ADM_ERR_INVALID_USER;
		if($level < 0 && $level > 2)
			return ADM_ERR_INVALID_VALUE;
		return $this->myc->query('UPDATE `users` SET `access_level` = \''.$level.'\' WHERE `id` = \''.$uid.'\' LIMIT 1');
	}

	/** Setzt Accept für einen Benutzer.
	 *
	 * @param integer $uid id des Benutzers
	 * @param integer $accept accept (0: banned; 1: accept; 2: random_errors)
	 * @return integer|bool Fehlernummer oder Boolean (von mysql query)
	 */
	public function setUserAccept($uid, $accept) {
		if($this->user->access_level !== 0)
			return ADM_ERR_NOT_ALLOWED;
		$uid = intval($uid);
		$accept = intval($accept);
		if(!$uid)
			return ADM_ERR_INVALID_USER;
		if($accept < 0 || $accept > 2)
			return ADM_ERR_INVALID_VALUE;
		return $this->myc->query('UPDATE `users` SET `accept` = \''.$accept.'\' WHERE `id` = \''.$uid.'\' LIMIT 1');
	}

	/** Fügt einen Benutzer hinzu.
	 *
	 * @param string $email Die E-Mail Adresse des Benutzers (zum Versenden der E-Mail benötigt)
	 * @param integer $class ID der zugehörigen Klasse
	 * @param integer $type Typ des Benutzers (0: admin; 1: teacher; 2: user)
	 * @param string $realname Der "echte" Name des Benutzers
	 * @param float $valid Anzahl Wochen, die vergehen, bis die Registrierung abläuft
	 * @return integer|bool Fehlernummer oder Boolean (von mysql query)
	 */
	public function addUser($email, $class = null, $type = 2, $realname = null, $valid = 2) {
		if($this->user->access_level !== 0)
			return ADM_ERR_NOT_ALLOWED;
		$type = intval($type);
		$valid = floatval($valid);
		if($type < 0 || $type > 2 || $valid <= 0 || $valid > 5)
			return ADM_ERR_INVALID_VALUE;
		$email = parse_email($email);
		if(!$email)
			return ADM_ERR_INVALID_EMAIL;
		if(intval($class) === 0)
			$class = null;
		$valid = date('Y-m-d H:i:s', time()+$valid*630000);
		$hash = md5(uniqid(microtime(), true));

		$link = new Template('system/template/mail/invitelink');
		$link->assign('hash', $hash);
		$tmpl = new Template('system/template/mail/invite.mail.html');
		$tmpl->setTitle(SYS_MAIL_INVITE_SUBJECT);
		$tmpl->assign('name', $realname);
		$tmpl->assign('link', $link);

		return $this->myc->query('INSERT INTO `users` (`password`, `access_level`, `realname`, `email`, `last_activity`, `class`) VALUES (\''.$this->myc->real_escape_string($hash).'\', \''.$type.'\', '.($realname===null?'NULL':'\''.$this->myc->real_escape_string($realname).'\'').', \''.$this->myc->real_escape_string($email).'\', \''.$valid.'\', '.($class===null?'NULL':'\''.$class.'\'').')')
			&& mail($email, SYS_MAIL_INVITE_SUBJECT, $tmpl->assign('id', $this->myc->insert_id)?$tmpl:$tmpl, 'Content-Type: text/html; charset=utf-8'.PHP_EOL.'From: '.SYS_NOREPLY);
	}

	/** Fügt eine Klasse hinzu.
	 *
	 * @param string $name Name der Klasse (bsp. "G2010E")
	 * @param string $nickname Spitzname der Klasse (bsp. "G1Einhörner")
	 * @return integer|bool Fehlernummer oder Boolean von mysql query
	 */
	public function addClass($name, $nickname = '') {
		if($this->user->access_level !== 0)
			return ADM_ERR_NOT_ALLOWED;
		if(!strlen($name) || strlen($name) > 6 || strlen($nickname) > 24)
			return ADM_ERR_INVALID_ARGUMENT;
		return $this->myc->query('INSERT INTO `classes` (`name`, `nickname`) VALUES (\''.$this->myc->real_escape_string($name).'\', \''.$this->myc->real_escape_string($nickname).'\')');
	}

	public function __get($key) {
		switch($key) {
			case 'allowed':
				return $this->user->access_level === 0;
				break;
			case 'user':
				return $this->user;
		}
	}

}