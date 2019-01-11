define (
	["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-construct", "dojo/ready", "dojo/on","tukos/utils","tukos/TukosTooltipDialog","tukos/widgets/ColorPalette","tukos/PageManager"], 
    function(declare, lang, domAttr, domStyle, dct, ready, on, utils, TukosTooltipDialog, colorPalette, Pmg){

    var colorIconClass = "dijitEditorIcon dijitEditorIconHiliteColor",
        sizeUnits = utils.storeData(['auto', 'cm', '%', 'em', 'px']), thicknessUnits = utils.storeData(['cm', 'em', 'px']), hAlignStoreData = Pmg.messageStoreData(['default', 'left', 'center', 'right']),
        vAlignStoreData = Pmg.messageStoreData(['top', 'middle', 'bottom']), displayStoreData = Pmg.messageStoreData(['block', 'inline', 'none']),
    	description = {backgroundColor: 'colorDescription', borderColor: 'colorDescription', color: 'colorDescription', textAlign: 'hAlignDescription', verticalAlign: 'vAlignDescription', width: 'sizeDescription',
    				   height: 'sizeDescription', margin: 'sizeDescription', display: 'displayDescription', placeHolder: 'domAttDescription', border: 'domAttDescription', cellPadding: 'domAttDescription', cellSpacing: 'domAttDescription'},
	    domAttValue = {
	        get: function(node, att){
	            return domAttr.get(node, att);
	        },
	        set: function(node, att, value){
	            value === '' ? domAttr.remove(node, att) : domAttr.set(node, att, value);
	        },
	        remove: function(node, att){
	            domAttr.remove(node, att);
	        }
	    },
	    styleAttValue = {
	        get: function(node, att){
	            return node.style[att];// we do not want the computed style, but the style set for this node
	        },
	        set: function(node, att, value){
	            domStyle.set(node, att, value);
	        },
	        remove: function(node, att){
	            domStyle.set(node, att, '');
	        }
	    };
    return declare(TukosTooltipDialog, {
        postCreate: function(){
            lang.mixin(this, this.dialogAtts());
            this.inherited(arguments);
            this.onOpen = lang.hitch(this, function(){
                this.editor.begEdit();
                if (this.prepareTable){
                    this.prepareTable();
                    this.target = this.table;
                }else{
                	var selection = this.editor.selection;
                	this.target = selection.getSelectedElement() || selection.getParentElement();
                }
                if (this.openDialog()){
                    dijit.TooltipDialog.prototype.onOpen.apply(this, arguments);
                    ready(lang.hitch(this, function(){//JCH: solves issues of empty TooltipDialog when browser window size changes after tooltipdialog has been laoded, among others ...
                        this.pane.resize();
                    }));                	
                }else{
                	dijit.popup.close(this);
                }
            });
            this.blurCallback = on.pausable(this, 'blur', this.close);
        },
        openDialog: function(){
            var target = this.target, includedAtts = this.includedAtts, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            if (this.isInsert){
                utils.forEach(this.defaultAttsValue, function(attValue, att){
                    var widget = paneGetWidget(att);
                    widget.set('value', attValue);
                });
                return true;
            }else if(target.id === 'dijitEditorBody'){
            	Pmg.setFeedback(Pmg.message('NoTagToEdit'), '', '', true);
            	this.close();
            	return false;
            }else{
                includedAtts.forEach(function(att){
                    var attWidget = paneGetWidget(att), attValue = attWidget.attValueModule.get(target, att);
                    attWidget.set('value', attValue);
                });
            	return true;
            }
        },
        apply: function(){
            var target = this.target, includedAtts = this.includedAtts, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            includedAtts.forEach(function(att){
                var attWidget = paneGetWidget(att);
                attWidget.attValueModule.set(target, att, attWidget.get('value'));
            });
        },
        _dialogAtts: function(extraWidgetsDescription, headerRowLayout, actions, actionsRowLayout, includedAtts){
            var widgetsDescription = extraWidgetsDescription || {}, attWidgetsDescription = lang.hitch(this, this.attWidgetsDescription);
            includedAtts.forEach(function(att){
                widgetsDescription[att] = attWidgetsDescription(att);
            });
            actions.forEach(lang.hitch(this, function(action){
                widgetsDescription[action] = {type: 'TukosButton', atts: {label: Pmg.message(action), onClick: lang.hitch(this, this[action])}};
            }));
            this.includedAtts = includedAtts;
            return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                        contents: lang.mixin(headerRowLayout, { attsRows: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true},  widgets: includedAtts}, actionRow: actionsRowLayout})
                    },
                    style: {minWidth: '500px'}
                }
            };
        },
        attWidgetsDescription(attName){
        	return this[description[attName]](attName);
        },
        colorDescription: function(attName){
        	return {type: 'ColorButton', atts: {label: Pmg.message(attName), showLabel: false, attValueModule: styleAttValue}};
        },
        storeSelectDescription: function(attName, storeData){
        	return {type: 'StoreSelect', atts: {label: Pmg.message(attName), style: {width: '10em'}, storeArgs: {data: storeData}, attValueModule: styleAttValue}};
        },
        hAlignDescription: function(attName){
        	return this.storeSelectDescription(attName, hAlignStoreData);
        },
        vAlignDescription: function(attName){
        	return this.storeSelectDescription(attName, vAlignStoreData);
        },
        displayDescription: function(attName){
        	return this.storeSelectDescription(attName, displayStoreData);
        },
        sizeDescription: function(attName){
        	var unitAttValue = lang.hitch(this, this.unitAttValue);
        	return {type: 'NumberUnitBox', atts: {label: Pmg.message(attName), concat: 'true', number: {style: {width: '3em'}}, unit: {style: {width: '5em'}, storeArgs: {data:sizeUnits}}, attValueModule: unitAttValue(attName)}};
        },
        domAttDescription: function(attName){
        	return {type: 'TextBox', atts: {label: Pmg.message(attName), style: {width: '5em'}, attValueModule: domAttValue}};
        },
         close: function(){
            this.editor.endEdit();
            this.button.closeDropDown(true);
            this.editor.focus();
        },
        cancel: function(){
        	this.close();
        },
        unitAttValue: function(attName){
            return {
                get: function(node){
                	return node.style && node.style[attName] ? node.style[attName] : '';
                },
                set: lang.hitch(this, function(node, att, value){
                	domStyle.set(node, attName, this.pane.getWidget(attName).get('value'));
                }),
                remove: function(node, att){
                    domStyle.set(node, attName, '');
                }
            };
        },
        classNodesToStoreData: function(className, attribute){
            var nodes = Array.apply(null, this.editor.document.getElementsByClassName(className)), storeData = [{id: '', name: ''}], att = attribute;
            nodes.forEach(function(node){
            	var id = node.getAttribute(att);
            	storeData.push({id: id, name: id});
            });
            return storeData;
        }
    });
});
