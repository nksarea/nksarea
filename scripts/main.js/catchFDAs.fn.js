/*
 * Fügt Events zu FileDroppingAreas und verhindert, dass losgelassene Dateien
 * angezeigt werden.
 *
 * @author Cédric Neukom
 */
function catchFDAs(e) {
	if(!window.XMLHttpRequest || !window.File)
		throw "Browser doesn\t support AJAX-Fileuploads.";

	if(!e.target instanceof HTMLElement)
		throw 'Browser seems confused.';

	//
	var fda = document.querySelector('[data-fda-submit]');
	for(var i = 0; i < a.length; i++) {
		fda.registerEvent('dragover', hoverFDA);
		fda.registerEvent('drop', handleFiles);
	}
}

registerEvent('load', catchLinks);
registerEvent('initialize', catchLinks);