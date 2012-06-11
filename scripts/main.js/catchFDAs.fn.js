/*
 * Fügt Events zu FileDroppingAreas und verhindert, dass losgelassene Dateien
 * angezeigt werden.
 *
 * @author Cédric Neukom
 */
function catchFDAs(e) {
	if(!window.XMLHttpRequest || !window.File || !window.FormData)
		throw "Browser doesn't support AJAX-Fileuploads.";

	if(!e.target instanceof HTMLElement)
		throw "Browser seems confused.";

	// Alle Elemente, die über einen SubmitPath verfügen, sind FDAs
	var fda = document.querySelectorAll('[data-fda-submit]');
	for(var i = 0; i < fda.length; i++) {
		fda[i].registerEvent('dragover', hoverFDA);
		fda[i].registerEvent('dragenter', hoverFDA);
		fda[i].registerEvent('drop', handleFiles);
	}
}

// Event global registrieren
registerEvent('load', catchFDAs);
registerEvent('initialize', catchFDAs);