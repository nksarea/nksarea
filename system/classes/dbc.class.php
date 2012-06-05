<?php

class dbc extends mysqli
{
	private $connected = false;
	public $errorblo;
	public $warning;

	/**
	 * Das gleiche wie mysqli::__construct(), bei Erfolg der verbindung wird this::connected auf true gesetzt.
	 */
	public function __construct($host = null, $username = null, $passwd = null, $dbname = null)
	{
		parent::__construct($host, $username, $passwd, $dbname);
		if ($this->connect_errno == 0)
			$this->connected = true;
		else
			$this->throwWarning('mysqli couldn`t connect');
	}

	/**
	 * this::connected gibt den momentanen Verbindungsstatus zurück
	 *
	 * @param type $name
	 * @return mixed im Fehlerfall false, sonst der Wert der abgerufenen Eigenschaft
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'connected':
				return $this->$name;
		}
	}

	/**
	 * Das gleiche wie mysqli::connect(), bei Erfolg der verbindung wird this::connected auf true gesetzt.
	 *
	 * @return boolean im Fehlerfall false sonst true
	 */
	public function connect($host = NULL, $username = NULL, $passwd = NULL, $dbname = NULL, $port = NULL, $socket = NULL)
	{
		parent::__construct($host, $username, $passwd, $dbname, $port, $socket);
		if ($this->connect_errno == 0)
			$this->connected = true;
		else
			$this->throwWarning('mysqli couldn`t connect');
		return true;
	}

	/**
	 * Das gleiche wie mysqli::close(), bei Erfolg der schliessung der Verbindung wird this::connected auf false gesetzt.
	 *
	 * @return boolean im Fehlerfall false sonst true
	 */
	public function close()
	{
		if (parent::close())
			$this->connected = false;
		else
			$this->throwWarning('mysqli couldn`t close');
		return true;
	}

	/**
	 * Alle Placeholder %{'name'}% werden durch die entsprechenden Daten aus $input ersetzt.
	 * Alternativ werden auch this::input und this::template akzeptiert. Sind alle Placeholder
	 * ersetzt worden wird der sql-Code ausgeführt und die Daten als dbcResult Object zurückgegeben.
	 *
	 * @param string $template name der Template
	 * @param array $input Daten für die Placeholder im Tempalte: 'Placeholder Name'=>'Daten'
	 * @return boolean im Fehlerfall false sonst true
	 */
	public final function query($template, $input)
	{
		foreach($input as $key => $value)
			$input[$key] = $this->real_escape_string ($value);
		
		$base = new base();
		$template = $base->template($template . '.sql', $input);
		
		if(!$template)
			return false;

		$result = parent::query($template);
		if ($result === false)
			$this->throwError('Your input was wrong or mysqli couldn`t interpret it', $template);
		if ($result === true)
			return true;
		if ($result->num_rows == 0)
			return null;

		$resultObject = new dbcResult($result, $result->num_rows);
		return $resultObject;
	}

	/**
	 * Fehlerbehandlung
	 */
	public function throwError($text, $var = NULL)
	{
		if (!is_string($text))
			$text = 'Error text wasn\'t a string!';
		if ($var != NULL)
			var_dump($var);

		trigger_error($text);
		exit;
	}

	public function throwWarning($text)
	{
		if (!isset($GLOBALS['warning']))
			$this->throwError('Not using base.class properly');

		if (!is_string($text))
			$this->throwError('Warning text wasn\'t a string', $text);
		else
			$GLOBALS['warning'][] = $text;
		return false;
	}

}
