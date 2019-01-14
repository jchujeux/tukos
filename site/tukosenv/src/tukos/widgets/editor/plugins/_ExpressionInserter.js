define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/dom-class", "dojo/string", "dojo/json", "dojoFixes/dojox/html/format", "tukos/TukosTooltipDialog", "tukos/utils",
		"tukos/expressions", "tukos/PageManager"], 
function(declare, lang, dct, domStyle, dcl, string, JSON, htmlFormat, TooltipDialog, utils, expressions, Pmg) {
	return declare(null, {

		constructor: function(args){
			declare.safeMixin(this,args);
		},
		inserterDialog: function(){
        	return this.dialog || (this.dialog = new TooltipDialog(this.dialogDescription()));
        },
        dialogDescription: function(){
            var widgetsDescription = {
                    value: {type: 'TextBox', atts: {label: Pmg.message('valueorformula'), style: {width: '30em'}}},
                    name: {type: 'TextBox', atts:{label: Pmg.message('expressionname')}}
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
                        expression = selection.getSelectedElement() || selection.getParentElement();
                    while((!dcl.contains(expression, 'tukosExpression')) && (expression = expression.parentNode)){};
                    if (expression){
                    	nameWidget.set('value', expression.id.slice(2));
                    	valueWidget.set('value', expressions.formulaOf(expression)) || expressions.valueOf(expression);
                    	selection.selectElement(expression);
                    }else{
                        nameWidget.set('value', '');
                        valueWidget.set('value', selection ? selection.getSelectedHtml() : '');                    	
                    }
                })
            };
        },
	    insertReplace: function(){
	    	var pane = this.dialog.pane, valueOf = lang.hitch(pane, pane.valueOf), name = valueOf('name'), value = valueOf('value'), inserter = this.inserter, editor = inserter.editor, selection = editor.selection,
	    		formula = '', expression;
	    	if (name){
		    	selection.remove();	    		
	    		if (value.trim().charAt(0) === '='){
	    			formula = value.trim();
	    			value = '';
	    		}
		    	editor.pasteHtmlAtCaret(string.substitute(expressions.template(), {name: name, value: value, formula: formula, visualPreTag: inserter.visualTag, visualPostTag: inserter.visualTag}), true);
		    	expressions.onClick(selection.getSelectedElement());
	    	}
	    },
        remove: function(){
        	var editor = this.inserter.editor, selection = editor.selection, value = expressions.valueOf(selection.getSelectedElement());
        	selection.remove();
	    	editor.execCommand('inserthtml', value);
        },
        close: function(){
        	this.inserter.close();
        }
    });
});
