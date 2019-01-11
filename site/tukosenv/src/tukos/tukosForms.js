/*
 * Source: https://thiscouldbebetter.wordpress.com/2012/12/18/loading-editing-and-saving-a-text-file-in-html5-using-javascrip/
 */
define (["dijit/registry", "tukos/expressions"], 
    function(registry, expressions){
	return {
	    saveForm: function(saveWidget){
			var theExpressions = Array.apply(null, document.getElementsByClassName('tukosExpression')), expressionsValues = {};
			theExpressions.forEach(function(expression){
				expressionsValues[expression.id] = {formula: expressions.formulaOf(expression), value: expressions.valueOf(expression)};
			});
			var widgetsNodes = Array.apply(null, document.getElementsByClassName('tukosWidget')), widgetsValues = {};
			widgetsNodes.forEach(function(widgetNode){
                var idNode = widgetNode, id;
				while(!(id = idNode.getAttribute('data-widgetid'))){idNode = idNode.parentNode};
				widgetsValues[id] = registry.byNode(widgetNode).get('value');
			});
			var formElements = Array.apply(null, document.getElementsByClassName('tukosFormElement')), formElementsValues = {};
	   		formElements.forEach(function(formElement){
	   			formElementsValues[formElement.id] = formElement.value;
			});
	   		var textToSave = JSON.stringify({expressions: expressionsValues, widgets: widgetsValues, formElements: formElementsValues});
	   		var textToSaveAsBlob = new Blob([textToSave], {type:"text/plain"});
			var textToSaveAsURL = window.URL.createObjectURL(textToSaveAsBlob);
			var downloadLink = document.createElement("a");
			downloadLink.download = document.title + ".json";
			downloadLink.innerHTML = "Download File";
			downloadLink.href = textToSaveAsURL;
				var destroyClickedElement= function(event){document.body.removeChild(event.target);};
			downloadLink.onclick = destroyClickedElement;
			downloadLink.style.display = "none";
			document.body.appendChild(downloadLink);
			downloadLink.click();  		
	    },
	    loadForm: function(loadWidget){
	    	var fileReader = new FileReader();
    	    fileReader.onload = function(fileLoadedEvent){
    	        var loadedContent = JSON.parse(fileLoadedEvent.target.result), expressionsValues = loadedContent.expressions, widgetsValues = loadedContent.widgets, formElementsValues = loadedContent.formElements;
    			var theExpressions = Array.apply(null, document.getElementsByClassName('tukosExpression'));
    			theExpressions.forEach(function(expression){
    				expressions.setExpression(expression, expressionsValues[expression.id]);
    			});
    			var widgetsNodes = Array.apply(null, document.getElementsByClassName('tukosWidget'));
    			widgetsNodes.forEach(function(widgetNode){
                    var idNode = widgetNode, id;
    				while(!(id = idNode.getAttribute('data-widgetid'))){idNode = idNode.parentNode};
    				registry.byNode(widgetNode).set('value', widgetsValues[id]);
    			});
    			var formElements = Array.apply(null, document.getElementsByClassName('tukosFormElement'));
    	   		formElements.forEach(function(formElement){
    	   			formElement.value = formElementsValues[formElement.id];
    			});
    	    };
    	    fileReader.readAsText(loadWidget._files[0], "UTF-8");
	    }
    }
});
