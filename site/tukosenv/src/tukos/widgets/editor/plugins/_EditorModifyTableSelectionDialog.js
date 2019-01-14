define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-class", "dojo/dom-construct", "tukos/utils", "tukos/expressions", "tukos/widgets/editor/plugins/_TagEditDialog", "tukos/PageManager"], 
    function(declare, lang, domAttr, domStyle, dcl, dct, utils, expressions, _TagEditDialog, Pmg){

    var actions = ['copySelected', 'emptySelected', 'pasteAtSelected', 'merge', 'split', 'apply', 'close'];

    return declare(_TagEditDialog, {
       
        dialogAtts: function(){
            var actionsRow = {
                    tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                    contents: {
                        col1: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: Pmg.message('forSelectedCells')}, widgets: ['merge', 'split']},
                        col2: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: ''}, widgets: ['apply']},
                        col3: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false}, widgets: ['close']}
                    }
                },
                extraWidgetsDescription = {
                    	isWorksheet: {type: 'CheckBox', atts: {title: Pmg.message('is worksheet'), hidden: true, disabled: true}, attValueModule: domAttr},
                    	sheetName: {type: 'TextBox', atts: {title: Pmg.message('sheetName'), style: {width: '10em'}, hidden: true, disabled: true}, attValueModule: domAttr}
                },
                headerRowLayout = {headerRow: {
                	tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, label: Pmg.message('modifyTableSelection')},
                	contents:{
                		row1: {tableAtts: {cols: 2, customClass: 'labelsandValues', showLabels: true}, widgets: ['isWorksheet', 'sheetName']},
            			row2: {tableAtts: {cols: 4, customClass: 'labelsandValues', showLabels: false}, widgets: ['copySelected', 'emptySelected', 'pasteAtSelected']}
                	}
                }};
            return this._dialogAtts(extraWidgetsDescription, headerRowLayout, actions, actionsRow, this.editableAtts);
        },
        openDialog: function(){
            var pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget), paneSetWidgets = lang.hitch(pane, pane.setWidgets), table = this.table;
            this.target = this.selectedTds[0];
            this.inherited(arguments);
            if (dcl.contains(table, 'tukosWorksheet')){
            	var tableInfo = this.tableInfo, disabledRowValue = tableInfo.trIndex === 0 ? true : false, disabledColValue = tableInfo.colIndex === 0 ? true : false;
            	paneSetWidgets({checked: {isWorksheet: true}, value: {sheetName: table.id}, hidden: {sheetName: false}});
            }else{
            	paneSetWidgets({checked: {isWorksheet: false}, hidden: {sheetName: true}});
            }
            return true;
        },
        apply: function(){
            var tds = this.selectedTds, includedAtts = this.includedAtts, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            includedAtts.forEach(function(att){
                dojo.forEach(tds, function(td){
                    var attWidget = paneGetWidget(att);
                    attWidget.attValueModule.set(td, att, attWidget.get('value'));
                });
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
