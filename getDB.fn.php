<?php
/** Gibt eine Referenz auf das Datenbankobjekt zurück.
 *
 * Da es nicht sinnvoll ist, mehrere Datenbankobjekte (mit verschiedenen
 * Datenbankservern) zu verbinden, gibt diese Funktion false zurück, falls
 * bereits ein Datenbankobjekt mit einem anderen Datenbanktreiber (bzw. einer
 * einer anderen Klasse) instantiiert ist.
 *
 * @param string $driverClass Die zu verwendende Datenbankklasse
 * @return object Diese Funktion gibt eine Referenz auf das Datenbankobjekt zurück.
 * @author Cédric Neukom
 */
function getDB($driverClass = 'dbc') {
	/** Datenbankobjekt */
	static $dbc;

	if(!is_object($dbc) || ($dbc instanceof $driverClass && !$dbc->connected)) {
		$dbc = new $driverClass('localhost', 'root', 'abc', 'nksarea');
		if($dbc->connect_errno)
			//Da kein Objekt (und somit nicht von der Basis Klasse erweitert), einfache Behandlung
			throw new Exception($dbc->connect_error, $dbc->connect_errno);
	} else if(!$dbc instanceof $driverClass)
		return false;

	return $dbc;
}