$(document).ready(function(){
	$('div[data-input]').each(function(){
		this.contentEditable = 'true';
	});
	$('td[data-input]').each(function(){
		this.contentEditable = 'true';
	});
	$('select').each(function(){
		$($('option[value="' + $('option:first', this).val() + '"]', this)[1]).remove();
	});
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
	$('td[data-input]').each(function(){
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
