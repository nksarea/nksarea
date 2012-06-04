<?php
/** Gibt das Benutzerobjekt oder false (falls kein Benutzer eingeloggt ist) zurück.
 *
 * Falls $user und $pwd gesetzt sind, versucht diese Funktion den Benutzer ein-
 * zu loggen.
 *
 * @staticvar mixed $user Speichert das Benutzerobjekt dauerhaft
 * @param string $username Benutzername
 * @param string $pwd Passwort
 * @return User|false Referenz auf das Benutzerobjekt oder false
 * @author Cédric Neukom
 */
function getUser($username = null, $pwd = null) {
	/** Userobjekt */
	static $user;

	// Nur ausführen, wenn noch nicht ausgeführt oder Logindaten gegeben
	//  andernfalls bereits vorhandenes $user verwenden
	if($user === null || $username && $pwd) {
		$user = new User();

		// Login versuchen, wenn Benutzername und Passwort gegeben sind und noch nicht eingeloggt
		if(!$user->loggedin && $username && $pwd) 
			$user->login($username, $pwd);

		// Wenn Benutzer nicht eingeloggt
		if(!$user->loggedin)
			$user = false;
	}

	return $user;
}