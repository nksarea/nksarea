<?php

/** Template Engine
 *
 * Dient der strikten Trennung von Programmcode (PHP), Markupcode
 * (X- / HTML / XML), Stylesheets (CSS) und Clientseitigem Programm-
 * code (JS).
 *
 * @author Cédric Neukom (unabhängig entwickelt; angepasst)
 */
class Template extends base {

	/** Der openTag in den Templates
	 * 
	 * Standardwert: %{
	 */
	const openTag = '%{';

	/** Der closeTag in den Templates
	 * 
	 * Standardwert: }%
	 */
	const closeTag = '}%';

	/** Falls XHTML-Konform, der schliessende Slash bei generierten standalone Tags
	 *
	 * Standardwert: /
	 */
	const closeSlash = '/';

	/** Dateiname der "Root-Template-Datei"
	 *
	 * Die Root-Template-Datei ist die Daite, die standardmässig geladen wird,
	 * wenn ein neues Template inizialisiert wird, ohne dass eine Datei angegeben
	 * ist.
	 * 
	 * Standardwert: index.xhtml
	 */
	const root = 'index.xhtml';

	/** Regulärer Ausdruck, der einen einzeiligen Kommentar matcht
	 * 
	 * Standard-Wert: ##(.*?)$
	 */
	const oneline_comment = '|##(.*?)$|m';

	/** Regulärer Ausdruck, der einen mehrzeiligen Kommentar matcht
	 * Standardwert: / * ( . * ? ) * /
	*/
	const comment = '|/\*(.*?)\*/|s';

	/** Gibt an, ob beim automatischen Erstellen Zeilenumbrüche und Tabs standardmässig
	 * entfernt werden.
	 * 
	 * Standardwert: true
	 */
	const rmRNT = true;

	/** Gibt an, ob ein Element standardmässig am Ende des Skripts erstellt wird
	 * oder nicht
	 *
	 * Standardwert: false
	 */
	const denyCreate = false;

	/** Elemente die in den <head>-Bereich eingefügt werden (falls vorhanden).	*/
	protected $head_assign = array(
		'title' => null,
		'scripts' => array(),
		'styles' => array(),
		'meta' => array(),
		'links' => array()
	);

	/** Enthält Elemente, die irgendwo eingefügt werden sollen					*/
	protected $assign = array();

	/** Enthält alle Feldnamen													*/
	private $mountPoints = array();

	/** Enthält das Template													*/
	protected $template = false;

	/** Pfad zum Template-Verzeichnis (falls gegeben)							*/
	private $tmpl_dir = null;

	/** Erstellt ein neues Template-Objekt.
	 *
	 * Dieser Konstruktor kann unabhängig davon, ob das Objekt als root dienen
	 * soll, oder nicht, verwendet werden.
	 *
	 * Der Konstruktor liest die gegebene Template-Datei ein und entfernt dabei
	 * sämtliche Kommentare.
	 * 
	 * @param string $tmpl_dir Pfad Template-Verzeichnis
	 * @param string $file Dateiname (relativ zu $tmpl_dir) der Template-Datei
	 */
	public function __construct($tmpl_dir = SYS_UI_TMPL_DIR, $file = self::root) {
		// Template einlesen
		if(is_file($tmpl_dir)) {
			$this->template = file_get_contents($tmpl_dir);
			$this->tmpl_dir = dirname($tmpl_dir);
		} else if(is_dir($tmpl_dir)) {
			$this->template = file_get_contents($tmpl_dir.'/'.$file);
			$this->tmpl_dir = $tmpl_dir;
		} else
			$this->throwError('Can\'t find Template file "'.$file.'" in dir "'.$tmpl_dir.'"');

		// Kommentare entfernen
		$this->template = preg_replace(array(self::oneline_comment, self::comment), '', $this->template);

		// MountPoints auslesen
		preg_match_all('/'.str_replace('/', '\\/', self::openTag).'(.*?)'.str_replace('/', '\\/', self::closeTag).'/', $this->template, $mountPoints);
		$this->mountPoints = $mountPoints[1];
	}

	/** Gibt Template als (HTML) String zurück. */
	public function __toString() {
		return $this->createHTML(true);
	}

	/** Setzt den Titel des Dokuments
	 *
	 * @param string $key Der Wert der gesetzt werden soll (momentan nur "title")
	 * @param string $value Der Titel des Dokuments
	 */
	public function __set($key, $value) {
		switch($key) {
			case 'title':
				$this->head_assign['title'] = $value;
				break;
		}
	}

	/** Fügt ein <link> Element im Head ein, verweisend auf die Stylesheet Datei
	 *
	 * @param String $source Pfad zur Stylesheet Datei
	 */
	public function addCSS($source) {
		if(!in_array($source, $this->head_assign['styles']))
			$this->head_assign['styles'][] = $source;
	}

	/** Fügt ein <script> Element im Head ein, welches auf die Scriptdatei verweist
	 *
	 * @param String $source Pfad zur Scriptdatei
	 */
	public function addJS($source) {
		if(!in_array($source, $this->head_assign['scripts']))
			$this->head_assign['scripts'][] = $source;
	}

	/** Fügt ein <link> Element im Head ein, mit den übergebenen Attributen
	 *
	 * Die Attribute werden in der Form $key='$value' eingesetzt
	 *
	 * @param Array $attributes Die Attribute des Elements
	 */
	public function addLink($attributes) {
		$this->head_assign['links'][] = $attributes;
	}

	/** Fügt einen Meta-Tag im Head ein
	 *
	 * @param String $name name-Attribut
	 * @param String $content content-Attribut
	 * @param Boolean $nameIsEquiv gibt an, ob anstatt dem name- das http-equiv-Attribut verwendet werden soll
	 */
	public function addMeta($name, $content, $nameIsEquiv = false) {
		$this->head_assign['meta'][] = array(
			'name' => $name,
			'content' => $content,
			'nameIsEquiv' => $nameIsEquiv
		);
	}

	/** Fügt ein Element an einem Ort an einer bestimmten Position ein
	 *
	 * @param String $where Die Zielvariable
	 * @param Mixed $element Das anzufügende Element (Template-Objekt oder String)
	 * @param Integer $position Die Position (Standardmässig zu hinterst)
	 * @return Boolean Gibt false zurück, wenn ein Fehler auftrat; andernfalls true
	 */
	public function assign($where, $element, $position = null) {
		// Prüfen, ob MountPoint existiert
		if(!in_array($where, $this->mountPoints))
			return $this->throwWarning('Can\'t find MountPoint "'.$where.'"');

		// einzufügende Position bestimmen
		if(!is_int($position))
			$position = count($this->assign[$where]);

		// Nur Template-Instanzen und Strings können eingefügt werden
		if(!is_string($element) && !$element instanceof self)
			return $this->throwError('Can only assign strings and Templates');

		// Wenn MountPoint noch nicht benutzt: "anlegen"
		if(!is_array($this->assign[$where]))
			$this->assign[$where] = array();

		// Element an Position einfügen und nachfolgende Elemente nach hinten schieben
		$assign_end = array_slice($this->assign[$where], $position);
		$this->assign[$where][$position] = $element;
		foreach($assign_end as $a)
			$this->assign[$where][++$position] = $a;
		return true;
	}

	/** Fügt mehrere Elemente an mehreren Orten (zu mehreren bestimmten Positionen) ein
	 *
	 * Diese Funktion greift auf assign zurück. Die Parameter, die an assign weiter
	 * gegeben werden, lassen sich auf zwei verschiedene Arten in $assigns übergeben:
	 *
	 * <b>Möglichkeit 1</b>
	 * <code>Array (
	 * &nbsp;$where => $what
	 * )</code>
	 *
	 * Dabei kann $what ein String oder ein Template-Objekt sein, aber auch ein
	 * Array. Falls $what ein Array ist, werden alle Einträge in $what nacheinander
	 * an bei von $where eingefügt:
	 * <code>Array (
	 * &nbsp;$where => Array (
	 * &nbsp;&nbsp;$what1,
	 * &nbsp;&nbsp;$what2
	 * &nbsp;)
	 * )</code>
	 *
	 * <b>Möglichkeit 2</b>
	 * <code>Array (
	 * &nbsp;Array (
	 * &nbsp;&nbsp;$where,
	 * &nbsp;&nbsp;$what,
	 * &nbsp;&nbsp;$position
	 * &nbsp;)
	 * )</code>
	 * Respektive:
	 * <code>Array (
	 * &nbsp;Array (
	 * &nbsp;&nbsp;'where' => $where,
	 * &nbsp;&nbsp;'what' => $what,
	 * &nbsp;&nbsp;'position' => $position
	 * &nbsp;)
	 * )</code>
	 *
	 * Wobei $position weggelassen werden kann.
	 * Die erste Variante dieser Möglichkeit wird der zweiten vorgezogen.
	 *
	 * Beispiel: Der Wert in $example[0] wird dem Wert in $example['what'] vorgezogen,
	 * falls beide gegeben sein sollten.
	 *
	 * <b>Unterscheidung</b>
	 *
	 * Die Unterscheidung wird für jeden Eintrag einzenl durchgeführt. Es ist also
	 * Möglich, die beiden Möglichkeiten gleichzeitig zu verwenden!<br>
	 * Der Unterschied liegt darin, dass der Schlüssel bei <i>Möglichkeit 1</i>
	 * ein String und bei <i>Möglichkeit 2</i> ein Integer ist.
	 *
	 * @param Array $assigns (Mehrdimensionales) Array (siehe oben)
	 * @return Integer Anzahl erfolgreich hinzugefügter Elemente
	 */
	public function multiAssign($assigns) {
		$count = 0;
		// assigns extrahieren und einfügen
		foreach($assigns as $where => $what)
			if(is_string($where)) {
				if(is_array($what))
					foreach($what as $w)
						$count += $this->assign($where, $w);
				else
					$count += $this->assign($where, $what);
			} else {
				$_where = $what[0]?$what[0]:$what['where'];
				$_what = $what[1]?$what[1]:$what['what'];
				if(isset($what[2]) || isset($what['position'])) {
					$_position = isset($what[2])?$what[2]:$what['position'];
					if(is_array($_what)) {
						foreach($_what as $w)
							if($this->assign($_where, $w, $_position)) {
								$count++;
								$_position++;
							}
					} else
						$count += $this->assign($_where, $_what, $_position);
				} else
					if(is_array($_what))
						foreach($_what as $w)
							$count += $this->assign($_where, $w);
					else
						$count += $this->assign($_where, $what);
			}
		return $count;
	}

	/** Erstellt ein neues Template-Objekt und gibt dieses zurück.
	 *
	 * Falls $assigns gesetzt ist, wird multiAssign auf dieses Objekt mit $assigns
	 * als Parameter für multiAssign ausgeführt.
	 *
	 * @param String $where Zielvariable
	 * @param String $fileName Name der Template-Datei; relativ zum Template Ordner,
	 *							falls dieser gegeben ist, andernfalls relativer oder
	 *							absoluter Pfad.
	 * @param Array $assigns (Mehrdimensionales) Array; siehe multiAssign
	 * @return Template|false Das erzeugte Template-Objekt oder false, falls die
	 *							Datei nicht gefunden werden kann, oder ein Fehler
	 *							beim Einfügen auftrat (assign()).
	 */
	public function assignFromNew($where, $fileName, $assigns = null) {
		$class = get_class($this);

		// Template Datei finden und Template Instanz erstellen
		if(is_file($this->tmpl_dir.'/'.$fileName))
			$template = new $class($this->tmpl_dir.'/'.$fileName);
		else if(is_file($fileName))
			$template = new $class($fileName);
		else
			return $this->throwError('Can\'t find Template file "'.$fileName.'"');

		// Template einfügen
		if(!$this->assign($where, $template))
			return false;

		// Inhalte einfügen
		if(is_array($assigns))
			$template->multiAssign($assigns);
		return $template;
	}

	/** Gibt die Namen der Felder zurück, die noch leer sind
	 *
	 * @return array Liste der Namen der Felder
	 */
	public function getEmptyFields() {
		$emptyFields = array();
		foreach($this->mountPoints as $field)
			if($field[0] === '#' // nur Felder
					&& empty($this->assign[$field])) // nur leere
				$emptyFields[] = $field;
		return $emptyFields;
	}

	/** Erstellt HTML Dokument aus dem Template
	 *
	 * @param Boolean $return gibt an, ob das Dokument zurückgegeben (true) oder ausgegeben (false) wird
	 * @param Boolean $contentTypeHeader gibt an, falls das Dokument ausgegeben wird, ob ein Content-Type HTTP-Header versendet werden soll
	 * @param Boolean $removeRNT gibt an, ob die Zeichen \r, \n und \t automatisch entfernt werden sollen
	 * @return String|Noting Gibt das Dokument zurück, falls $return = true, ansonsten nichts
	 */
	public function createHTML($return = false, $contentTypeHeader = true, $removeRNT = self::rmRNT) {
		$places = array();
		$code = array();
		// MountPoints vorbereiten
		foreach($this->assign as $k => $v) {
			$places[] = self::openTag.$k.self::closeTag;
			$code[] = implode('', $v);
		}
		$places[] = '</head>';
		$code[] = $this->getHeadElements() .'</head>';

		// Inhalte einfügen
		$build = str_replace($places, $code, $this->template);

		// Leere MountPoints enternen
		$build = preg_replace(	'/'.str_replace('/', '\\/', self::openTag).'.*?'.str_replace('/', '\\/', self::closeTag).'/',
								'',
								$build);
		if($removeRNT)
			$build = str_replace(array("\r", "\n", "\t"), '', $build);

		if($return)
			return $build;
		else {
			if($contentTypeHeader)
				header('Content-Type: application/xhtml+xml; charset=utf-8');
			echo $build;
		}
	}

	/** Erstellt XML (Update) Dokument aus dem Template
	 *
	 * Hier werden nur Felder und <head>-Elemente berücksichtigt. Ein Feldname
	 * beginn mit dem #-Zeichen. %{LANGUAGE}% wird somit nicht überschrieben, da
	 * es sich sowieso um die selbe Sprache handelt.
	 *
	 * @param Boolean $return gibt an, ob das Dokument zurückgegeben (true) oder ausgegeben (false) wird
	 * @param Boolean $contentTypeHeader gibt an, falls das Dokument ausgegeben wird, ob ein Content-Type Header gesendet werden soll
	 * @return String|Nothing Gibt das Dokument als String zurück, falls $return = true, ansonsten nichts
	 */
	public function createXML($return = false, $contentTypeHeader = true) {
		$build = '<?xml version="1.0" encoding="utf-8" ?>'.PHP_EOL
				.'<update>';

		// Elemente für den <head>-Bereich z.B. Titel und Scripts
		$build .= $this->getHeadElements();

		// Inhalte
		foreach($this->assign as $k => $v)
			$build .= '<section target="'.$k.'" value="'.htmlspecialchars(implode('', $v)).'"/>';

		$build .= '</update>';

		if($return)
			return $build;
		else {
			if($contentTypeHeader)
				header('Content-Type: application/xml; charset=utf-8');
			echo $build;
		}
	}

	/** Erstellt aus den für den <head>-Bereich definierten Werten den
	 * entsprechenden HTML-Code.
	 */
	protected function getHeadElements() {
		$build = '';

		// Titel setzen
		if($this->head_assign['title'] !== null)
			$build .= '<title>'.$this->head_assign['title'].'</title>';

		// scripts einbinden
		foreach($this->head_assign['scripts'] as $src)
			$build .= '<script type="text/javascript" src="'.$src.'"></script>';

		// Stylesheets einbinden
		foreach($this->head_assign['styles'] as $src)
			$build .= '<link rel="stylesheet" type="text/css" href="'.$src.'"/>';

		// Links einfügen
		foreach($this->head_assign['links'] as $src) {
			$build .= '<link';
			foreach($src as $a => $v)
				$build .= ' '.$a.'="'.$v.'"';
			$build .= self::closeSlash.'>';
		}

		// Meta-Informationen einfügen
		foreach($this->head_assign['meta'] as $src)
			if($src['nameIsEquiv'])
				$build .= '<meta http-equiv="'.$src['name'].'" content="'.$src['content'].'"'.self::closeSlash.'>';
			else
				$build .= '<meta name="'.$src['name'].'" content="'.$src['content'].'"'.self::closeSlash.'>';

		return $build;
	}

}