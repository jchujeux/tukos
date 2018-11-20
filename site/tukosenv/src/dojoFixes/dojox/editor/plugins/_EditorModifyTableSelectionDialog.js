define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-class", "dojo/dom-construct", "tukos/utils", "tukos/expressions", "dojoFixes/dojox/editor/plugins/_EditorTableDialog", "tukos/PageManager", 
		 "dojo/i18n!dojoFixes/dojox/editor/plugins/nls/TableDialog"], 
    function(declare, lang, domAttr, domStyle, dcl, dct, utils, expressions, EditorTableDialog, Pmg, messages){

    var actions = ['copySelected', 'emptySelected', 'pasteAtSelected', 'merge', 'split', 'apply', 'remove', 'close'];

    return declare(EditorTableDialog, {
       
        dialogAtts: function(){
            var actionsRow = {
                    tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                    contents: {
                        col1: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: messages.forSelectedCells}, widgets: ['merge', 'split']},
                        col2: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: messages.forSelectedAtts}, widgets: ['apply', 'remove']},
                        col3: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false}, widgets: ['close']}
                    }
                },
                extraWidgetsDescription = {
                    verticalAlign: {type: 'StoreSelect', atts: {style: {width: '10em'}, 
                    	storeArgs: {data: [{id: 'default', name: messages['default']}, {id: 'left', name: messages.left}, {id: 'center', name: messages.center}, {id: 'right', name: messages.right}]}}, attValueModule: domStyle},
                    	isWorksheet: {type: 'CheckBox', atts: {title: Pmg.message('is worksheet'), disabled: true}, attValueModule: domAttr},
                    	sheetName: {type: 'TextBox', atts: {title: Pmg.message('sheetName'), style: {width: '10em'}, hidden: true, disabled: true}, attValueModule: domAttr},
                },
                headerRowLayout = {headerRow: {
                	tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, label: messages.modifyTableSelectionTitle},
                	contents:{
                		row1: {tableAtts: {cols: 2, customClass: 'labelsandValues', showLabels: true}, widgets: ['isWorksheet', 'sheetName']},
            			row2: {tableAtts: {cols: 4, customClass: 'labelsandValues', showLabels: false}, widgets: ['copySelected', 'emptySelected', 'pasteAtSelected']}
                	}
                }};
            return this._dialogAtts(extraWidgetsDescription, headerRowLayout, actions, actionsRow, []);
        },
        openDialog: function(){
            var selectedTd = this.selectedTds[0], activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), paneSetWidgets = lang.hitch(pane, pane.setWidgets), table = this.table;
            activeAttWidgets.forEach(function(att){
                var attWidget = paneGetWidget(att), attValue = attWidget.attValueModule.get(selectedTd, att);
                attWidget.set('value', attValue);
                if (att === 'backgroundColor' || att === 'borderColor'){
                    domStyle.set(attWidget.iconNode, att, attValue);
                }
                paneGetWidget(att + 'CheckBox').set('checked', utils.empty(attValue) ? false : true);
            });
            if (dcl.contains(table, 'tukosWorksheet')){
            	var tableInfo = this.tableInfo, disabledRowValue = tableInfo.trIndex === 0 ? true : false, disabledColValue = tableInfo.colIndex === 0 ? true : false;
            	paneSetWidgets({checked: {isWorksheet: true}, value: {sheetName: table.id}, hidden: {sheetName: false}});
            }else{
            	paneSetWidgets({checked: {isWorksheet: false}, hidden: {sheetName: true}});
            }
        },
        apply: function(){
            var tds = this.selectedTds, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                if (pane.getWidget(att + 'CheckBox').checked){
                    dojo.forEach(tds, function(td){
                        var attWidget = paneGetWidget(att);
                        attWidget.attValueModule.set(td, att, attWidget.get('value'));
                    });
                }
            });
        },        
        remove: function(){
            var tds = this.selectedTds, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                if (paneGetWidget(att + 'CheckBox').checked){
                    var attValueModule = paneGetWidget(att).attValueModule;
                    dojo.forEach(tds, function(td){
                        paneGetWidget(att).attValueModule.remove(td, att);
                    });
                }
            });
        },
        merge: function(){
            var self = this, tds = this.selectedTds, table = this.table;
            require(["redips/redipsTable"], function(redipsTable){
                dojo.forEach(tds, lang.hitch(this, function(td){
                    redipsTable.mark(true, td);
                }));
                redipsTable.merge('h', false, table);
                redipsTable.merge('v', true, table);
                this.close();
            });
        },
        split: function(){
            var self = this, tds = this.selectedTds, table = this.table;
            require(["redips/redipsTable"], function(redipsTable){
                dojo.forEach(tds, lang.hitch(this, function(td){
                    redipsTable.mark(true, td);
                    redipsTable.split('h', table);
                    redipsTable.mark(true, td);
                    redipsTable.split('v', table);
                }));
                this.close();
            });
        }
    });
});
