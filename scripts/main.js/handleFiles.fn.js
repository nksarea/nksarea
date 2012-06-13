/*
 * Verarbeitet Dateiuploads. (Wenn Dateien auf eine inizialisierte FDA gezogen
 * wurden). Die Dateien werden unter Berücksichtigung der FDA-Attribute mit einer
 * Fortschrittsanzeige hochgeladen.
 *
 * @param e Event Objekt
 * @author Cédric Neukom
 */
function handleFiles(e) {
	// Eventweiterverarbeitung unterbinden
	if(e.preventDefault) {
		e.preventDefault();
		e.stopPropagation();
	}

	// Dateitypprüfung?
	var mimeMatch = e.target.dataset.fdaMime;
	if(mimeMatch)
		mimeMatch = new RegExp(mimeMatch);

	if(e.dataTransfer.files)
		for(var i = 0; i < e.dataTransfer.files.length; i++) {
			var box = insertFileBox(
						e.target.dataset.fdaName,
						e.dataTransfer.files[i].name,
						e.dataTransfer.files[i].size);

			if(e.dataTransfer.files[i].size && // nicht-leere Datei: sicher kein Ordner
					(!mimeMatch || e.dataTransfer.files[i].type)) { // keine Dateityprestriktion oder richtiger Dateityp
				// FormData vorbereiten
				var fd = new FormData;
				fd.append('file', e.dataTransfer.files[i]);

				// Upload vorbereiten
				var xhr = new XMLHttpRequest;
				xhr.box = box; // Damit können auch CallbackFunktionen auf die Box zugreiffen
				box.xhr = xhr; // Damit kann abort() über die Box auf den Request zugreiffen
				xhr.open('POST', e.target.dataset.fdaSubmit, true);

				xhr.onreadystatechange = function(e2) {
					if(this.readyState == 4) {// Wenn der Upload abgeschlossen:
						// Box anpassen
						switch(this.status) {
							case 200:
								reportFileBox(box, "Filetransfer completed.", 1);
								box.classList.add('complete');
								break;
							default:
								reportFileBox(box, xhr.responseText, 4);
						}

						// Callback ausfürhen
						if(e.target.dataset.fdaCallback) {
							var r = eval(e.target.dataset.fdaCallback);
							// Wenn Rückgabewert eine Funktion ist, führe diese aus
							// damit wird folgende Schreibweise möglich:
							//  <div data-fda-callback="myCallbackFunction" ...>
							if(typeof r == 'function')
								r(e2);
						}
					}
				}

				// Fortschritt des Uploads in der Box anzeigen
				xhr.upload.onprogress = function(e2) {
					var progress = box.querySelector('progress');
					if(progress) {
						progress.max = e2.total;
						progress.value = e2.loaded;
					}
				}

				// CallfirstFunktion ausführen, vor dem Absenden
				if(e.target.dataset.fdaCallfirst) {
					var r = eval(e.target.dataset.fdaCallfirst);
					// siehe callback
					if(typeof r == 'function')
						r(e, e.dataTransfer.files[i], xhr);
				}

				// Request absenden: Datei hochladen
				xhr.send(fd);
			} else if(e.dataTransfer.files[i].size)
				reportFileBox(box, "The file has not been uploaded: it had the wrong type.", 3);
			// Ordner können nicht hochgeladen werden.
		}
}