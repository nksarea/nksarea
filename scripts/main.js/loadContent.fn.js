/*
 * Inhaltsmanagement: Funktionen für asynchrones Laden von Inhalten und in-auftrag-
 * geben von Jobs.
 *
 * @author Cédric Neukom
 */

/*
 * Lädt Inhalt nach.
 *
 * @param e Pfad, HTMLElement oder Event
 * @param post POST-Inhalte, falls nicht gesetzt, ein GET Request wird ausgeführt
 * @param ct Content-Type Header, falls nicht gesetzt, aber POST Request:
 *						application/x-www-form-urlencoded
 */
function loadContent(e, post, ct) {
	var evt;
	// Prüfe nicht, ob AJAX unterstützt wird, da bereits beim Initialisieren geprüft

	// Element aus Event extrahieren
	if(e instanceof Event) {
		evt = e;
		e = e.srcElement ? e.srcElement : e.target; // IE 8
	}
	
	// Pfad aus Element extrahieren
	if(e instanceof HTMLElement)
		if(e.href)
			e = e.href;

	// Falls kein Pfad übermittelt
	if(typeof e != 'string' || !e.match(/^https?:/))
		throw 'No path specified';

	// Laden der Seite verhindern
	if(evt instanceof Event) {
		evt.cancelBubble = true; // IE 8
		if(evt.preventDefault)
			evt.preventDefault();
	}

	// AJAX vorbereiten
	var xhr = new XMLHttpRequest;
	xhr.open(post?'POST':'GET', e, false);
	xhr.setRequestHeader('X-Interface', 'xml'); // damit der Server erkennt, dass ein XML Dokument reicht
	if(post)
		xhr.setRequestHeader('Content-Type', ct?ct:'application/x-www-form-urlencoded');

	// Request abschicken
	xhr.send(post);

	// Antwort verarbeiten
	switch(xhr.status) {
		case 200: // OK
			var x = xhr.responseXML.rootElement ?
				xhr.responseXML.rootElement : // IE 8
				xhr.responseXML.documentElement.childNodes;
			var a;

			for(var i = 0; i < x.length; i++)
				switch(x[i].tagName) {
					case 'title':
						document.title = x[i].firstChild.nodeValue;
						break;

					case 'section':
						if((a = document.querySelector(x[i].getAttribute('target'))))
							a.initialize(x[i]);
						break;

					case 'link':
						break;

					case 'script':
						break;
				}
			break;

		case 404: // Not Found
			report("The requested page couldn't be found.", 1);
			break;

		case 403: // Forbidden
			report("You don't have permission to access the requested page.", 1);
			break;

		case 201: // Created
			var loc = xhr.getResponseHeader('Location');
			if(loc) {
				location.href = loc;
				break;
			}

		case 202: // Accepted
			var poll = xhr.getResponseHeader('X-Poll');
			if(poll &&
					(poll = poll.match(/^(\/[^ ]*) ([1-9][0-9]{0,2})$/)))
				setTimeout(loadContent, poll[2]*1000, location.protocol+'//'+location.host+poll[1]);
			else
				report("Your request was accepted. It will be processed later.");
			break;

		default:
			report("An unknown technical error occurred.", 1);
	}
}