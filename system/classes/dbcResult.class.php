<?php

class dbcResult extends base
{

	private $data;
	private $dataArray = array('dataObject' => NULL, 'dataArray' => NULL, 'dataAssoc' => NULL, 'dataRow' => NULL);
	private $dataLength = 0;
	private $pointer = 0;

	/**
	 * Das gleiche wie mysqli::__construct(), bei Erfolg der verbindung wird this::connected auf true gesetzt.
	 */
	public function __construct($data, $dataLength)
	{
		if (get_class($data) !== 'mysqli_result')
			$this->throwError('$data wasn`t a MySQLi_Result');
		if (!is_int($dataLength))
			$this->throwError('$dataLength wasn`t an int');

		$this->data = $data;
		$this->dataLength = $dataLength;
	}

	/**
	 * Wird this::pointer verändert wir gleichzeitig auch der pointer von this::data verschoben
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'pointer':
				if ($this->data->data_seek($value))
					$this->pointer = $value;
				else
					$this->throwWarning('Given offset doesn`t exist or wasn`t a number', $value);
				break;
		}
	}

	/**
	 * Zuerst werden die 'normalen' Eigenschaften behandelt, danach die, die mit der Datenbank zu tun haben.
	 * Der zurückgegebene Wert ist immer die reihe von this::data, auf der der this::pointer steht.
	 *
	 * @param type $name
	 * @return mixed im Fehlerfall false, sonst der Wert der abgerufenen Eigenschaft
	 */
	public function __get($name)
	{
		if (isset($this->dataArray[$name]) && $this->dataArray[$name] != NULL)
			return $this->dataArray[$name];

		$this->data->data_seek($this->pointer);

		switch ($name)
		{
			case 'dataObj':
				return $this->dataArray[$name] = $this->data->fetch_object();
			case 'dataAssoc':
				return $this->dataArray[$name] = $this->data->fetch_assoc();
			case 'dataArray':
				return $this->dataArray[$name] = $this->data->fetch_array(MYSQLI_NUM);
			case 'dataRow':
				return $this->dataArray[$name] = $this->data->fetch_row();
			case 'dataLength':
				return $this->dataLength;
			default:
				return false;
		}
	}

	/**
	 * Setzt this::pointer wenn möglich auf den nächsten höheren Wert
	 *
	 * @return boolean existiert der nächste index in this::data nicht wird false zurückgegeben, sonnst true
	 */
	public function next()
	{
		if (!$this->data->data_seek($this->pointer + 1))
			return false;

		$this->pointer += 1;
		$this->dataArray = array('dataObject' => NULL, 'dataArray' => NULL, 'dataAssoc' => NULL, 'dataRow' => NULL);
		return true;
	}

	/**
	 * Setzt this::pointer wenn möglich auf den nächsten tieferen Wert
	 *
	 * @return boolean existiert der nächste index in this::data nicht wird false zurückgegeben, sonnst true
	 */
	public function previous()
	{
		if (!$this->data->data_seek($this->pointer - 1))
			return false;

		$this->pointer -= 1;
		$this->dataArray = array('dataObject' => NULL, 'dataArray' => NULL, 'dataAssoc' => NULL, 'dataRow' => NULL);
		return true;
	}

}

