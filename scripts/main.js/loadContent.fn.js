/*
 * Lädt Inhalt nach.
 *
 * @param e Pfad, HTMLElement oder Event
 * @param post POST-Inhalte, falls nicht gesetzt, ein GET Request wird ausgeführt
 * @param ct Content-Type Header, falls nicht gesetzt, aber POST Request:
 *						application/x-www-form-urlencoded
 * @author Cédric Neukom
 */
function loadContent(evt, post, ct) {
	var e;
	// Prüfe nicht, ob AJAX unterstützt wird, da bereits beim Initialisieren geprüft

	// Falls HashchangeEvent (im Verlauf zurück oder vorwärts navigiert)
	if(evt instanceof Event && evt.type == 'hashchange')
		// Hole Pfad aus location
		e = '/'+location.hash.substr(1);

	else {
		// Element aus Event extrahieren
		if(evt instanceof Event)
			e = evt.srcElement ? evt.srcElement : evt.target; // IE 8
		else
			e = evt;
	
		// Pfad aus Element extrahieren
		if(e instanceof HTMLElement)
			if(e.href)
				e = e.href;

		// Falls kein Pfad übermittelt
		if(typeof e != 'string' || !e.match(/^https?:/))
			throw "No path specified";

		location.hash = e.match(/^https?:\/\/[^/]+\/(.*)$/)[1];
	}

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
			// Update übernehmen
			var x = xhr.responseXML.rootElement ?
			xhr.responseXML.rootElement : // IE 8
			xhr.responseXML.documentElement.childNodes;
			var a;

			for(var i = 0; i < x.length; i++)
				switch(x[i].tagName) {
					case 'title':
						// neuen Dokumenttitel übernehmen
						document.title = x[i].firstChild.nodeValue;
						break;

					case 'section':
						// Feldinhalt aktualisieren
						if((a = document.querySelector(x[i].getAttribute('target'))))
							a.initialize(x[i]);
						break;

					case 'link':
					case 'script':
						// Styles und Scripts nachladen, falls nötig

						// Attribute sammeln; querySelector vorbereiten
						var attr = [];
						for(var n = 0; n < x[i].attributes.length; n++)
							attr.push(x[i].attributes[n].name+'="'+x[i].attributes[n].value+'"');

						// Prüfen, ob ein Element mit den Attributen bereits gefunden werden kann
						if(!document.querySelector(x[i].tagName+'['+attr.join('][')+']')) {
							var tag = document.createElement(x[i].tagName);
							for(var n = 0; n < x[i].attributes.length; n++)
								tag.setAttribute(x[i].attributes[n].name, x[i].attributes[n].value);

							// script/link Tag einhängen; dokument nachladen
							if(document.head)
								document.head.appendChild(tag);
							else
								document.body.appendChild(tag); // IE 8
						}
						break;

					case 'report':
						// Meldung vom Server für Benutzer anzeigen
						report(x[i].getAttribute('content'), parseInt(x[i].getAttribute('level')));
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

// registriere HashchangeEvent: ermöglicht navigieren im Browserverlauf
registerEvent('hashchange', loadContent);