/*
 * Fängt Links und ersetzt Ladevorgang der neuen Seite, falls interner Link
 *
 * @author Cédric Neukom
 */
function catchLinks(e) {
	if(!window.XMLHttpRequest)
		throw "Browser doesn't support AJAX.";

	if(!e.target)
		throw "Browser seems confused";

	// Bei Load-Event soll e.target = document sein
	if(!e.target.getElementsByTagName)
		e.target = document;

	var a = e.target.getElementsByTagName('a');
	for(var i = 0; i < a.length; i++)
		if(a[i].getAttribute('href') && a[i].getAttribute('href')[0] == '/')
			a[i].registerEvent('click', loadContent);
}

// Event global registrieren
registerEvent('load', catchLinks);
registerEvent('initialize', catchLinks);