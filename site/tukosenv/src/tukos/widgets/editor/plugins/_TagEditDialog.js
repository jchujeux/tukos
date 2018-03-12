define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-construct", "dojo/ready", "dojo/on", "tukos/utils", "tukos/TukosTooltipDialog", "dijit/ColorPalette", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, domAttr, domStyle, dct, ready, on, utils, TukosTooltipDialog, colorPicker, messages){

    var colorIconClass = "dijitEditorIcon dijitEditorIconHiliteColor",
        sizeUnits = [{id: '', name: ''}, {id: 'auto', name: 'auto'}, {id: 'cm', name: 'cm'}, {id: '%', name: '%'}, {id: 'em', name: 'em'}, {id: 'px', name: 'px'}],
        thicknessUnits = [{id: '', name: ''}, {id: 'cm', name: 'cm'}, {id: 'em', name: 'em'}, {id: 'px', name: 'px'}],
        hAlignStoreData = [{id: '', name:''}, {id: 'adefault', name: messages['adefault']}, {id: 'left', name: messages.left}, {id: 'center', name: messages.center}, {id: 'right', name: messages.right}],
        vAlignStoreData = [{id: '', name: ''}, {id: 'top', name: messages.top}, {id: 'middle', name: messages.middle}, {id: 'bottom', name: messages.bottom}],
    	displayStoreData = [{id: '', name: ''}, {id: 'block', name: 'block'}, {id: 'inline', name: 'inline'}, {id: 'none', name: 'none'}];

    return declare(TukosTooltipDialog, {
       
        postCreate: function(){
            lang.mixin(this, this.dialogAtts());
            this.inherited(arguments);
            this.onOpen = lang.hitch(this, function(){
                this.begEdit();
                if (this.getTableInfo){
                    this.tableInfo = this.getTableInfo(true);
                    this.target = this.table = this.pane.table = this.tableInfo.tbl;
	                if (this.getSelectedCells){
	                    this.selectedTds = this.getSelectedCells(this.table);
	                }
                }else{
                	var selection = this.editor.selection, target = this.target = selection.getSelectedElement() || selection.getParentElement();
                	if (target.id === 'dijitEditorBody'){
                		this.close();
                		return;
                	}
                }
                this.openDialog();
                dijit.TooltipDialog.prototype.onOpen.apply(this, arguments);
                ready(lang.hitch(this, function(){//JCH: solves issues of empty TooltipDialog when browser window size changes after tooltipdialog has been laoded, among others ...
                    this.pane.resize();
                }));
            });
            this.blurCallback = on.pausable(this, 'blur', this.close);
        },

        openDialog: function(){
            var target = this.target, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                var attWidget = paneGetWidget(att), attValue = attWidget.attValueModule.get(target, att);
                attWidget.set('value', attValue);
                if (att === 'backgroundColor' || att === 'borderColor'){
                    domStyle.set(attWidget.iconNode, att, attValue);
                }
                 paneGetWidget(att + 'CheckBox').set('checked', utils.empty(attValue) ? false : true);
            });
        },
        
        apply: function(){
            var target = this.target, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                if (paneGetWidget(att + 'CheckBox').checked){
                    var attWidget = paneGetWidget(att);
                    attWidget.attValueModule.set(target, att, attWidget.get('value'));
                }
            });
        },
        
        remove: function(){
            var target = this.target, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                var checkBox = paneGetWidget(att + 'CheckBox');
                if (checkBox.checked){
                    var attWidget = paneGetWidget(att);
                	attWidget.attValueModule.remove(target, att);
                    attWidget.set('value', '');
                    checkBox.set('checked', false);
                }
            });
        },

        _dialogAtts: function(extraWidgetsDescription, headerRowLayout, actions, actionsRowLayout, includedAtts){
            var widgetsDescription = extraWidgetsDescription || {}, widgetsAttsArray = [], unitAttValue = lang.hitch(this, this.unitAttValue), domAttValue = this.domAttValue(), styleAttValue = this.styleAttValue(),
                backgroundColorPicker = this.backgroundColorPicker = new colorPicker({onChange: lang.hitch(this, this.onChangeBackgroundColor), onBlur: function(){dijit.popup.close(this);}}), 
                borderColorPicker = this.borderColorPicker = new colorPicker({onChange: lang.hitch(this, this.onChangeBorderColor), onBlur: function(){dijit.popup.close(this);}}),
                attWidgetsDescription = {
                    backgroundColor: {type: 'DropDownButton', atts: {iconClass: colorIconClass, loadDropDown: function(callback){(this.dropDown = backgroundColorPicker).startup(); callback();}, attValueModule: styleAttValue}},
                    borderColor: {type: 'DropDownButton', atts: {iconClass: colorIconClass, loadDropDown: function(callback){(this.dropDown = borderColorPicker).startup(); callback();}, attValueModule: styleAttValue}},
                    align: {type: 'StoreSelect', atts: {style: {width: '10em'}, storeArgs: {data: hAlignStoreData}, attValueModule: domAttValue}},
                    width: {type: 'NumberUnitBox', atts: {number: {style: {width: '3em'}}, unit: {style: {width: '5em'}, storeArgs: {data:sizeUnits}}, attValueModule: unitAttValue('width')}}, 
                    height: {type: 'NumberUnitBox', atts: {number: {style: {width: '3em'}}, unit: {style: {width: '5em'}, storeArgs: {data:sizeUnits}}, attValueModule: unitAttValue('height')}}, 
                    margin: {type: 'NumberUnitBox', atts: {number: {style: {width: '3em'}}, unit: {style: {width: '5em'}, storeArgs: {data:sizeUnits}}, attValueModule: unitAttValue('margin')}}, 
                    display: {type: 'StoreSelect', atts: {style: {width: '8em'}, storeArgs: {data:displayStoreData}, attValueModule: styleAttValue}}, 
                    border: {type: 'TextBox', atts: {style: {width: '5em'}, attValueModule: domAttValue}},
                    cellPadding: {type: 'TextBox', atts: {style: {width: '5em'}, attValueModule: domAttValue}},
                    cellSpacing: {type: 'TextBox', atts: {style: {width: '5em'}, attValueModule: domAttValue}}
                };
            includedAtts.forEach(function(att){
                widgetsDescription[att + 'Label'] = {type: 'HtmlContent', atts: {value: messages[att], style: {textAlign: 'right', whiteSpace: 'nowrap'}}};
                widgetsDescription[att + 'CheckBox'] = {type: 'CheckBox', atts: {checked: true}};
                widgetsDescription[att] = attWidgetsDescription[att];
                widgetsDescription[att]['atts'].onChange = function(){console.log('I am activated');this.pane.getWidget(att+ 'CheckBox').set('checked', true)};
                widgetsAttsArray.push(att + 'Label', att + 'CheckBox', att);
            });
            widgetsDescription['selectAllToggleLabel'] = {type: 'HtmlContent', atts: {value: messages.selectAllToggle, style: {textAlign: 'right', whiteSpace: 'nowrap', fontStyle: 'italic'}}};
            widgetsDescription['selectAllToggleCheckBox'] = {type: 'CheckBox', atts: {checked: false, onWatchLocalAction: lang.hitch(this, this.selectAllToggleWatch)()}};
            widgetsAttsArray.push('selectAllToggleLabel', 'selectAllToggleCheckBox');
            actions.forEach(lang.hitch(this, function(action){
                widgetsDescription[action] = {type: 'TukosButton', atts: {label: messages[action], onClick: lang.hitch(this, this[action])}};
            }));
            this.activeAttWidgets = includedAtts;
            return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'/*, labelStyle: {whiteSpace: 'nowrap'}*/},
                        contents: lang.mixin(headerRowLayout, { attsRows: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false/*, widgetCellStyle: {whiteSpace: 'nowrap'}*/},  widgets: widgetsAttsArray}, actionRow: actionsRowLayout})
                    },
                    style: {minWidth: '500px'}
                }
            };
        },

        onChangeColor: function(colorStyle, newColor){
            var paneGetWidget = lang.hitch(this.pane, this.pane.getWidget), widget = paneGetWidget(colorStyle);
            domStyle.set(widget.iconNode, 'backgroundColor', newColor);
            widget.set('value', newColor);
            dijit.popup.close(this[colorStyle + 'Picker']);
            paneGetWidget(colorStyle + 'CheckBox').set('checked', true)
        },

        onChangeBackgroundColor: function(newColor){
            this.onChangeColor('backgroundColor', newColor);
        },

        onChangeBorderColor: function(newColor){
            this.onChangeColor('borderColor', newColor);
        },

        selectAllToggleWatch: function(){
            var action = function(sWidget, tWidget, newValue){
                var target = this.target, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
                activeAttWidgets.forEach(function(att){
                    paneGetWidget(att + 'CheckBox').set('checked', newValue);
                });
            }
            return {checked: {selectAllToggleCheckBox: {localActionStatus: lang.hitch(this, action)}}};
        },

         close: function(){
            this.editor.focus();
            this.endEdit();
            dijit.popup.close(this);
        },

        begEdit: function(){
            if(this.editor.customUndo){
                this.editor.beginEditing();
            }else{
                this.valBeforeUndo = this.editor.getValue();                   
            }
        },

        endEdit: function(){
            if(this.editor.customUndo){
                this.editor.endEditing();
            }else{
                // This code ALMOST works for undo - It seems to only work for one step back in history however
                var afterUndo = this.editor.getValue();
                 this.editor.setValue(this.valBeforeUndo);
                this.editor.replaceValue(afterUndo);
            }
            this.editor.onDisplayChanged();
        },
       
        unitAttValue: function(attName){
            return {
                get: function(node){
                    var unitAttValue = node ? (node.style || {})[attName] || domAttr.get(node, attName) : '';
                    if (utils.empty(unitAttValue)){
                        return '';
                    }else if (dojo.isString(unitAttValue)){
                        return '[' + (/\d+/.exec(unitAttValue) || '""') + ',"' + /[%a-z]+/i.exec(unitAttValue) + '"]';
                    }else{
                        return  '[' + unitAttValue + ', ""]';
                    }
                },
                set: lang.hitch(this, function(node, att, value){
                    var widget = this.pane.getWidget(attName), number = widget.numberField.get('value');
                    domStyle.set(node, attName, (isNaN(number) ? '' : number) + widget.unitField.get('value'));
                }),
                remove: function(node, att){
                    domStyle.set(node, attName, '');
                }
            };
        },

        domAttValue: function(){
            return {
                get: function(node, att){
                    return domAttr.get(node, att);
                },
                set: function(node, att, value){
                    value === '' ? domAttr.remove(node, att) : domAttr.set(node, att, value);
                },
                remove: function(node, att){
                    domAttr.remove(node, att);
                }
            };
        },
        styleAttValue: function(){
            return {
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
        }
    });
});
