/*
 * Fügt einen input-Wert mit der Datei-ID des hinzuzufügenden Bildes hinzu
 *
 * @author Cédric Neukom
 */
function storeProjectImage(e) {
	// Datei-ID auslesen
	var filename = e.target.responseText;

	// input-Wert erzeugen und einhängen
	var d = document.createElement('div');
	d.className = 'hidden';
	d.dataset.input = 'addImage';
	d.appendChild(document.createTextNode(filename));
	document.body.appendChild(d);

	// input-Element mit Löschlink teilen
	e.target.imageContainer.inputElem = d;
}