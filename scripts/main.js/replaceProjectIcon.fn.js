/*
 * Fügt einen input-Wert mit dem neuen Icon hinzu.
 *
 * @author Cédric Neukom
 */
function replaceProjectIcon(e) {
	// Datei-ID auslesen
	var filename = e.target.responseText;

	// input-Wert Element auslesen
	var d = document.querySelector('[data-input="iconFile"]');
	if(d)
		// und Datei-ID hineinschreiben
		d.firstChild.nodeValue = filename;
		
	else {
		// oder neu erzeugen
		d = document.createElement('div');
		document.body.appendChild(d);

		d.className = 'hidden';
		d.dataset.input = 'iconFile';
		d.appendChild(document.createTextNode(filename));
	}
}