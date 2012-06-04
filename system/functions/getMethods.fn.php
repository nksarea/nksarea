<?php

/** Gibt Methods-Objekt entsprechend dem Zugriffslevel zurück.
 
 * @return GuestMethods|UserMethods|AdminMethods
 * @author Cédric Neukom, Lorze
 */
function getMethods() {
	/** Methodenobjekt */
	static $methods;

	if(!is_object($dbc) || !$dbc instanceof Methods) {
		$user = getUser();

		if($user instanceof User) {
			if($user->access_level === 0)
				$methods = new AdminMethods();

			if($user->access_level <= 2 || !$methods->permitted)
				$methods = new UserMethods();
		}

		if(!$methods instanceof Methods || !$methods->permitted)
			$methods = new GuestMethods();
	}
	return $methods;
}