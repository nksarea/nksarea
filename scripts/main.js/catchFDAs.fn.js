/*
 * Fügt Events zu FileDroppingAreas und verhindert, dass losgelassene Dateien
 * angezeigt werden.
 *
 * @author Cédric Neukom
 */
function catchFDAs(e) {
	if(!window.XMLHttpRequest || !window.File || !window.FormData)
		throw "Browser doesn't support AJAX-Fileuploads.";

	if(!e.target)
		throw "Browser seems confused";

	// Bei Load-Event soll e.target = document sein
	if(!e.target.querySelectorAll)
		e.target = document;

	// Alle Elemente, die über einen SubmitPath verfügen, sind FDAs
	var fda = e.target.querySelectorAll('[data-fda-submit]');
	for(var i = 0; i < fda.length; i++) {
		fda[i].registerEvent('dragover', hoverFDA);
		fda[i].registerEvent('dragenter', hoverFDA);
		fda[i].registerEvent('drop', handleFiles);
	}
}

// Event global registrieren
registerEvent('load', catchFDAs);
registerEvent('initialize', catchFDAs);