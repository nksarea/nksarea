<?php
//Der openTag in den Templates
//	Standard: %{
define('sTE_openTag', '%{');

//Der closeTag in den Templates
//	Standard: }%
define('sTE_closeTag', '}%');

//Falls XHTML-Konform der schliessende Slash bei standalone Tags
define('sTE_closeSlash', '/');

/** simple Template Engine
 *
 * Dient der strikten Trennung von Programmcode (PHP), Markupcode
 * (X- / HTML / XML), Stylesheets (CSS) und Clientseitigem Programm-
 * code (JS).
 *
 * Kann sowol bei grossen als auch bei kleineren Projekten eingesezt
 * werden und sehr dienlich sein.
 *
 * @author cedl.ch
 * @link http://cedl.ch
 */
class Template {
	/** Elemente die in den <head>-Bereich eingefügt werden (falls vorhanden).	*/
	private $head_assign = array(
		'title' => null,
		'scripts' => array(),
		'styles' => array(),
		'meta' => array(),
		'links' => array()
	);

	/** Enthält Elemente, die irgendwo eingefügt werden sollen					*/
	private $assign = array();

	/** Enthält das Template													*/
	private $template = false;

	/** Verhindert, dass create bei __destruct aufgerufen wird					*/
	private $denyCreate = false;

	/** Erstellt ein neues Template-Objekt.
	 *
	 * Dieser Konstruktor kann unabhängig davon, ob das Objekt als root dienen
	 * soll, oder nicht, verwendet werden.
	 * 
	 * @param String $source Pfad zur Quelldatei
	 */
	public function __construct($source) {
		$this->template = file_get_contents($source);
	}

	/** Destruktor führt falls nicht gesperrt create() aus						*/
	public function __destruct() {
		if(!$this->denyCreate)
			$this->create();
	}

	/** Stellt Alternativ-String zur Verfügung									*/
	public function  __toString() {
		return $this->create(true);
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

	/** Fügt ein Element an einem Ort an einer Bestimmten Position ein
	 *
	 * @param String $where Die Zielvariable
	 * @param Mixed $element Das anzufügende Element (Template-Objekt oder String)
	 * @param Integer $position Die Position (Standardmässig zu hinterst)
	 * @return Boolean|Nothing Gibt false zurück, wenn ein Fehler auftrat
	 */
	public function assign($where, $element, $position = null) {
		if(strpos($this->template, sTE_openTag.$where.sTE_closeTag) === false)
			return false;
		if(!is_int($position))
			$position = count($this->assign[$where]);
		if(!is_string($element) && !is_object($element))
			return false;
		if(is_object($element))
			if(get_class() != get_class($element))
				return false;
		if(!is_array($this->assign[$where]))
			$this->assign[$where] = array();
		$assign_end = array_slice($this->assign[$where], $position);
		$this->assign[$where][$position] = $element;
		foreach($assign_end as $a)
			$this->assign[$where][++$position] = $a;
	}

	/** Erstellt das Dokument / die Ausgabe aus dem Template
	 *
	 * @param Boolean $return gibt an, ob das Dokument zurückgegeben (true) oder ausgegeben (false) wird
	 * @param Boolean $removeRNT gibt an, ob die Zeichen \r, \n und \t automatisch entfernt werden sollen
	 * @param Boolean $untouchAutoCreate gibt an, ob die AutoCreate verweigerung geändert werden darf
	 * @return String|Noting Gibt das Dokument zurück, falls $return = true, ansonsten nichts
	 */
	public function create($return = false, $removeRNT = true, $untouchAutoCreate = false) {
		$places = array();
		$code = array();
		foreach($this->assign as $k => $v) {
			$places[] = sTE_openTag.$k.sTE_closeTag;
			$code[] = implode('', $v);
		}
		$places[] = '</head>';
		$code[] = $this->getHeadElements() .'</head>';
		$build = str_replace($places, $code, $this->template);
		$build = preg_replace(	'/'.str_replace('/', '\\/', sTE_openTag).'.*'.str_replace('/', '\\/', sTE_closeTag).'/',
								'',
								$build);
		if($removeRNT)
			$build = str_replace(array("\r", "\n", "\t"), '', $build);
		if(!$untouchAutoCreate)
			$this->denyCreate = true;
		if($return)
			return $build;
		else
			echo $build;
	}

	/** Erstellt aus den für den <head>-Bereich definierten Werten den
	 * entsprechenden HTML-Code.
	 */
	protected function getHeadElements() {
		$build = '';
		if($this->head_assign['title'] !== null)
			$build .= '<title>'.$this->head_assign['title'].'</title>';
		foreach($this->head_assign['scripts'] as $src)
			$build .= '<script type="text/javascript" src="'.$src.'"></script>';
		foreach($this->head_assign['styles'] as $src)
			$build .= '<link rel="stylesheet" type="text/css" href="'.$src.'"/>';
		foreach($this->head_assign['links'] as $src) {
			$build .= '<link';
			foreach($src as $a => $v)
				$build .= ' '.$a.'="'.$v.'"';
			$build .= sTE_closeSlash.'>';
		}
		foreach($this->head_assign['meta'] as $src)
			if($src['nameIsEquiv'])
				$build .= '<meta http-equiv="'.$src['name'].'" content="'.$src['content'].'"'.sTE_closeSlash.'>';
			else
				$build .= '<meta name="'.$src['name'].'" content="'.$src['content'].'"'.sTE_closeSlash.'>';
		return $build;
	}

	/** Verweigert AutoCreate (oder erlaubt es wieder)
	 *
	 * @param Boolean $deny gibt an, ob AutoCreate verweigert wird
	 */
	public function denyAutoCreate($deny = true) {
		if($deny)
			$this->denyCreate = true;
		else
			$this->denyCreate = false;
	}

	/** Setzt den Titel des Dokuments (nur falls ein <head>-Bereich existiert
	 *
	 * @param <type> $title Der (neue) Titel des Dokuments
	 */
	public function setTitle($title) {
		$this->head_assign['title'] = $title;
	}
}