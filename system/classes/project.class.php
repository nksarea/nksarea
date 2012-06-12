<?php

class project extends base
{

	private $data;
	private $versions;
	private $info;
	private $pid;

	/** Erstellt ein nneues Projekt
	 *
	 * @param integer $pid Project ID
	 * @author Lorze Widmer
	 */
	public function __construct($pid)
	{
		$this->pid = $pid;

		//Daten werden aus der Datenbank geladen
		$this->data = getDB()->query('refreshDataP', array('pid' => $this->pid));
		if ($this->data === null)
			$this->throwError('No project with given pid exists', $this->pid);
		$this->data = $this->data->dataObj;

		//Infos werden aus der Datenbank geladen und in einen assoziativen Array geladen InfoKey => InfoValue
		$temp = getDB()->query('refreshDataProjectInfo', array('pid' => $this->pid));
		$this->info = $temp;
		if ($temp !== null)
		{
			$this->info = array();
			do
				$this->info[$temp->dataObj->key] = $temp->dataObj->value;
			while ($temp->next());
		}

		//Infos werden aus der Datenbank geladen und in einen assoziativen Array geladen InfoKey => InfoValue
		$fail = (($this->data->access_level == 0) ||
				($this->data->access_level == 1 && getUser()->access_level == 1) ||
				($this->data->access_level <= 2 && $this->data->class == getUser()->class && getUser()->access_level == 2) ||
				($this->data->access_level <= 2 && getUser()->access_level == null)) && $this->data->owner != getUser()->id && getUser()->access_level != 0;

		//Im Fehlerfall wird das Skript abgebrochen und ein Fehler ausgegeben
		if ($fail)
			$this->throwError('$user has no access to the project');
	}

	public function __get($name)
	{
		switch ($name)
		{
			//private Eigenschaften
			case 'pid':
			case 'info':
				return $this->$name;
				break;
			//Werte aus der Datenbank
			case 'owner':
			case 'name':
			case 'version':
			case 'color':
			case 'description':
			case 'access_level':
			case 'upload_time':
			case 'list':
				return $this->data->$name;
				break;
			//privet Funktionen
			case 'versions':
				return $this->getVersions();
				break;
		}
	}

	public function __set($name, $value)
	{
		//Berechtigungen werden überprüft
		if (getUser()->data->id != $this->data->owner && getUser()->data->access_level != 0)
			return;

		switch ($name)
		{
			//Werte der Dantenbank die keinen refresh benötigen, weil nur ein Wert gesetzt wird
			case 'name':
			case 'description':
			case 'access_level':
			case 'list':
				getDB()->query('setProject', array('id' => $this->pid, 'field' => $name, 'value' => $value));
				$this->data->$name = $value;
				break;
			//Werte der Dantenbank die einen refresh benötigen, weil mehrere Werte gesetzt werden
			case 'owner':
				getDB()->query('setProject', array('id' => $this->pid, 'field' => $name, 'value' => $value));
				$this->data = getDB()->query('refreshDataP', array('pid' => $this->pid));
				$this->data = $this->data->dataObj;
				break;
			//privet Funktionen
			case 'icon':
				$this->setIcon($value);
				break;
			default:
				return;
		}
	}

	/** Gibt die Datei/Ordenerstruktur des Projektes zurück. Sie wird
	 * mithilfe von WinRAR aus der Datei $pid.rar gelesen
	 *
	 *  @return mixed array() mit der Datei/Ordenerstruktur, bei keiner Datei/Ordenerstruktur false
	 * @author Lorze Widmer
	 */
	public function viewContent()
	{
		//existiert keine RAR-Datei wird ein Fehler ausgegeben
		if (!is_file(SYS_SHARE_PROJECTS . $this->pid . '.rar'))
			$this->throwError('there is no RAR data for the project');

		//das Template wird gefüllt und ausgeführt
		getRAR()->execute('viewContent', array('path' => SYS_SHARE_PROJECTS . $this->pid . '.rar'));
		//mithilfe des Pluginsystemes wird die Datei/Ordenerstruktur ausgegeben
		return getRAR()->plugin('rar_parseContent');
	}
	/** Das ganze Projekt oder nur eine Datei wird zum Downlaod vorbereitet.
	 *
	 * @param string	$mask Gibt an wenn nur ein File ausgepackt und zum
	 *					Download vorbereitet wird oder ob das ganze Projekt
	 *					vorbereitet wird. $mask muss der Filename + den ganzen
	 *					Pfad in der RAR-Datei sein.
	 *  @return array mit element pfad (Pfad zur gefragten datei) und name(Name der Datei)
	 * @author Lorze Widmer
	 */
	public function prepareDownload($mask = false)
	{
		//Überprüfen der Parameter
		if (!is_file(SYS_SHARE_PROJECTS . $this->pid . '.rar'))
			$this->throwError('there is no RAR data for the project');
		if (!is_string($mask) && !is_bool($mask))
			$this->throwError('$filter is not a bool nor a string');

		//ist $mask false wird der Pfade der RAR-Datei des Projektes zurückgegeben
		if ($mask == false)
		{
			return array('path' => SYS_SHARE_PROJECTS . $this->pid . '.rar', 'name' => $this->data->name);
		}
		//sonst wird die mit $mask angegebene Datei entpackt und der Pfad zurückgegeben
		else
		{
			//um Dateikonflikte zu vermeiden wir der Name der Datei auf eine uniqid gesetzt
			$path = SYS_TMP . uniqid();
			$name = explode('\\', $mask);
			$name = $name[count($name) - 1];

			//die Datei wird entpackt
			getRAR()->execute('prepareDownload', array('path' => SYS_SHARE_PROJECTS . $this->pid . '.rar', 'mask' => $mask, 'name' => $name, 'destination' => $path));
			return array('path' => $path, 'name' => $name);
		}
	}

	public function removeProject()
	{
		//@todo
	}

	public function setVersion($version, $folder = false)
	{
		//Überprüfen der Parameter
		if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $version))
			$this->throwError('$version isn`t a version number', $version);

		//$versionfile ist der pfad zur Versions-Datei in der Form: $pid.rar-v$version
		$versionFile = SYS_SHARE_PROJECTS . $this->pid . '-v' . $version;

		//das aktuelle File wird in eine Versions-Datei umbenannt
		if (!rename(SYS_SHARE_PROJECTS . $this->pid . '.rar', SYS_SHARE_PROJECTS . $this->pid . '-v' . $this->data->version))
			$this->throwError('couldn`t rename file');

		//gibt es $versionfile nicht gibt es die Version nicht und es wird eine neue version erstellt
		if (!is_file($versionFile))
		{
			if (!is_dir(SYS_TMP . $folder))
				$this->throwError('$version isn`t currently present');
			getRAR()->execute('packProject', array('source' => SYS_TMP . $folder, 'destination' => $versionFile));
			$this->versions[] = $version;
//			Sorting array ->
		}

		$this->data->version = $version;
		getDB()->query('setProject', array('id' => $this->pid, 'field' => 'version', 'value' => $version));

		if (!rename($versionFile, SYS_SHARE_PROJECTS . $this->pid . '.rar'))
			$this->throwError('couldn`t rename file');
		return true;
	}

	public function setInfo($key, $value)
	{
		if (!is_string($key))
			$this->throwError('$key isn`t a string', $key);
		if (!is_string($value) && $value !== NULL)
			$this->throwError('$value isn`t a string nor NULL', $value);

		if ($value === NULL)
		{
			getDB()->query('removeProjectInfo', array('pid' => $this->pid, 'key' => $key));
			if (getDB()->affected_rows === 0)
				return false;
			unset($this->info[$key]);
			return true;
		}
		else if (!is_array($this->info) || empty($this->info[$key]))
			getDB()->query('addProjectInfo', array('pid' => $this->pid, 'key' => $key, 'value' => $value));
		else
			getDB()->query('setProjectInfo', array('pid' => $this->pid, 'key' => $key, 'value' => $value));
		$this->info[$key] = $value;
		return true;
	}

	private function getVersions()
	{
		if (is_array($this->versions))
			return $this->versions;

		$this->versions = array($this->data->version);

		$dir = opendir(SYS_SHARE_PROJECTS);
		while (($file = readdir($dir)) !== false)
		{
			if (strpos($file, $this->pid . '-v') !== 0)
				continue;

			$this->versions[] = str_replace($this->pid . '-v', '', $file);
		}
		closedir($dir);

//		Sorting array ->
		return $this->versions;
	}

	private function setIcon($file)
	{
		if (!is_file(SYS_TMP . $file))
			$this->throwError('$file isn`t a file');
		if (!$type = getimagesize(SYS_TMP . $file))
			$this->throwError('GD couldn`t read this image file');

		switch ($type)
		{
			case "1":
				$imorig = imagecreatefromgif(SYS_TMP . $file);
				break;
			case "2":
				$imorig = imagecreatefromjpeg(SYS_TMP . $file);
				break;
			case "3":
				$imorig = imagecreatefrompng(SYS_TMP . $file);
				break;
			default:
				$imorig = imagecreatefromjpeg(SYS_TMP . $file);
		}

		$width = imagesx($imorig);
		$height = imagesy($imorig);

		$im = imagecreatetruecolor(1, 1);
		imagecopyresampled($im, $imorig, 0, 0, 0, 0, 1, 1, $width, $height);

		$rgb = imagecolorat($im, 0, 0);
		$rgb = imagecolorsforindex($im, $rgb);

		$r = $rgb['red'] / 255;
		$g = $rgb['green'] / 255;
		$b = $rgb['blue'] / 255;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);

		switch ($max)
		{
			case 0:
				$h = 0;
				break;
			case $min:
				$h = 0;
				break;
			default:
				$delta = $max - $min;

				if ($r == $max)
					$h = 0 + ( $g - $b ) / $delta;
				else if ($g == $max)
					$h = 2 + ( $b - $r ) / $delta;
				else
					$h = 4 + ( $r - $g ) / $delta;

				$h *= 60;
				if ($h < 0)
					$h += 360;
		}
		$im = imagecreatetruecolor(196, 196);
		imagecopyresampled($im, $imorig, 0, 0, 0, 0, 196, 196, $width, $height);
		if (!imagejpeg($im, SYS_ICON_FOLDER . $this->pid . '.jpg'))
			$this->throwError('even your filesystem hates you and won`t save your image');

		getDB()->query('setProject', array('id' => $this->pid, 'field' => 'color', 'value' => $h));
		$this->data->color = $h;
	}

}
