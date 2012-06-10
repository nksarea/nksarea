/*
 * Schliesst Infobox oder bricht Dateiupload ab.
 *
 * @author Cédric Neukom
 */
function abort(e) {
	if(e.target.parentNode.xhr instanceof XMLHttpRequest &&
			!e.target.parentNode.classList.contains('complete') &&
			!e.target.parentNode.classList.contains('abort')) {
		// Dateiupload abbrechen
		e.target.classList.add('abort');
		e.target.parentNode.xhr.abort();
		reportFileBox(e.target.parentNode, "Aborted.", 2);
	} else // Box schliessen
		e.target.classList.add('close');
}