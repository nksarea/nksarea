$(document).ready(function(){ 
	var element = $('[data-path="/"]');
	
	$(element).attr('class', 'show');
});

function changeFolder(path){
	var elementOld = $('.show[data-path]"]');
	var elementNew = $('[data-path="' + path + '"]');
	var head = $('.files-box .head');
	var projectName = $('.title')[0].innerHTML;
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
			var txt = document.createTextNode(projectName);
			div.style.color = 'red';
		}
		else
		{
			var txt = document.createTextNode(splitPath[index]);
		}
		
		div.appendChild(txt);head.append(div);head.append(img);
	}
	
	var heightOld = $(elementOld).css('height');

	$(elementOld).animate({
		height: $(elementNew).css('height')
	}, 500, function() {
		$(elementOld).attr('class', 'hidden');
		$(elementOld).css('height', heightOld);
		$(elementNew).attr('class', 'show');
	});
}
