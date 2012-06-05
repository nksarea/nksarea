var input, table;
window.onload = function() {
	input = document.getElementById('input');
	input.onkeyup = checkinput;
}

function checkinput() {
	if(input.value.length < 1)
		return;
	var parse = input.value;
	var select = '<td><select><option>Nicht verwenden</option><option>Name</option><option>Vorname</option><option>E-Mail</option></select></td>'
	var cells = 0;
	table = document.createElement("table");
	document.body.firstChild.removeChild(input);
	document.body.firstChild.appendChild(table);
	var th = document.createElement("thead");
	var tb = document.createElement("tbody");
	table.appendChild(th);
	table.appendChild(tb);
	parse = parse.split('\n');
	if(parse[0].toLowerCase().indexOf("name") > -1)
		var isHeadline = true;
	for(i in parse) {
		if(isNaN(parseInt(i)))
			continue;
		if(parse[i].length == 0) {
			delete parse[i];
			continue;
		}
		parse[i] = parse[i].split('\t');
		if(parse[i].length > cells)
			cells = parse[i].length;
	}
	th.innerHTML = '<tr></tr>';
	if(isHeadline)
		for(i = 0; i < cells; i++) {
			th.rows[0].innerHTML += select;
			if(typeof parse[0][i] == "string")
				switch(parse[0][i].toLowerCase().replace(' ', '').replace('-', '')) {
					case "name":
						th.rows[0].cells[i].firstChild.childNodes[1].setAttribute("selected", "selected");
						break;
					case "vorname":
						th.rows[0].cells[i].firstChild.childNodes[2].setAttribute("selected", "selected");
						break;
					case "email":
					case "mail":
					case "emailadresse":
						th.rows[0].cells[i].firstChild.childNodes[3].setAttribute("selected", "selected");
						break;
				}
		}
	else
		for(i = 0; i < cells; i++)
			th.rows[0].innerHTML += select;
	for(i in parse) {
		if(isNaN(parseInt(i)) || (isHeadline && i == 0))
			continue;
		tb.innerHTML += '<tr></tr>';
		for(c = 0; c < cells; c++) {
			if(typeof parse[i][c] != "undefined")
				tb.rows[tb.rows.length-1].innerHTML += '<td>'+parse[i][c]+'</td>';
			else
				tb.rows[tb.rows.length-1].innerHTML += '<td>'+parse[i][c]+'</td>';
		}
	}
}