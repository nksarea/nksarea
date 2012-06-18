/*
 * Entfernt ein Bild aus der Slideshow, das neu hinzugefügt werden würde.
 *
 * @author Cédric Neukom
 */
function unaddProjectImage(e) {
	document.body.removeChild(e.target.inputElem);
	e.target.parentNode.parentNode.removeChild(e.target.parentNode);
}