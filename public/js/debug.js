document.onreadystatechange = function () {
	if (document.readyState != "complete") return;

	function show() {
		this.parentNode.parentNode.childNodes[0].click();
	}

	function allSpread(status) {
		var elems = document.querySelectorAll(".debug-spread-all input[type=checkbox]");

		Array.prototype.forEach.bind(elems)(function (elem) {
			elem.checked = status;
		});
	}

	function showAllDebugContainer() {
		[].forEach.bind(debugContainers)(function (debugContainer) {
			if (debugContainer.classList.contains("show")) {
				debugContainer.classList.remove("show");
			} else {
				debugContainer.classList.add("show");
			}
		});
	}

	var debugContainers = document.querySelectorAll(".debug-container");

	[].forEach.bind(debugContainers)(function (debugContainer) {
		debugContainer.addEventListener("click", function (event) {
			if (event.target.classList.contains("hide")) {
				show.bind(event.target)();
			} else if (event.target.parentNode.classList.contains("debug-form-submit")) {
				var submitContainer = event.target.parentNode;
				var query = this.childNodes[0].innerText.replace(/\n/g, "&").split("&");
				var form = submitContainer.childNodes[submitContainer.childNodes.length - 1];

				form.innerHTML = "";

				for (var i = 0; i < query.length; i++) {
					var queryValues = query[i].split("=");
					var queryName = queryValues.shift();
					var queryValue = queryValues.join("=");
					var inputElement = document.createElement("input");
					inputElement.name = queryName;
					inputElement.value = queryValue;
					form.appendChild(inputElement);
				}

				form.method = event.target.innerHTML;
				form.submit();
			}
		});
	});

	document.body.addEventListener("dblclick", function (event) {
		if (!event.target.classList.contains("debug-container")) return;
		var debugContainer = event.target;

		if (debugContainer.classList.contains("debug-spread-all")) {
			allSpread(false);
			debugContainer.classList.remove("debug-spread-all");
		} else {
			debugContainer.classList.add("debug-spread-all");
			allSpread(true);
		}
	});

	document.body.addEventListener("contextmenu", function (event) {
		if (!event.target.classList.contains("debug-container")) return;
		event.preventDefault();
		var debugContainer = event.target;
		if (debugContainer.classList.contains("debug-full-screen")) {
			return document.body.removeChild(debugContainer);
		}
		var clonedDebugContainer = debugContainer.cloneNode(true);
		clonedDebugContainer.classList.add("debug-full-screen");
		document.body.appendChild(clonedDebugContainer);
	});

	var isOpera = navigator.userAgent.match(/OPR/) != null;

	window.addEventListener("keydown", function (event) {
		// if (!isOpera) return;
		if (event.which != 19) return;
		showAllDebugContainer();
	});

	if (document.cookie.split("; ").indexOf("mode=dev") > -1) {
		showAllDebugContainer();
	}
};
