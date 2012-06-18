/*
 * Zeigt das neue ProjektIcon direkt an.
 *
 * @author Cédric Neukom
 */
function insertProjectIcon(e, file) {
	if(window.URL && window.createObjectURL)
		// Projekt-Icon mittels objectURL laden
		e.target.src = URL.createObjectURL(file);

	else if(window.FileReader) {
		// Projekt-Icon als DataURL einlesen
		var reader = new FileReader();

		// Beim Laden Bild-URL setzen
		reader.onload = function(e2) {
			e.target.src = e2.target.result;
		}

		// Icon einlesen
		reader.readAsDataURL(file);
	} else
		// Benutzer informieren
		report("Cannot display the new icon while uploading. After saving your changes, the new icon will be displaid.");
}