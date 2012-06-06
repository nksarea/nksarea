<?php
/** Parst eine E-Mail Adresse und prüft, ob Host existiert
 *
 * @param string $email Die zu parsende E-Mail Adresse
 * @return mixed Die E-Mail Adresse, falls erfolgreich, false andernfalls
 * @author Cédric Neukom
 */
function parseEmail($email) {
	$host = preg_replace('/^.+@(.+)$/', '$1', $email);
	if(getmxrr($host, $hosts))
		return $email;
	else
		return false;
}