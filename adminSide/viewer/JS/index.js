const zoomButton = document.getElementById('zoom');
const input = document.getElementById('inputFile');
const currentPage = document.getElementById('current_page');
const canvasViewer = document.querySelector('.pdf-viewer'); // for PDF rendering
const docViewer = document.getElementById('docViewer'); // for images and Word docs

let currentPDF = {};

function resetCurrentPDF() {
	currentPDF = {
		file: null,
		countOfPages: 0,
		currentPage: 1,
		zoom: 1.5
	};
}


input.addEventListener('change', event => {
	const inputFile = event.target.files[0];
	const fileType = inputFile.type;

	// Hide both viewers first
	canvasViewer.classList.add('hidden');
	docViewer.classList.add('hidden');

	if (fileType === 'application/pdf') {
		const reader = new FileReader();
		reader.readAsDataURL(inputFile);
		reader.onload = () => {
			loadPDF(reader.result);
			zoomButton.disabled = false;
		};
	} else if (fileType.startsWith('image/')) {
		docViewer.innerHTML = `<img src="${URL.createObjectURL(inputFile)}" style="max-width: 100%; max-height: 600px;" />`;
		docViewer.classList.remove('hidden');
	} else if (fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
		docViewer.innerHTML = `<iframe src="https://docs.google.com/gview?url=${URL.createObjectURL(inputFile)}&embedded=true" 
								style="width:100%; height:600px;" frameborder="0"></iframe>`;
		docViewer.classList.remove('hidden');
	} else {
		alert("Unsupported file type. Please upload a PDF, image, or Word document.");
	}
});

zoomButton.addEventListener('input', () => {
	if (currentPDF.file) {
		document.getElementById('zoomValue').innerHTML = zoomButton.value + "%";
		currentPDF.zoom = parseInt(zoomButton.value) / 100;
		renderCurrentPage();
	}
});

document.getElementById('next').addEventListener('click', () => {
	if (currentPDF.currentPage < currentPDF.countOfPages) {
		currentPDF.currentPage += 1;
		renderCurrentPage();
	}
});

document.getElementById('previous').addEventListener('click', () => {
	if (currentPDF.currentPage > 1) {
		currentPDF.currentPage -= 1;
		renderCurrentPage();
	}
});

function loadPDF(data) {
	const pdfFile = pdfjsLib.getDocument(data);
	resetCurrentPDF();
	pdfFile.promise.then((doc) => {
		currentPDF.file = doc;
		currentPDF.countOfPages = doc.numPages;
		canvasViewer.classList.remove('hidden');
		document.querySelector('main h3').classList.add("hidden");
		renderCurrentPage();
	});
}

function renderCurrentPage() {
	currentPDF.file.getPage(currentPDF.currentPage).then((page) => {
		const context = canvasViewer.getContext('2d');
		const viewport = page.getViewport({ scale: currentPDF.zoom });
		canvasViewer.height = viewport.height;
		canvasViewer.width = viewport.width;

		const renderContext = {
			canvasContext: context,
			viewport: viewport
		};
		page.render(renderContext);
	});
	currentPage.innerHTML = `${currentPDF.currentPage} of ${currentPDF.countOfPages}`;
}
