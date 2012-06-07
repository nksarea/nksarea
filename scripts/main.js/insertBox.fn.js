/*
 * Fügt eine Box (HTMLElement) im Informationsbereich ein.
 * Kann kein Informationsbereich (#infoContainer) gefunden werden, so wird einer
 * erstellt und in document.body eingefügt.
 *
 * @param box ein im Informationsbereich einzufügendes HTMLElement-Objekt
 * @author Cédric Neukom
 */
function insertBox(box) {
	var info = document.querySelector('#infoContainer');
	if(!info) {
		info = document.createElement('div');
		info.id = 'infoContainer';
		document.body.appendChild(info);
	}
	info.appendChild(box);
}