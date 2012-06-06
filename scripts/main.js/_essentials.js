/*
 * Enthält Initialisierungsfunktionen
 */
var INITS = [];

/*
 * Fügt einen EventListener Browserunabhängig hinzu
 * (wegen IE 8)
 */
function registerEvent(type, listener) {
	if(type == 'initialize') // simuliertes Event
		return INITS.push(listener);

	if(this.addEventListener)
		return this.addEventListener(type, listener);
	else if(this.attachEvent)
		return this.attachEvent('on'+type, listener);
	else
		throw 'Browser doesn\'t support event handling.';
};
HTMLElement.prototype.registerEvent = registerEvent;
XMLHttpRequest.prototype.registerEvent = registerEvent;

/*
 * Inizialisiert ein Element
 *
 * @param src Quellknoten
 */
HTMLElement.prototype.initialize = function(src) {
	this.innerHTML = src.getAttribute('value');
	for(var i = 0; i < INITS.length; i++)
		INITS[i]({target: this}); // schlecht simuliertes "Event"
}
