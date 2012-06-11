/*
 * Fängt Links und ersetzt Ladevorgang der neuen Seite, falls interner Link
 *
 * @author Cédric Neukom
 */
function catchLinks(e) {
	if(!window.XMLHttpRequest)
		throw "Browser doesn\t support AJAX.";

	if(e.srcElement) // IE 8
		e.target = e.srcElement;

	if(!e.target instanceof HTMLElement)
		throw "Browser seems confused.";

	var a = e.target.getElementsByTagName('a');
	for(var i = 0; i < a.length; i++)
		if(a[i].getAttribute('href') && a[i].getAttribute('href')[0] == '/')
			a[i].registerEvent('click', loadContent);
}

// Event global registrieren
registerEvent('load', catchLinks);
registerEvent('initialize', catchLinks);