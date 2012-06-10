/*
 * Dies ist nötig, um später, beim drop-Event zugriff auf die Dateien zu erhalten.
 *
 * @author Cédric Neukom
 */
function hoverFDA(e) {
	if(e.preventDefault) {
		e.preventDefault();
		e.stopPropagation();
	}
}