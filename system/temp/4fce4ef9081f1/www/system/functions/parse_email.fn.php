<?php
/** Parst eine E-Mail Adresse und prüft, ob Host existiert
 *
 * @param string $email Die zu parsende E-Mail Adresse
 * @return string|false Die E-Mail Adresse, falls erfolgreich, false andernfalls
 */
function parse_email($email) {
	$host = preg_replace('/^.+@(.+)$/', '$1', $email);
	if(getmxrr($host, $hosts))
		return $email;
	else
		return false;
}