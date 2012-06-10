/*
 * Fügt eine Meldung in eine Dateibox ein.
 *
 * @param box, in der die Meldung angezeigt werden soll.
 * @param message
 * @param level bestimmt über die CSS-Klasse und beinflusst somit
 *				direkt das Layout der Meldung.
 * @author Cédric Neukom
 */
function reportFileBox(box, message, level) {
	if(!(box instanceof HTMLElement))
		throw "box isn't an HTMLElement";

	// Level validieren
	if(typeof level == 'undefined')
		level = 1;
	else
		level = parseInt(level);
	if(isNaN(level) || level < 1 || level > 5) {
		message = "The given level was invalid. Allowed are only the levels 1 to 5.";
		level = 5;
	}

	// Level setzen
	box.classList.add('report'+level);

	// Nachricht anstelle der <progress>-Bar einfügen
	var p = document.createElement('p');
	p.appendChild(document.createTextNode(message));
	box.insertBefore(p, box.querySelector('progress'));
	box.removeChild(box.querySelector('progress'));
}