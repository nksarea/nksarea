/*
 * Dies ist nötig, um später, beim drop-Event Zugriff auf die Dateien zu erhalten.
 *
 * @author Cédric Neukom
 */
function hoverFDA(e) {
	if(e.preventDefault) {
		e.preventDefault();
		e.stopPropagation();
	}
}