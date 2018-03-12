define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/string", "dojo/json", "dojoFixes/dojox/html/format", "tukos/TukosTooltipDialog", "tukos/utils", "tukos/hiutils",
        "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, dct, domStyle, string, JSON, htmlFormat, TooltipDialog, utils, hiutils, Pmg, messages) {

	return declare(null, {

        htmlSourceInserter: function(){
        	return this.srcDialog || (this.srcDialog = new TooltipDialog(this.srcDialogDescription()));
        },

        srcDialogDescription: function(){
            var widgetsDescription = {
                    content: {type: 'Textarea', atts: {label: messages.htmlSource, style: {width: '800px', maxHeight: '400px'}}},
                    ancestor: {type: 'TextBox', atts:{label: messages.ancestorTag}}
            };
            messages['srcRemove'] = messages['remove'];
            ['srcAncestor', 'srcInsert', 'srcRemove', 'close'].forEach(lang.hitch(this, function(action){
                    widgetsDescription[action] = {type: 'TukosButton', atts: {label: messages[action], onClick: lang.hitch(this, this[action])}};
            }));
        	return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false},
                        contents: {
                        	row1: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['content']},
                        	row2: {
                        		tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false},
                        		contents: {
                        			col1: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'}, widgets: ['ancestor']},
                        			col2: {tableAtts: {cols: 4, showLabels: false}, widgets: ['srcAncestor', 'srcInsert', 'srcRemove', 'close']}
                        		}
                        	}
                        }
                    }
                },
                onOpen: lang.hitch(this, function(){
                	console.log('opening tooltip');
                    var pane = this.srcDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), contentWidget = paneGetWidget('content'), selection = this.editor.selection, ancestorTag = paneGetWidget('ancestor').get('value');
                	contentWidget.set('value', selection.getSelectedHtml());
                })
            };
        	
        },

	    srcInsert: function(){
	    	var pane = this.srcDialog.pane, valueOf = lang.hitch(pane, pane.valueOf), content = valueOf('content');
	    	if (content){
		    	this.editor.execCommand('inserthtml', content);
	    	}
	    },

        srcRemove: function(){
        	//hiutils.removeNode(this.editor.selection.getSelectedElement());
        	this.editor.selection.remove();
        },
        
        srcAncestor: function(){
        	var pane = this.srcDialog.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), tagName = paneGetWidget('ancestor').get('value'),
        		selection = this.editor.selection, ancestorElement = tagName ? selection.getAncestorElement(tagName) : selection.getParentElement();
        	selection.selectElement(ancestorElement);
        	paneGetWidget('content').set('value', htmlFormat.prettyPrint(ancestorElement.outerHTML, 2));
        }      
    });
});
