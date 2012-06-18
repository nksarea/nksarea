/*
 * Fügt einen input-Wert mit der neuen Datei hinzu
 *
 * @author Cédric Neukom
 */
function storeProjectFile(e) {
	// Datei-ID auslesen
	var filename = e.target.responseText;

	// input-Wert als HTMLElement erzeugen und einhängen
	var d = document.createElement('div');
	d.className = 'hidden';
	d.dataset.input = 'projectFile';
	d.appendChild(document.createTextNode(filename));
	document.body.appendChild(d);
}