$(document).ready(function(){
	var slideshows = new Array();

	$('.box').each(function(){
		var id = $(this).attr('id');
		var folder = $('.slideshow', '#' + id).attr('folder');
		if(id == undefined){
			return;
		}
		$.get('php/createDiashow.php?config=' + folder + '&folder=' + folder, function(data){
			slideshows[slideshows.length] = new Slideshow;
			slideshows[slideshows.length - 1].construct(id, data);
		}, 'json');
	});
	
	function Slideshow() {
		var container;
		var slidesContainer = '';
		var data;
		var slides;
		var config = new Object();

		config.slideWidth = 'fit';
		config.slideHeight = 'auto';
		config.slideDistance = 10;
		config.slideHref = true;
		config.loadOffset = 4;
		config.position = -1;
		config.containerWidth = 0;
		config.containerMarginLeft = 0;
		
		
		this.construct = function(par1, par2){			
			container = document.getElementById(par1);
			slides = container.getElementsByClassName('slide');
			data = par2;
			
			slidesContainer = document.createElement('div');
			slidesContainer.setAttribute('class', 'slideInner');
			getComponent('slidesContainer').appendChild(slidesContainer);

			slidesContainer = getComponent('slideInner');
			changeSlide('right');
			
			$('.control', container).mousedown(function(){
				changeSlide(($(this).hasClass('left') === true) ? 'left' : 'right')
			});
		}
		
		function changeSlide(direction){
			config.position = (direction == 'right') ? config.position+1 : config.position-1;
			for(var i = 0; i < config.loadOffset; i++){
				if(slides[config.position + i] == undefined && data.slides[config.position + i] != undefined)
					loadSlide(config.position + i);
			}
			config.containerMarginLeft -= slides[config.position].offsetLeft;

			if(config.position==0){
				hide('left');
			} else{
				show('left');
			}

			if(config.position==data.slides.length - 1){
				hide('right');
			} else{
				show('right');
			}
			$('.slideInner', container).animate({
				'marginLeft' : config.containerMarginLeft
			});
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
			img.style.marginRight = slide.width  + (config.slideDistance/2) +  'px';
			img.style.marginTop = slide.height  + 'px';
			img.style.marginBottom = slide.height + 'px';
			img.setAttribute('src', img_data.src);
			a.setAttribute('href', img_data.href);
			a.setAttribute('target', '_blank');
			div.setAttribute('class', 'slide');

			a.appendChild(img);if(config.slideHref === true){div.appendChild(a)}else{div.appendChild(img)};slidesContainer.appendChild(div);
			
			slides = container.getElementsByClassName('slide');
			config.containerWidth += img_data.width + (slide.width*2) + config.slideDistance;
			slidesContainer.style.width = config.containerWidth + 'px';
		}
		
		function hide(className){
			var element = getComponent(className);
			if(!element)
				return;
			element.style.display = 'none';
		}
		function show(className){
			var element = getComponent(className);
			if(!element)
				return;
			element.style.display = 'block';
		}
		function getComponent(className){
			var element = container.getElementsByClassName(className)[0];
			if(element == undefined)
				return false;
			return element;
		}
	}
});


