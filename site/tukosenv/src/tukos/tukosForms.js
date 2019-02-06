/*
 * Source: https://thiscouldbebetter.wordpress.com/2012/12/18/loading-editing-and-saving-a-text-file-in-html5-using-javascrip/
 */
define (["dijit/registry", "tukos/expressions", "dojo/request", "tukos/PageManager"], 
    function(registry, expressions, request, Pmg){
	return {
	    saveForm: function(widget){
	   		var textToSaveAsBlob = new Blob([JSON.stringify({content: this.contentToProcess()})], {type:"text/plain"}), textToSaveAsURL = window.URL.createObjectURL(textToSaveAsBlob);
			var downloadLink = document.createElement("a");
			downloadLink.download = document.title + ".json";
			downloadLink.innerHTML = "Download File";
			downloadLink.href = textToSaveAsURL;
				var destroyClickedElement= function(event){
					document.body.removeChild(event.target);widget.set('label', label);
			};
			downloadLink.onclick = destroyClickedElement;
			downloadLink.style.display = "none";
			document.body.appendChild(downloadLink);
			downloadLink.click();  		
	    },
	    sendFormMail: function(widget){
        	var data = JSON.parse(widget.domNode.parentNode.getAttribute('data-params')), label = widget.get('label');
        	widget.set('label', Pmg.loading(label));
        	data.content = this.contentToProcess();
	    	//request("https://localhost/tukos/index20.php/tukosApp/Dialogue/backoffice/NoView/NoMode/SendEmail", 
	    	request("https://" + Pmg.getItem('tukosFormsDomainName') + "/tukos/index20.php/tukosApp/Dialogue/backoffice/NoView/NoMode/SendEmail", 
        			{method: 'POST', handleAs: 'json', data: data}).then(
        		function(response){
        			console.log('the response is: ' + response);
        			//widget.set('label', label + '... ' + Pmg.message('sent'));
        			widget.set('label', label + '... ' + response.feedback[0]);
        			setTimeout(function(){widget.set('label', label);}, 1000);
        		},
        		function(error){
        			widget.set('label', label + '... ' + Pmg.message('error'));
        			setTimeout(function(){widget.set('label', label);}, 300);
        			console.log('the error is: ', error);
        		}
        	);
	    },
	    contentToProcess: function(){
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
	   		return JSON.stringify({expressions: expressionsValues, widgets: widgetsValues, formElements: formElementsValues});
	    	
	    },
	    loadForm: function(widget){
	    	var fileReader = new FileReader();
    	    fileReader.onload = function(fileLoadedEvent){
    	        var loadedAtts = JSON.parse(fileLoadedEvent.target.result), loadedContent = JSON.parse(loadedAtts.content), expressionsValues = loadedContent.expressions, widgetsValues = loadedContent.widgets, 
    	        	formElementsValues = loadedContent.formElements, theExpressions = Array.apply(null, document.getElementsByClassName('tukosExpression'));
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
    	    fileReader.readAsText(widget._files[0], "UTF-8");
	    }
    }
});
