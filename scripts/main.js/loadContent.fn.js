var loadNoContent = false;
/*
 * Lädt Inhalt nach.
 *
 * @param evt Pfad, HTMLElement oder Event
 * @param post POST-Inhalte, falls nicht gesetzt, ein GET Request wird ausgeführt
 * @param ct Content-Type Header, falls nicht gesetzt, aber POST Request:
 *						application/x-www-form-urlencoded
 * @author Cédric Neukom
 */
function loadContent(evt, post, ct) {
	var e;
	// Prüfe nicht, ob AJAX unterstützt wird, da bereits beim Initialisieren geprüft

	if(evt instanceof Event && evt.type == 'submit') {
		// Falls Submit Event

		// submitPath aus Formular lesen
		if((e = evt.target.getAttribute('action'))[0] != '/')
			return;

		// Formulardaten serialisieren
		var query = [];
		for(var i = 0; i < evt.target.elements.length; i++)
			query.push(encodeURIComponent(evt.target.elements[i].name)
				 +'='+ encodeURIComponent(evt.target.elements[i].value));

		// Formulardaten als POST oder GET mitsenden
		if(evt.target.method.toLowerCase() == 'post')
			// POST-Request mit standard Content-Type Header
			post = query.join('&');
		else
			e += '?'+query.join('&');

	} else if(evt instanceof Event &&
			(evt.type == 'hashchange' || (evt.type == 'load'))) {
		// Falls Hashchange oder Load Event

		if(evt.type == 'load' && location.hash.length<=1)
			return; // Falls Load und kein Hash gegeben: lade nichts neues

		if(evt.type == 'hashchange' && loadNoContent) {
			loadNoContent = false;
			return; // Falls hashchangeEvent von loadContent ausgelöst wurde
		}

		// Hole Pfad aus location
		e = '/'+location.hash.substr(1);

	} else {
		// Element aus Event extrahieren
		if(evt instanceof Event)
			e = evt.currentTarget ? evt.currentTarget : (evt.target ? evt.target : evt.srcElement); // IE 8
		else
			e = evt;
	
		// Pfad aus Element extrahieren
		if(e instanceof HTMLElement)
			if(e.href)
				e = e.href;

		// Falls kein Pfad übermittelt
		if(typeof e != 'string' || !e.match(/^https?:/))
			throw "No path specified";

		loadNoContent = true;
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
			scrollBy(0, 0);
			// color-Stylesheets sind nur je für ein Dokument gültig: entferne sie
			var a;
			while((a = document.querySelector('link[href^="styles/css/color-"]')))
				a.parentNode.removeChild(a);

			// Update übernehmen
			var x = xhr.responseXML.rootElement ?
			xhr.responseXML.rootElement : // IE 8
			xhr.responseXML.documentElement.childNodes;

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
			report("The requested page couldn't be found.", 4);
			break;

		case 403: // Forbidden
			report("You don't have permission to access the requested page.", 4);
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
			report("An unknown technical error occurred.", 4);
	}
}

// registriere Hashchange-Event: ermöglicht navigieren im Browserverlauf
registerEvent('hashchange', loadContent);

// registriere Load-Event: ermöglicht das verschicken von Links aus der Adresszeile
//  und Neuladen der Seite
registerEvent('load', loadContent);

// registriere Submit-Event: ermöglicht das Abschicken von Formularen via AJAX
registerEvent('submit', loadContent);