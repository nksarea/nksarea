/*
 * @author Lorze
 * @author Cédric Neukom jQuery-Abhängigkeiten entfernt, editMode und Verwaltungs-
 *					funktionen hinzugefügt, andere kleine Anpassungen
 */	
function Slideshow(container, data, editMode) {
	// inizialisiere Slideshow-Environment
	var slidesContainer = document.createElement('div');
	var slides = container.querySelectorAll('.slide');
	var config = new Object();
	var movingSlide = null;
	var autoChangeTimeout = null;

	config.slideWidth = 'fit';
	config.slideHeight = 'auto';
	config.slideDistance = 10;
	config.slideHref = true;
	config.loadOffset = 4;
	config.position = -1;
	config.containerWidth = 0;
	config.containerMarginLeft = 0;

	slidesContainer.setAttribute('class', 'slideInner');
	container.querySelector('.slidesContainer').appendChild(slidesContainer);

	var controls = container.querySelectorAll('.control');
	for(var i = 0; i < controls.length; i++)
		controls[i].registerEvent('mousedown', function() {
			changeSlide(this.className.match(/\bleft\b/) ? 'left' : 'right');
		});

	if(editMode) {// Im editMode können Slides verschoben werden
		// bei MouseMove Position ändern
		registerEvent('mousemove', changeOrder);

		// bei MouseUp Position speichern
		registerEvent('mouseup', stopMoving);

		// beim Ziehen über controls automatisch Slide wechseln
		for(var i = 0; i < controls.length; i++) {
			controls[i].registerEvent('mouseover', startAutoChange);
			controls[i].registerEvent('mouseout', stopAutoChange);
			controls[i].registerEvent('mouseup', stopAutoChange);
		}
	}

	// Zur ersten Slide wechseln
	changeSlide('right');

	function changeSlide(direction){
		if(movingSlide) // Wenn Slide herumgezogen wird, Event Auslösen, damit nicht die Maus zusätzlich bewegt werden muss
			changeOrder(direction);

		config.position = (direction == 'right') ? config.position+1 : config.position-1;
		for(var i = 0; i < config.loadOffset; i++){
			if(slides[config.position + i] == undefined && data.slides[config.position + i] != undefined)
				loadSlide(config.position + i);
		}
		config.containerMarginLeft -= slides[config.position].offsetLeft;

		if(config.position==0)
			hide('left');
		else
			show('left');

		if(config.position==data.slides.length - 1)
			hide('right');
		else
			show('right');

		container.querySelector('.slideInner').style.marginLeft = config.containerMarginLeft+'px';
	}
		
	function loadSlide(index){
		var img_data = data.slides[index];
		var img, a, div;
		var slide = new Object();
			
		switch(config.slideHeight){
			case 'auto':
				slide.height = (data.height-img_data.height)/2;
				break;
			case 'fit':
				slide.height = 0;
				break;
			default:
				slide.height = (config.slideHeight-img_data.height)/2;
				break;
		}

		switch(config.slideWidth){
			case 'auto':
				slide.width = (data.width-img_data.width)/2;
				break;
			case 'fit':
				slide.width = 0;
				break;
			default:
				slide.width = (config.slideWidth-img_data.width)/2;
				break;
		}
			
		img = document.createElement('img');
		a = document.createElement('a');
		div = document.createElement('div');
			
		img.style.width = img_data.width  + 'px';
		img.style.height = img_data.height  + 'px';
		img.style.marginLeft = slide.width + (config.slideDistance/2) + 'px';
		img.style.marginRight = slide.width  + (config.slideDistance/2) + 'px';
		img.style.marginTop = slide.height  + 'px';
		img.style.marginBottom = slide.height + 'px';
		img.setAttribute('src', img_data.src);
		a.setAttribute('href', img_data.href);
		a.setAttribute('target', '_blank');
		div.setAttribute('class', 'slide');

		a.appendChild(img);
		if(config.slideHref === true)
			div.appendChild(a);
		else
			div.appendChild(img);

		slidesContainer.appendChild(div);
			
		slides = container.getElementsByClassName('slide');
		config.containerWidth += img_data.width + (slide.width*2) + config.slideDistance;
		slidesContainer.style.width = config.containerWidth + 'px';

		// editMode ermöglicht das Entfernen von Bildern und das Ändern deren
		// Reihenfolge
		if(editMode) {
			var rm = document.createElement('a');
			rm.className = 'remove';
			rm.dataset.id = img_data.src.match(/\/([0-9a-z]+)\./)[1];
			rm.appendChild(document.createTextNode("Remove"));
			rm.registerEvent('click', removeSlide);

			div.appendChild(rm);
			//div.registerEvent('mousedown', moveSlide);
		}
	}
		
	function hide(className){
		var elements = container.getElementsByClassName(className);
		for(var i = 0; i < elements.length; i++)
			elements[i].style.display = 'none';
	}

	function show(className){
		var elements = container.getElementsByClassName(className);
		for(var i = 0; i < elements.length; i++)
			elements[i].style.display = 'block';
	}

	/*
	 * Entfernt Slide aus DOM und vermerkt sie zum Löschen
	 */
	function removeSlide(e) {
		if(!editMode)
			throw "Not allowed";

		// zum Löschen vermerken
		var rmd = document.createElement('div');
		rmd.className = 'hidden';
		rmd.dataset.input = 'removeImage';
		rmd.innerHTML = e.target.dataset.id;

		// Falls Reihenfolge geändert, Reihenfolge updaten (das entfernte Bild entfernen)
		if(document.querySelector('[data-input="orderImages"]'))
			reorderSlides();

		// Slide aus DOM entfernen
		slidesContainer.removeChild(e.target.parentNode);
	}

	/*
	 * Diese Funktionen wurden aufgrund Zeitmangels und zu kleiner Wichtigkeit
	 * vorerst weg gelassen.
	 *
	 * Slide herumziehen
	 *
	function moveSlide(e) {
		if(!editMode)
			throw "Not allowed";

		e.stopPropagation();
		e.preventDefault();

		movingSlide = e.target;
	}

	/*
	 * TODO moveable
	 *
	function changeOrder(e) {
		if(movingSlide) {
			if(e instanceof Event) {
				
			}
		}
	}

	function stopMoving(e) {
		
	}

	function reorderSlides() {
		
	}

	/*
	 * Beginnt automatisches Wechseln der Slides, wenn Slide über controls gezogen
	 *
	function startAutoChange(e) {
		if(!autoChangeTimeout)
			autoChangeTimeout = window.setInterval(changeSlide, 750, e.target.className.match(/\bleft\b/) ? 'left' : 'right');
	}

	/*
	 * Stoppt automatisches Wechseln der Slides
	 *
	function stopAutoChange() {
		if(autoChangeTimeout) {
			window.clearInterval(autoChangeTimeout);
			autoChangeTimeout = null;
		}
	}
	*/
}
