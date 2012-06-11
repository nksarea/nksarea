/*
 * Fügt eine Box (HTMLElement) im Informationsbereich ein.
 * Kann kein Informationsbereich (#infoContainer) gefunden werden, so wird einer
 * erstellt und in document.body eingefügt.
 *
 * @param box ein im Informationsbereich einzufügendes HTMLElement-Objekt
 * @author Cédric Neukom
 */
function insertBox(box) {
	// Informationsbereich finden
	var info = document.querySelector('#infoContainer');
	if(!info) { // oder anlegen
		info = document.createElement('div');
		info.id = 'infoContainer';
		document.body.appendChild(info);
	}
	// Box einfügen
	info.appendChild(box);

	// Abbrechen-Button einfügen
	var a = document.createElement('a');
	a.className = 'abort';
	a.registerEvent('click', abort);
	box.appendChild(a);
}