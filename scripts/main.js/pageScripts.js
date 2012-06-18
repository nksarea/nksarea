function initPage() { 
	if($('[data-path="/"]').length == 1)
		changeFolder('/');
	
	$('[data-reply]').each(function(){
		var margin = $('[data-id="' + $(this).attr('data-reply') + '"]').css('margin-left');
		margin = parseInt(margin.replace('px', '')) + 60;
		$(this).css('margin-left', margin);
	});
	$('div[data-input]').each(function(){
		this.contentEditable = 'true';
	});
	$('select').each(function(){
		$($('option[value="' + $('option:first', this).val() + '"]', this)[1]).remove();
	});
	$('.result-box').each(function(){
		if($('.result', this).length == 0)
			$(this).remove();
	});
	$('.comment-box').each(function(){
		if($('.comment', this).length == 0)
			$(this).remove();
	});
	
	setTimeout(function() {
		var i = 0;
		var parent;
		
		$('.button').each(function(index){
			if($(this).parent().attr('class') != parent)
				i = 0;
			parent = $(this).parent().attr('class');
			var top = this.parentNode.offsetTop + 20 + i * 60;
			$(this).css('top', top);
			i += 1;
		});
	},50);
}
registerEvent('load', initPage);
registerEvent('initialize', initPage);

function submit(path){
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

	var post = '';
	for(var i in submit)
		if(typeof submit[i] == 'object')
			for(var n = 0; n < submit[i].length; n++)
				post += '&'+i+'[]='+encodeURIComponent(submit[i][n]);
		else
			post += '&'+i+'='+encodeURIComponent(submit[i]);

	loadContent(path, post.substr(1));
}

function storeProjectFile(e) {
	var filename = e.target.responseText;
	var d = document.createElement('div');
	d.className = 'hidden';
	d.dataset.input = 'projectFile';
	d.appendChild(document.createTextNode(filename));
	document.body.appendChild(d);
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

function removeComment(comment){
	$('[data-reply="' + $(comment).attr('data-id') + '"]').each(function(){
		removeComment(this);
	});

	$(comment).animate({
		height: '0px'
	}, 250, function() {
		var div = document.createElement('div');
		div.setAttribute('class', 'hidden');
		div.setAttribute('data-input', 'removeComment');
		var txt = document.createTextNode($(this).attr('data-id'));
		div.appendChild(txt);
		document.body.appendChild(div);
		
		if($('.comment').length == 1){
			$(this).parent().animate({
				height: '0px'
			}, 250, function() {
				$(this).remove();
				});
		}
		
		$(this).remove();
		$('.comment').unbind('click');
	});
}

