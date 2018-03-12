define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-construct", "dojo/ready", "dojo/on", "tukos/utils", "tukos/TukosTooltipDialog", "dijit/ColorPalette", "dojo/i18n!dojoFixes/dojox/editor/plugins/nls/TableDialog"], 
    function(declare, lang, domAttr, domStyle, dct, ready, on, utils, TukosTooltipDialog, colorPicker, messages){

    var editableAtts = ['backgroundColor', 'borderColor', 'align', 'verticalAlign', 'width', 'border', 'cellPadding', 'cellSpacing'],
        widthUnits = [{id: '', name: ''}, {id: 'auto', name: 'auto'}, {id: '%', name: '%'}, {id: 'em', name: 'em'}, {id: 'px', name: 'px'}];

    return declare(TukosTooltipDialog, {
       
        postCreate: function(){
            lang.mixin(this, this.dialogAtts());
            this.inherited(arguments);
            if (this.getTableInfo){
                this.table = this.getTableInfo(true).tbl;
            }
            this.onOpen = lang.hitch(this, function(){
                this.begEdit();
                if (this.getTableInfo){
                    this.tableInfo = this.getTableInfo(true);
                    this.table = this.pane.table = this.tableInfo.tbl;
                }
                if (this.getSelectedCells){
                    this.selectedTds = this.getSelectedCells(this.table);
                }
                this.openDialog();
                dijit.TooltipDialog.prototype.onOpen.apply(this, arguments);
                ready(lang.hitch(this, function(){//JCH: solves issues of empty TooltipDialog when browser window size changes after tooltipdialog has been laoded, among others ...
                    this.pane.resize();
                }));
            });
            this.blurCallback = on.pausable(this, 'blur', this.close);
        },

        _dialogAtts: function(extraWidgetsDescription, headerRowLayout, actions, actionsRowLayout, excludeAtts){
            var widgetsDescription = extraWidgetsDescription || {}, widgetsAttsArray = [], widthAttValue = this.widthAttValue(), domAttValue = this.domAttValue(), styleAttValue = this.styleAttValue(),
                backgroundColorPicker = this.backgroundColorPicker = new colorPicker({onChange: lang.hitch(this, this.onChangeBackgroundColor), onBlur: function(){dijit.popup.close(this);}}), 
                borderColorPicker = this.borderColorPicker = new colorPicker({onChange: lang.hitch(this, this.onChangeBorderColor), onBlur: function(){dijit.popup.close(this);}}),
                attWidgetsDescription = {
                    backgroundColor: {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = backgroundColorPicker).startup(); callback();}, attValueModule: styleAttValue}},
                    borderColor: {type: 'DropDownButton', atts: {iconClass: "dijitEditorIcon dijitEditorIconHiliteColor", loadDropDown: function(callback){(this.dropDown = borderColorPicker).startup(); callback();}, attValueModule: styleAttValue}},
                    align: {type: 'StoreSelect', atts: {style: {width: '10em'}, storeArgs: {data: [{id: '', name:''}, {id: 'default', name: messages['default']}, {id: 'left', name: messages.left}, {id: 'center', name: messages.center}, {id: 'right', name: messages.right}]}, attValueModule: domAttValue}},
                    verticalAlign: {type: 'StoreSelect', atts: {style: {width: '10em'}, storeArgs: {data: [{id: '', name: ''}, {id: 'top', name: messages.top}, {id: 'middle', name: messages.middle}, {id: 'bottom', name: messages.bottom}]}, attValueModule: styleAttValue}},
                    width: {type: 'NumberUnitBox', atts: {number: {style: {width: '3em'}}, unit: {style: {width: '5em'}, storeArgs: {data:widthUnits}}, attValueModule: widthAttValue}}, 
                    border: {type: 'TextBox', atts: {style: {width: '5em'}, attValueModule: domAttValue}},
                    cellPadding: {type: 'TextBox', atts: {style: {width: '5em'}, attValueModule: domAttValue}},
                    cellSpacing: {type: 'TextBox', atts: {style: {width: '5em'}, attValueModule: domAttValue}}
                };
            var activeAttWidgets = this.activeAttWidgets = utils.array_diff(editableAtts, excludeAtts || []);
            activeAttWidgets.forEach(function(att){
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
            return {
                paneDescription: {
                    widgetsDescription: widgetsDescription,
                    layout: {
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'/*, labelStyle: {whiteSpace: 'nowrap'}*/},
                        contents: lang.mixin(headerRowLayout, { attsRows: {tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: false/*, widgetCellStyle: {whiteSpace: 'nowrap'}*/},  widgets: widgetsAttsArray}, actionRow: actionsRowLayout})
                    }
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
                var table = this.table, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
                activeAttWidgets.forEach(function(att){
                    paneGetWidget(att + 'CheckBox').set('checked', newValue);
                });
            }
            return {checked: {selectAllToggleCheckbox: {localActionStatus: lang.hitch(this, action)}}};
        },

         close: function(){
            this.editor.focus();
            this.endEdit();
            dijit.popup.close(this);
        },

        begEdit: function(){
            if(this.editor._tablePluginHandler.undoEnabled){
                if(this.editor.customUndo){
                    this.editor.beginEditing();
                }else{
                    this.valBeforeUndo = this.editor.getValue();                   
                }
            }
        },

        endEdit: function(){
            if(this.editor._tablePluginHandler.undoEnabled){
                if(this.editor.customUndo){
                    this.editor.endEditing();
                }else{
                    // This code ALMOST works for undo - It seems to only work for one step back in history however
                    var afterUndo = this.editor.getValue();
                     this.editor.setValue(this.valBeforeUndo);
                    this.editor.replaceValue(afterUndo);
                }
                this.editor.onDisplayChanged();
            }
        },
       
        widthAttValue: function(){
            return {
                get: function(node){
                    var widthAttValue = node.style.width || domAttr.get(node, "width");
                    if (utils.empty(widthAttValue)){
                        return '';
                    }else if (dojo.isString(widthAttValue)){
                        return '[' + (/\d+/.exec(widthAttValue) || '""') + ',"' + /[%a-z]+/i.exec(widthAttValue) + '"]';
                    }else{
                        return  '[' + widthAttValue + ', ""]';
                    }
                },
                set: lang.hitch(this, function(node, att, value){
                    var widget = this.pane.getWidget('width');
                    domStyle.set(node, "width", widget.numberField.get('value') + widget.unitField.get('value'));
                }),
                remove: function(node, att){
                    domStyle.set(node, 'width', '');
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
