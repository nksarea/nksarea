$(document).ready(function(){ 
	
	if($('[data-path="/"]').length == 1)
		changeFolder('/');
	
	$('div[data-input]').each(function(){
		this.contentEditable = 'true';
	});
	$('select').each(function(){
		$($('option[value="' + $('option:first', this).val() + '"]', this)[1]).remove();
	});
	
	setTimeout(function() {
		$('.button').each(function(index){
			var top = this.parentNode.offsetTop + 20 + index * 60;
			$(this).css('top', top);
		});
	},200);
});

function submit(){
	var submit = new Object();
	
	$('div[data-input]').each(function(){
		var id = this.getAttribute('data-input');
		
		if(typeof submit[id] == 'string')
			submit[id] = new Array(submit[id], $(this).text());
		else if(typeof submit[id] == 'object')
			submit[id][submit[id].length] = $(this).text();
		else
			submit[id] = $(this).text();
	});
	$('select[data-input]').each(function(){
		var id = this.getAttribute('data-input');
		
		if(typeof submit[id] == 'string')
			submit[id] = new Array(submit[id], $('option:selected', this).val());
		else if(typeof submit[id] == 'object')
			submit[id][submit[id].length] = $('option:selected', this).val();
		else
			submit[id] = $('option:selected', this).val();
	});
	
	alert(submit);
}

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

function removeComment(){
	$('.comment').click(function(){
		$(this).animate({
			height: '0px'
		}, 250, function() {
			var div = document.createElement('div');
			div.setAttribute('class', 'hidden');
			div.setAttribute('data-input', 'removeComment');
			var txt = document.createTextNode($(this).attr('data-id'));
			div.appendChild(txt);
			document.body.appendChild(div);
		
			$(this).remove();
			$('.comment').unbind('click');
		});
	});
}

