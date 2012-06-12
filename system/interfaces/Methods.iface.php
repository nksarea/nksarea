<?php

/** Methods Interface.
 *
 * @author Cédric Neukom
 */
interface Methods {

	/** Prüft Berechtigungen und setzt $this->permitted */
	public function __construct();

	/** Stellt mindestens $this->permitted zur Verüfung */
	public function __get($key);
}