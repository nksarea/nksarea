/*
 * Fügt eine Dateibox zum InfoContainer hinzu und gibt sie zurück.
 *
 * @author Cédric Neukom
 * @param targetName Der Name des Uploadziels
 * @param fileName Der Name der Datei
 * @param fileSize Der Maximalwert der ProgressBar
 */
function insertFileBox(targetName, fileName, fileSize) {
	// Beschriftung: Dateiname
	var label = document.createElement('label');
	label.appendChild(document.createTextNode(fileName));

	// Fortschrittsanzeige: maximaler Fortschritt ist erreicht, wenn ganze Datei hochgeladen ist
	var progress = document.createElement('progress');
	progress.max = fileSize;
	progress.value = 0;

	// Uploadziel
	var target = document.createElement('address');
	target.appendChild(document.createTextNode(targetName));

	// Dateibox erstellen; füllen
	var box = document.createElement('div');
	box.className = 'file';
	box.appendChild(label);
	box.appendChild(progress);
	box.appendChild(target);

	// Box einfügen und zurück geben
	insertBox(box);
	return box;
}