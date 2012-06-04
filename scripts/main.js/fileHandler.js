var uploadContainer;

/*
 * Adds a file-drop handler.
 *
 * obj consists of the following options:
 *
 * - target
 *    HTMLElement to listen for file droppings
 *
 * - targetName (optional)
 *    If indicated will be displaid as target of the upload
 *
 * - mimeMatch (optional)
 *    RegExp which mime type of files has to match
 *
 * - script
 *    Path to the serverside uploadscript
 *
 * - formData (optional)
 *    FormData to use: you can add your own post parameters
 */
function addFileHandler(obj) {
	if(window.FormData && window.File &&
		obj.target instanceof HTMLElement) {

		obj.target.handleFile = {};

		if(obj.targetName)
			obj.target.handleFile.targetName = obj.targetName;

		if(obj.mimeMatch instanceof RegExp)
			obj.target.handleFile.mimeMatch = obj.mimeMatch;

		if(!obj.script)
			return false;
		obj.target.handleFile.postScript = obj.script;

		if(obj.formData instanceof FormData)
			obj.target.handleFile.formData = obj.formData;

		if(obj.success)
			obj.target.handleFile.success = obj.success;

		return true;
	}
	return false;
}

/*
 * Finds nearest registered fileHandler to an Event or an HTMLElement and returns
 * it. If no fileHandler is found, this function will return false.
 */
function getFileHandler(e) {
	return (e instanceof Event ? getFileHandler(e.target) : (e instanceof HTMLElement ? (e.handleFile ? e.handleFile : (e.parentNode ? getFileHandler(e.parentNode) : false)) : false));
}

/*
 * stops propagation and prevents default of e
 */
function stopFilePropagation(e) {
	if(e instanceof Event) {
		e.preventDefault();
		e.stopPropagation();
	}
	return false;
}

/*
 * Handles dropped files to upload to given target (or default)
 */
function fileDrophandler(e) {
	var fileHandle, i;
	if(e.dataTransfer.files && (fileHandle = getFileHandler(e))) {
		var toUpload = [];
		var wrongType = [];
		stopFilePropagation(e);

		for(i = 0; i < e.dataTransfer.files.length; i++)
			if(!fileHandle.mimeMatch instanceof RegExp || e.dataTransfer.files[i].type.match(fileHandle.mimeMatch))
				toUpload.push(e.dataTransfer.files[i]);
			else if(e.dataTransfer.files[i].size)
				wrongType.push(e.dataTransfer.files[i].name);

		if(wrongType.length && toUpload.length) {
			if(!confirm('The following files had the wrong type to upload to this target: '+ wrongType +'Do you want to upload the remaining files?'))
				return;
		} else if(wrongType.length)
			alert('None of the given files had the required type to upload. No file will be uploaded.');

		for(i = 0; i < toUpload.length; i++)
			fileUpload(toUpload[i], fileHandle.targetName, fileHandle.postScript, fileHandle.formData, fileHandle.success);
	}
}

/*
 * Uploads the given file and adds a proggressbar to the uploadContainer
 */
function fileUpload(file, targetName, scriptURL, formData, success) {
	if(!file instanceof File)
		return false;

	if(!(uploadContainer instanceof HTMLElement)) {
		uploadContainer = document.createElement('div');
		uploadContainer.classList.add('uploadContainer');
		document.body.appendChild(uploadContainer);
	}

	uploadContainer.classList.add('enabled');

	var bar = document.createElement('section');
	var name = document.createElement('h1');
	var target = document.createElement('address');
	var progress = document.createElement('progress');
	var cancel = document.createElement('a');

	uploadContainer.appendChild(bar);

	bar.appendChild(cancel);
	bar.appendChild(name);
	bar.appendChild(target);
	bar.appendChild(progress);

	bar.xhr = new XMLHttpRequest;
	cancel.onclick = cancelRequest;
	name.innerHTML = file.name;
	target.innerHTML = targetName ? targetName : 'unknown target';

	if(!(formData instanceof FormData))
		formData = new FormData();
	formData.append('file', file);

	bar.xhr.parentNode = bar;
	bar.xhr.upload.onprogress = function(e) {
		// update the progress bar
		if(e.lengthComputable) {
			progress.max = e.total;
			progress.value = e.loaded;
		}
	}
	bar.xhr.onload = function(e) {
		removeBar(e);
		if(success)
			success(e);
	}
	bar.xhr.onerror = xhrError;

	bar.xhr.open('POST', scriptURL, true);
	bar.xhr.send(formData);
	return true;
}

/*
 * Marks file as done
 */
function removeBar(e) {
	if(e instanceof Event)
		if(e.target.status != 200)
			xhrError(e);
		else if(e.target.parentNode instanceof HTMLElement)
			e.target.parentNode.classList.add('done');
}

/*
 * Warns the user
 */
function xhrError(e) {
	if(e instanceof Event)
		if(e.target.parentNode instanceof HTMLElement)
			e.target.parentNode.classList.add('error');
}

/*
 * Aborts running upload
 */
function cancelRequest(e) {
	if(e instanceof Event && e.target.parentNode.xhr instanceof XMLHttpRequest) {
		if(e.target.parentNode.classList.contains('cancelled') || e.target.parentNode.classList.contains('error'))
			e.target.parentNode.classList.add('done');
		else {
			e.target.parentNode.xhr.abort();
			e.target.parentNode.classList.add('cancelled');
			e.target.parentNode.querySelector('progress').value = 0;
		}
	}
}