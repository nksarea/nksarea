<?php
/** Fügt Datei bzw. Ordner dem Archiv hinzu
 *
 * $path kann ein assoziatives Array sein; in diesem Falle wird der Schlüssel als
 * (zip-interner) Pfad zum Verzeichnis aufgefasst, in dem der / die, mit dem Wert
 * angegebene, Ordner / Datei hinzugefügt wird. Der Pfad aus dem Schlüssel eines
 * Assocs (assoziatives Array) wird dem, mit $localprefix angegebenen, Pfad nach-
 * gestellt.
 *
 * @param string|ZipArchive $archive Dateiname oder Referenz auf ZipArchive-Objekt
 * @param string|array $path Datei- oder Ordnerpfad bzw -pfade
 * @param string $localprefix Pfad im Zip-Archiv (Ordner), in welchen die Pfade importiert werden
 * @return bool Bei Erfolg true, ansonsten false
 * @todo Funktionsprüfung
 * @todo Dateien die mit . beginnen ignorieren?
 * @todo KEINE Fortschrittsanzeige da relativ schnell
 */
function ZipPack($archive, $path, $localprefix = '') {
	if(is_string($archive) && is_file($archive)) {
		$arch = new ZipArchive();
		$arch->open($archive);
		$archive = $arch;
		unset($arch);
	} else if(get_class($archive) !== 'ZipArchive')
		return false;

	if(is_array($path))
		foreach($path as $k => $v)
			if(is_string($k)) {
				if(!ZipPack($archive, $v, $localprefix.'/'.$k))
					return false;
			} else {
				if(!ZipPack($archive, $v, $localprefix))
					return false;
			}
	else if(is_string($path)) {
		if(is_file($path))
			$archive->addFile($path, trim($localprefix.'/'.basename($path), '/'));
		else if(is_dir($path)) {
			$entries = scandir($path);
			foreach($entries as $entry)
				if($entry !== '.' && $entry !== '..') //hier ignorieren
					if(is_dir($path.'/'.$entry)) {
						if(!ZipPack($archive, $path.'/'.$entry, $localprefix.'/'.$entry))
							return false;
					} else if(is_file($path.'/'.$entry)) {
						if(!ZipPack($archive, $path.'/'.$entry, $localprefix))
							return false;
					}
		} else
			return false;
	} else
		return false;
	return true;
}