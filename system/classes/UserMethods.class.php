<?php

/** Methoden für Benutzer
 *
 * @author Cédric Neukom
 */
class UserMethods extends base implements Methods {

	protected $permitted = false;
	protected $minAccessLevel = 2;

	public function __construct() {
		if(!$user = getUser())
			$this->throwError('You are not permitted to use these functions, because you\'re not logged in.');
		else if($user->access_level >= $this->minAccessLevel)
			$this->throwError('Your access level isn\'t heigh enough to use these methods.');
		else
			$this->permitted = true;
	}

	public function __get($key) {
		switch($key) {
			case 'permitted':
				return $this->$key;
		}
	}

	/** Erstellt eine neue Liste
	 *
	 * @param string $name Name der Projektliste
	 * @param integer $type Typ (0: Projektliste; 1: Prüfungsliste)
	 * @param integer $class ID der zugeordneten Klasse
	 * @param integer $deadline Timestamp - Letzte Abgabemöglichkeit (Abgabe
	 * 							weiterhin möglich - hat mit Hervorhebung des
	 * 							Abgabedatums kompensiert zu werden)
	 * @return mixed false im Fehlerfalll, andernfalls die ID der Liste
	 * @author Cédric Neukom
	 */
	public static function addList($name, $type, $class, $deadline = null) {
		if(!getUser())
			return $this->throwError('You are not logged in. You can not create a list while you aren\'t logged in.');

		$type = intval($type);
		if($type > 1 && $type < 0)
			return $this->throwError('The list type you specified is invalid. Please specify a valid list type.');

		if(($idl = intval($deadline)))
			if($idl > time())
				$deadline = $idl;
			else
				return $this->throwError('The deadline isn\'t valid. Please specify a valid deadline.');
		else
			$deadline = null;

		$class = intval($class);
		if(!$class)
			return $this->throwError('The class you specified is invalid.');

		$db = getDB();
		$classes = $db->query('getClass', array('id' => $class));
		if(!$classes || !$classes->dataLength)
			return $this->throwError('The class you specified is invalid.');

		if($db->query('addList', array(
			'owner' => getUser()->data->id,
			'name' => $name,
			'type' => $type,
			'deadline' => $deadline === null ? 'NULL' : $deadline,
			'class' => $class
		)))
			return $db->insertID;
		else
			return $this->throwError('A technical error occurred. The list has not been created.');
	}
	
	public function addProject($folder, $name, $access_level, $description = NULL, $list = NULL)
	{
		$access_level = intval($access_level);
		$query = array();

		if (!is_dir(SYS_TMP . $folder))
			$this->throwError('$folder isn`t a folder');
		if (!is_string($name))
			$this->throwError('$name isn`t a string');
		if ($access_level < 0 && $acces_level > 4)
			$this->throwError('$access_level isn`t valid');
		if (!is_string($description) && $description != NULL)
			$this->throwError('$description isn`t a string nor NULL');
		if (!is_int($list) && $list != NULL)
			$this->throwError('$list isn`t a string nor NULL');

		$query['owner'] = intval(getUser()->id);
		$query['name'] = $name;
		$query['description'] = ($description === null ? 'NULL' : $description);
		$query['access_level'] = $access_level;
		$query['list'] = ($list === null ? 'NULL' : intval($list));

		$result = getDB()->query('addProject', $query);
		if (!$result)
			$this->throwError('MYSQLi hates you!');

		return getRAR()->execute('packProject', array('source' => SYS_TMP . $folder, 'destination' => SYS_SHARE_PROJECTS . getDB()->insert_id . '.rar'));
	}

	function addFile($name, $list, $file)
	{
		$query = array();
		
		if(!is_string($name))
			$this->throwError ('$name isn`t a string', $name);
		if(!is_file(SYS_TMP . $file))
			$this->throwError ('$file isn`t a file', $file);

		$query['name'] = $name;
		$query['owner'] = intval(getUser()->id);
		$query['list'] = intval($list);
		$query['mime'] = mime_content_type($file);
		
		if (getDB()->query('addProject', $query))
			$this->throwError('MYSQLi hates you!');

		return getRAR()->execute('moveFile', array('source' => SYS_TMP . $file, 'destination' => SYS_SHARE_PROJECTS . getDB()->insert_id));
	}
}
