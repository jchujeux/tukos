define (
	["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/dom-attr", "dojo/dom-style", "dojo/ready", "dojo/on","tukos/utils","tukos/TukosTooltipDialog","tukos/widgets/ColorPalette","tukos/PageManager"], 
    function(declare, lang, when, domAttr, domStyle, ready, on, utils, TukosTooltipDialog, colorPalette, Pmg){

    var colorIconClass = "dijitEditorIcon dijitEditorIconHiliteColor",
        sizeUnits = utils.storeData(['auto', 'cm', '%', 'em', 'px']), thicknessUnits = utils.storeData(['cm', 'em', 'px']), hAlignStoreData = Pmg.messageStoreData(['default', 'left', 'center', 'right']),
        vAlignStoreData = Pmg.messageStoreData(['top', 'middle', 'bottom']), displayStoreData = Pmg.messageStoreData(['block', 'inline', 'inline-table', 'none']), pageBreakInsideStoreData = Pmg.messageStoreData(['auto', 'avoid']), 
        objectFitStoreData = Pmg.messageStoreData(['fill', 'contain', 'cover', 'none', 'scale-down']), objectPositionStoreData = Pmg.messageStoreData(['50% 50%', 'right top', 'left bottom', '0px 0px']),
    	description = {backgroundColor: 'colorDescription', borderColor: 'colorDescription', color: 'colorDescription', pageBreakInside: 'pageBreakInsideDescription', textAlign: 'hAlignDescription', verticalAlign: 'vAlignDescription', width: 'sizeDescription',
    				   	height: 'sizeDescription', minWidth: 'sizeDescription', maxWidth: 'sizeDescription', minHeight: 'sizeDescription', maxHeight: 'sizeDescription', objectFit: 'objectFitDescription', objectPosition: 'objectPositionDescription',
    				   	margin: 'sizeDescription', display: 'displayDescription', paddingLeft: 'sizeDescription', paddingRight: 'sizeDescription', paddingBottom: 'sizeDescription', paddingTop: 'sizeDescription',
    				   	placeHolder: 'domAttDescription', border: 'domAttDescription', cellPadding: 'domAttDescription', cellSpacing: 'domAttDescription'},
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
				var self = this, _arguments = arguments;
				this.editor.begEdit();
                if (this.prepareTable){
                    this.prepareTable();
                    this.target = this.table;
                }else{
                	var selection = this.editor.selection;
                	this.target = selection.getSelectedElement() || selection.getParentElement();
                }
                when(this.openDialog(), function(response){
					if (response){
	                    dijit.TooltipDialog.prototype.onOpen.apply(self, _arguments);
	                    ready(lang.hitch(self, function(){//JCH: solves issues of empty TooltipDialog when browser window size changes after tooltipdialog has been laoded, among others ...
	                        this.pane.resize();
	                    }));
	                }else{
	                	dijit.popup.close(this);
	                }
				});
            });
            this.blurCallback = on.pausable(this, 'blur', this.close);
        },
        openDialog: function(){
            var target = this.target, includedAtts = this.includedAtts, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            return this.pane.onInstantiated(lang.hitch(this, function(){
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
			}));
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
        attWidgetsDescription: function(attName){
        	return this[description[attName]](attName);
        },
        colorDescription: function(attName){
        	return {type: 'ColorButton', atts: {label: Pmg.message(attName), showLabel: false, attValueModule: styleAttValue}};
        },
        storeSelectDescription: function(attName, storeData){
        	return {type: 'StoreSelect', atts: {label: Pmg.message(attName), style: {width: '10em'}, storeArgs: {data: storeData}, attValueModule: styleAttValue}};
        },
        storeComboBoxDescription: function(attName, storeData){
        	return {type: 'StoreComboBox', atts: {label: Pmg.message(attName), style: {width: '10em'}, storeArgs: {data: storeData}, attValueModule: styleAttValue}};
        },
        pageBreakInsideDescription: function(attName){
        	return this.storeSelectDescription(attName, pageBreakInsideStoreData);
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
        objectFitDescription: function(attName){
        	return this.storeSelectDescription(attName, objectFitStoreData);
		},
        objectPositionDescription: function(attName){
        	return this.storeComboBoxDescription(attName, objectPositionStoreData);
		},
        sizeDescription: function(attName){
        	var unitAttValue = lang.hitch(this, this.unitAttValue);
        	return {type: 'NumberUnitBox', atts: {label: Pmg.message(attName), concat: 'true', number: {style: {width: '3em'}}, unit: {style: {width: '5em'}, storeArgs: {data:sizeUnits}}, attValueModule: unitAttValue(attName)}};
        },
        domAttDescription: function(attName){
        	return {type: 'TextBox', atts: {label: Pmg.message(attName), style: {width: '5em'}, attValueModule: domAttValue}};
        },
         close: function(){
            var handler = this.editor._tablePluginHandler;
        	 this.editor.endEdit();
            this.button.closeDropDown(true);
            if (this.editor.focused){
            	this.editor.focus();
            }
            this.editor._tablePluginHandler.availableCurrentlySet = false;
            setTimeout(function(){handler.checkAvailable();}, 100);
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
