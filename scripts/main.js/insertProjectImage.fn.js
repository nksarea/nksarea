/*
 * Zeigt das neue Bild direkt in der Diashow an.
 *
 * @author Cédric Neukom
 */
function insertProjectImage(e, file, xhr) {
	// Bild-Element erstellen und einhängen
	var div = document.createElement('div');
	div.className = 'slide';
	e.target.querySelector('.slidesContainer').appendChild(div);

	var img = document.createElement('img');
	div.appendChild(img);

	// Bild löschbar machen
	var rm = document.createElement('a');
	rm.className = 'remove';
	rm.appendChild(document.createTextNode("Remove"));
	rm.registerEvent('click', unaddProjectImage);
	div.appendChild(rm);

	// Löschlink ins XHR speichern, damit die Callbackfunktion storeProjectImage
	// das input-Element hiermit verknüpfen kann
	xhr.imageContainer = rm;

	if(window.URL && window.createObjectURL)
		// Projekt-Icon mittels objectURL laden
		img.src = URL.createObjectURL(file);

	else if(window.FileReader) {
		// Projekt-Icon als DataURL einlesen
		var reader = new FileReader();

		// Beim Laden Bild-URL setzen
		reader.onload = function(e2) {
			img.src = e2.target.result;
		}

		// Icon einlesen
		reader.readAsDataURL(file);
	} else
		// Benutzer informieren
		report("Cannot display the new icon while uploading. After saving your changes, the new icon will be displaid.");
}