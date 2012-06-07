/* TODO handleFiles
 * Handles dropped files to upload to given target (or default)
 *
function fileDropHandler(e) {
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
 *
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
}*/