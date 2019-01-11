define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/dom-class", "dojo/string", "dojo/json", "dojoFixes/dojox/html/format", "tukos/TukosTooltipDialog", "tukos/utils",
		"tukos/tukosWidgets", "tukos/PageManager"], 
function(declare, lang, dct, domStyle, dcl, string, JSON, htmlFormat, TooltipDialog, utils, tukosWidgets, Pmg) {
	return declare(null, {

		constructor: function(args){
			declare.safeMixin(this,args);
		},
		inserterDialog: function(){
        	return this.dialog || (this.dialog = new TooltipDialog(this.dialogDescription()));
        },
        dialogDescription: function(){
            var widgetsDescription = {
                    value: {type: 'Textarea', atts: {label: Pmg.message('widgetcontent'), style: {width: '50em', maxHeight: '20em'}}},
                    name: {type: 'TextBox', atts:{label: Pmg.message('widgetname')}}
            };
            ['insertReplace', 'remove', 'close'].forEach(lang.hitch(this, function(action){
                    widgetsDescription[action] = {type: 'TukosButton', atts: {label: Pmg.message(action), onClick: lang.hitch(this, this[action])}};
            }));
        	return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
                        contents: {
                        	row1: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['name', 'value']},
                        	row2: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false}, widgets: ['insertReplace', 'remove', 'close']}
                        }
                    }
                },
                onOpen: lang.hitch(this, function(){
                    var pane = this.dialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), nameWidget = paneGetWidget('name'), valueWidget = paneGetWidget('value'), selection = this.inserter.editor.selection,
                        widgetContainer = selection.getSelectedElement() || selection.getParentElement();
                    while((!dcl.contains(widgetContainer, 'tukosContainer')) && (widgetContainer = widgetContainer.parentNode));
                    if (widgetContainer){
                    	nameWidget.set('value', widgetContainer.getAttribute('data-widgetId'));
                    	valueWidget.set('value', widgetContainer.getAttribute('data-params'));
                    	selection.selectElement(widgetContainer);
                    }else{
                        nameWidget.set('value', '');
                        valueWidget.set('value', selection ? selection.getSelectedHtml() : '');                    	
                    }
                })
            };
        },
	    insertReplace: function(){
	    	var pane = this.dialog.pane, valueOf = lang.hitch(pane, pane.valueOf), value = JSON.parse(valueOf('value')), inserter = this.inserter, editor = inserter.editor, selection = editor.selection,
	    		tukosWidget;
	    	if (value.name){
		    	selection.remove();	    		
		    	tukosWidgets.targetHTML(value).then(function(html){
		    		editor.pasteHtmlAtCaret(html, true);
		    	})
	    	}
	    },
        remove: function(){
        	var editor = this.inserter.editor, selection = editor.selection;
        	selection.remove();
        },
        close: function(){
        	this.inserter.close();
        }
    });
});
