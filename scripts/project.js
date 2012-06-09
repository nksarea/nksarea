$(document).ready(function(){ 
	changeFolder('/');
	setTimeout(function() {
		var color = $('.title')[0].style.color;
		$('.button').each(function(index){
			var top = this.parentNode.offsetTop + 20 + index * 60;
			$(this).css('top', top);
			$(this).css('background-color', color);
		});
	},50);
});

function changeFolder(path){
	var elementOld = $('.show[data-path]"]');
	var elementNew = $('[data-path="' + path + '"]');
	var head = $('.files-box .head .path');
	var title = $('.title')[0];
	$(head).empty();
	
	var splitPath = path.split('/');
	for(var index in splitPath)
	{
		if(index == splitPath.length -1)
			continue;
		var img = document.createElement('img');
		img.setAttribute('src', 'styles/images/path-separator.png');
		var div = document.createElement('div');
		div.setAttribute('class', 'text');
		div.setAttribute('onclick', 'changeFolder("' + splitPath.slice(0, parseInt(index) + 1).join('/') + '/")');
		
		if(index == 0)
		{
			var txt = document.createTextNode(title.innerHTML);
			div.style.color = title.style.color;
		}
		else
		{
			var txt = document.createTextNode(splitPath[index]);
		}
		
		div.appendChild(txt);
		head.append(div);
		head.append(img);
	}
	
	var heightOld = $(elementOld).css('height');

	$(elementOld).animate({
		height: $(elementNew).css('height')
	}, 250, function() {
		$(elementOld).attr('class', 'hidden');
		$(elementOld).css('height', heightOld);
		$(elementNew).attr('class', 'show');
	});
}
