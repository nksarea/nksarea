/*
 * Zeigt eine Meldung im Informationsbereich an.
 *
 * @param message
 * @param level bestimmt über die CSS-Klasse und beinflusst somit
 *				direkt das Layout der Meldung.
 * @author Cédric Neukom
 */
function report(message, level) {
	// Level validieren
	if(level === null)
		level = 1;
	else
		level = parseInt(level);
	if(isNaN(level) || level < 1 || level > 5)
		return report("The given level was invalid. Allowed are only the levels 1 to 5.", 5);

	var box = document.createElement('div');
	box.className = 'report'+level;
	box.appendChild(document.createTextNode(message));
	insertBox(box);
}