/*
 * Inizialisiert alle Slideshows
 *
 * @author Cédric Neukom
 */
function slideshowInit(e) {
	if(!window.JSON)
		return;

	if(!e.target)
		throw "Browser seems confused";

	// Bei Load-Event soll e.target = document sein
	if(!e.target.querySelectorAll)
		e.target = document;

	// im dataSet Attribut slideshow finden sich die JSON-codierten Informationen
	// bzgl der Slideshow
	var slideshows = e.target.querySelectorAll('[fda-slideshow]');
	for(var i = 0; i < slideshows.length; i++)
		new Slideshow(slideshows[i], JSON.parse(slideshows[i].dataset.slideshow), slideshows[i].dataset.slideshowEditable?true:false);
}

// Events registrieren
registerEvent('load', slideshowInit);
registerEvent('initialize', slideshowInit);