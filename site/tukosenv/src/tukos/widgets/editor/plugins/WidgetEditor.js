define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/dom-class", "dojo/string", "tukos/widgets/editor/plugins/_TagEditDialog", "tukos/utils", "tukos/hiutils", 
		"tukos/tukosWidgets", "tukos/PageManager"], 
function(declare, lang, dct, domStyle, dcl, string, _TagEditDialog, utils, hiutils, tukosWidgets, Pmg) {
	var attWidgets = ['backgroundColor', 'color', 'width', 'height', 'placeHolder'],
		extraWidgets = ['name', 'type', 'value', 'topics', 'values', 'numCols', 'orientation', 'uniquechoice', 'increment', 'min', 'max', 'digits'],
		paramWidgets = extraWidgets.concat(attWidgets);
	return declare(null, {

		constructor: function(args){
			declare.safeMixin(this,args);
		},
		inserterDialog: function(){
        	return this.dialog || (this.dialog = new _TagEditDialog({
        		editor: this.editor, button: this.button,
        		dialogAtts: function(){
        			return this._dialogAtts({
        					name: {type: 'StoreComboBox', atts: {label: Pmg.message('name'), storeArgs: {}, onChange: lang.hitch(this, this.onNameChange)}},
        					//name: {type: 'TextBox', atts: {label: Pmg.message('name')}},
	        				type: {type: 'StoreSelect', atts: {label: Pmg.message('widgetType'), style: {width: '10em'}, storeArgs: {data: Pmg.messageStoreData(tukosWidgets.widgetTypes())}, onChange: lang.hitch(this, this.onTypeChange)}},
	        				value: {type: 'TextBox', atts: {label: Pmg.message('initialvalue'), style: {width: '5em'}}},
	       				 	topics: {type: 'TextBox', atts: {label: Pmg.message('topics'), style: {width: '10em'}}},
	       				 	values: {type: 'TextBox', atts: {label: Pmg.message('values'), style: {width: '10em'}}},
	        				numCols: {type: 'TextBox', atts: {label: Pmg.message('numCols'), style: {width: '5em'}}},
	        				orientation: this.storeSelectDescription(Pmg.message('orientation'), Pmg.messageStoreData(['vertical', 'horizontal'])),
	        				uniquechoice: {type: 'CheckBox', atts: {label: Pmg.message('uniquechoice')}},
	        				increment: {type: 'TextBox', atts: {label: Pmg.message('increment'), style: {width: '5em'}}},
	        				min: {type: 'TextBox', atts: {label: Pmg.message('min'), style: {width: '5em'}}},
	        				max: {type: 'TextBox', atts: {label: Pmg.message('max'), style: {width: '5em'}}},
	        				digits: {type: 'TextBox', atts: {label: Pmg.message('digits'), style: {width: '5em'}}},
	        			},
                    	{headerRow: {tableAtts: {cols: 6, customClass: 'labelsAndValues', label: Pmg.message('widgetEditor'), showLabels: true, orientation: 'vert'}, widgets: extraWidgets}},
                    	['insert', 'replace', 'remove', 'close'], {tableAtts: {cols: 4,   customClass: 'labelsAndValues', showLabels: false}, widgets: ['insert'/*, 'replace'*/, 'remove', 'close']},
                    	attWidgets
        			);
        		},
        		openDialog: function(){
                    var pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), selection = this.editor.selection, widgetContainer = selection.getSelectedElement() || selection.getParentElement(), 
                    	paramsValues, defaultParams, nameWidget = paneGetWidget('name');
                    while((!dcl.contains(widgetContainer, 'tukosContainer')) && (widgetContainer = widgetContainer.parentNode));
                    if (widgetContainer){
                    	paramsValues = tukosWidgets.getParams(widgetContainer);
                    	defaultParams = this.presentParams = tukosWidgets.defaultParams(paramsValues['type']);
                        selection.selectElement(widgetContainer);
                        paneGetWidget('insert').set('label', Pmg.message('replace'));
                    }else{
                    	paramsValues = defaultParams = this.presentParams = tukosWidgets.defaultParams();
                        paneGetWidget('insert').set('label', Pmg.message('insert'));
                    }
                	paramWidgets.forEach(function(param){
                		var paramWidget = paneGetWidget(param), absentParam = defaultParams[param] === undefined;
                		paramWidget.set('value', absentParam ? '' : paramsValues[param] || '', false);
                		paramWidget.set('hidden', absentParam);
                	});
                	hiutils.setUniqueAtt(this.editor.document, 'tukosContainer', 'data-widgetid');
                	nameWidget.store.setData(this.classNodesToStoreData('tukosContainer', 'data-widgetid'));
                	nameWidget.set('initialValue', nameWidget.get('value'));
                	return true;
        		},
                onTypeChange: function(newValue){
                	var pane = this.pane, defaultParams = this.presentParams = tukosWidgets.defaultParams(newValue), paneGetWidget = lang.hitch(pane, pane.getWidget);
                	paramWidgets.forEach(function(param){
                		var paramWidget = paneGetWidget(param), paramValue = paramWidget.get('value'), absentParam = defaultParams[param] === undefined;
                		paramWidget.set('value', paramValue || (absentParam ? '' : defaultParams[param]), false);
                		paramWidget.set('hidden', absentParam);
                	});
                	this.pane.resize();                		
                },
                onNameChange: function(newValue){
                	var nameWidget = this.pane.getWidget('name'), initialValue = nameWidget.get('initialValue');
                    if (newValue !== initialValue && nameWidget.get('store').get(newValue)){
                		Pmg.setFeedback(Pmg.message('chosenNameAlreadyExists'), '', '', true);
                		nameWidget.set('value', initialValue, false);
                	}
                },
        		insert: function(){
        	    	var pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), presentParams = this.presentParams, params = {}, editor = this.editor, selection = editor.selection;
        	    	utils.forEach(presentParams, function(defaultValue, param){
        	    		params[param] = paneGetWidget(param).get('value');
        	    	});
    		    	tukosWidgets.targetHTML(params).then(function(html){
    		    		//selection.remove();
    		    		editor.pasteHtmlAtCaret(html, true);
                        paneGetWidget('insert').set('label', Pmg.message('replace'));
    		    	});
    		    	
        	    },
                remove: function(){
                	var editor = this.editor, selection = editor.selection;
                	selection.remove();
                },
        	}));
        }
    });
});
