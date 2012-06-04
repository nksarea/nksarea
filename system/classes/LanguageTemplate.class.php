<?php

/**
 * Erweitert die Template Engine um die Funktionalität von Übersetzungn zwischen
 * beliebigen Sprachen. Es kann der volle Funktionsumfang der Template Engine
 * verwendet werden, diese Klasse stellt nur den Übersetzungs-Layer dar. Bei
 * allen Text-relevanten Funktionen wird automatisch übersetzt.
 *
 * Diese Klasse schaut automatisch, dass der Text in der vom Benutzer bevorzugten
 * Sprache ausgegeben wird.
 *
 * Diese Klasse verwendet den Microsoft Bing Translator und cachet die übersetzten
 * Texte lokal in einer Datenbank.
 *
 * @author Cédric Neukom
 */
class LanguageTemplate extends Template {

	protected static $lang;
	private static $accessToken;
	/** Vom Bing-Translator unterstützte Sprachen */
	private static $supportetLanguages = array("ar", "bg", "ca", "cs", "da", "nl", "en", "et", "fi", "fr", "de", "el", "ht", "he", "hi", "mww", "hu", "id", "it", "ja", "ko", "lv", "lt", "no", "pl", "pt", "ro", "ru", "sk", "sl", "es", "sv", "th", "tr", "uk", "vi");

	// static:

	/** Liest aus Accept-Header die vom Benutzer bevorzugteste Sprache, die vom
	 * Bing-Translator unterstützt wird. Falls es keine überschneidung gibt, wird
	 * "en" zurück gegeben.
	 *
	 * @return string Gibt das entsprechende Sprachkürzel zurück
	 * @link https://www.ietf.org/rfc/rfc2616.txt
	 */
	public static function getLang() {
		// Falls bevorzugte Sprache noch nicht ausgelesen: Sprachwünsche aus Accept-Header lesen
		if(!self::$lang && preg_match_all('/([a-z]{1,8})(-[a-z]{1,8})?(;q=[\.0-9]{1,4})?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
			// Ordnen nach "quality"
			array_multisort($matches[3], $matches[1]);

			if($matches[3][0] == '')
				// weil Standardwert für "quality" = 1, $matches[3] = '' bevorzugen
				$matches[1][] = array_shift($matches[1]);

			// Die Sprachen sind nun der Priorität nach aufsteigend sortiert
			// Suche nun die erste Sprache, die vom Bing-Translator unterstützt wird.
			while($matches[1]) {
				$lang = array_pop($matches[1]);
				if(in_array($lang, self::$supportetLanguages)) {
					self::$lang = $lang;
					break;
				}
			}

			// Falls keine Übereinstimmung gefunden
			if(!self::$lang)
				self::$lang = 'en';
		}
		return self::$lang;
	}

	/** Übersetzt eine Zeichenkette.
	 *
	 * Die angegebene Zeichenkette wird in die vom Benutzer bevorzugte (automatisch
	 * erkannte) Sprache übersetzt. Zunächst wird im lokalen Cache nachgeschaut,
	 * ob diese Zeichenkette bereits übersetzt wurde. Falls sie jedoch noch nicht
	 * übersetzt wurde, wird die Zeichenkette (wenn nötig gesplittet) an Bing gesendet
	 * um sie übersetzen zu lassen.
	 *
	 * @param string $string Die zu übersetzende Zeichenkette
	 * @return string Die übersetzte Zeichenkette
	 * @todo benutzer zwingen, absätze (<1000 zeichen) zu machen
	 */
	protected static function translate($string) {
		$db = getDB();
		$key = sha1($string);

		if(($res = $db->query('getTranslation', array(
			'key' => $key,
			'lang' => self::getLang()
		))) && $res->dataLength)
			return $res->dataObj->text;

		$string = explode(PHP_EOL, $string);
		while(count($string)) {
			// Unschön gelöst: Sollte sich eine Zeichenkette eingeschlichen haben,
			// die zu lang ist, wird diese nicht übersetzt
			if(strlen($tString = array_shift($string).PHP_EOL) > 1001) {
				$output .= $tString;
				$tString = '';
			}

			// Anfragestring füllen; max 1000 Zeichen
			while(count($string) &&
					!($used = false) &&
					strlen($tString)+strlen($s=array_shift($string)) < 1000 &&
					($used = true))
				$tString .= $s.PHP_EOL;
			if(!$used)
				array_unshift($string, $s);
			$tString = trim($tString);

			// curl HTTP Request erzeugen
			$ch = curl_init('http://api.microsofttranslator.com/V2/Http.svc/Translate'
				.'?text='.rawurlencode($tString)
				.'&to='.self::getLang());

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer '.self::getAccessToken(),
				'Content-Type: text/plain')
			);

			if(!($response = curl_exec($ch)))
				return curl_error($ch);

			else {
				preg_match('/^<string[^>]*>(.*)<\/string>$/', $response, $response);
				$output .= $response[1].PHP_EOL;
			}
		}

		// Übersetzung speichern
		$db->query('addTranslation', array(
			'key' => $key,
			'text' => $output,
			'lang' => self::getLang()
		));
		return $output;
	}

	/** Diese Funktion ruft einen Zugriffssclüssel ab und gibt ihn zurück.
	 *
	 * Der Zugriffsschlüssel kann gecachet werden, da er grundsätzlich länger
	 * gültig ist, als ein PHP Script zur Ausführung beanspruchen darf.
	 *
	 * @return string der Zugriffsschlüssel
	 */
	private static function getAccessToken() {
		if(!self::$accessToken) {
			$dbc = getDB();

			// Token aus Datenbank lesen
			if(($res = $dbc->query('getLangToken', array()))
					&& $res->dataObj->token)
				self::$accessToken = $res->dataObj->token;
			else {
				// Neuen Token anfordern
				// HTTP-Post Inhalt erzeugen
				$post = http_build_query(array(
					'grant_type' => 'client_credentials',
					'scope' => 'http://api.microsofttranslator.com',
					'client_id' => 'nksarea',
					'client_secret' => 's2erxdWZVJ04RN4h81DbvC16LdzEVTzcxITPfgkm35A='
				));

				// curl HTTP-Post Request erzeugen
				$ch = curl_init('https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				if(($response = curl_exec($ch)) && $response = json_decode($response)) {
					self::$accessToken = $response->access_token;
					$dbc->query('setLangToken', array(
						'token' => self::$accessToken
					));
				} else
					return false;
			}
		}
		return self::$accessToken;
	}

	// objective:

	// Die PHP-Docs wurden ab hier weggelassen, da sie sich grundsätzlich nicht
	// von den Kommentaren der Template Engine unterscheiden würden. (Die IDEs
	// greiffen an dieser Stelle auf die PHP-Docs der überschriebenen Methode
	// zurück.)

	public function __construct($source) {
		// Bevorzugte Sprache auswählen
		if(!self::$lang)
			self::$lang = self::getLang();

		// Template Engine starten
		parent::__construct($source);
		if(strpos($this->template, self::openTag.'LANGUAGE'.self::closeTag))
		$this->assign('LANGUAGE', self::$lang);
	}

	public function createHTML($return = false, $contentTypeHeader = true, $removeRNT = self::rmRNT) {
		// Inhalte übersetzen. Hier werden nur Strings übersetzt, da andere
		// LanguageTemplate-Instanzen selber übersetzen werden.
		foreach($this->assign as $k => &$assigns)
			if($k !== strtoupper($k)) // Umgebungskonstanten nicht übersetzen (z.B. Sprachkürzel)
				foreach($assigns as &$assign)
					if(is_string($assign))
						$assign = self::translate($assign);

		return parent::createHTML($return, $contentTypeHeader, $removeRNT);
	}

	public function createXML($return = false, $contentTypeHeader = true) {
		// Inhalte übersetzen. Hier werden nur Strings übersetzt, da andere
		// LanguageTemplate-Instanzen selber übersetzen werden.
		foreach($this->assign as $k => &$assigns)
			if($k !== strtoupper($k)) // Umgebungskonstanten nicht übersetzen (z.B. Sprachkürzel)
				foreach($assigns as &$assign)
					if(is_string($assign))
						$assign = self::translate($assign);

		return parent::createXML($return, $contentTypeHeader);
	}

	protected function getHeadElements() {
		// Hier wird nur der Seitentitel übersetzt, bei den anderen Tags macht
		// eine Übersetzung keinen Sinn (bei script- und link-Tags ist das relativ
		// selbsterklärend; meta-Tags werden heute auch nicht mehr für sprachliche
		// Informationen verwendet ("keywords" und "description" haben ja keinen
		// Einfluss mehr auf das Suchmaschinen-Ranking), sondern eher für technische
		// Hinweise an den Client oder die RenderingEngine.

		if($this->head_assign['title'])
			$this->head_assign['title'] = self::translate($this->head_assign['title']);

		return parent::getHeadElements();
	}

}
