define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-construct", "tukos/utils", "dojoFixes/dojox/editor/plugins/_EditorTableDialog", "dojo/i18n!dojoFixes/dojox/editor/plugins/nls/TableDialog"], 
    function(declare, lang, domAttr, domStyle, dct, utils, EditorTableDialog, messages){

    var actions = ['insertBefore', 'insertAfter', 'apply', 'remove', 'close', 'insertRowBefore', 'insertRowAfter', 'deleteRow', 'insertColBefore', 'insertColAfter', 'deleteCol'];

    return declare(EditorTableDialog, {
       
        dialogAtts: function(){
            var actionsRow = {
                tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                contents: {
                        col1: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: messages.insertLineBreak}, widgets: ['insertBefore', 'insertAfter']},
                        col2: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: messages.forSelectedAtts, style: {verticalAlign: 'top'}}, widgets: ['apply', 'remove']},
                        col3: {tableAtts: {cols: 1,   customClass: 'labelsAndValues', showLabels: false, style: {verticalAlign: 'top'}}, widgets: ['close']}
                    }
                },
                extraWidgetsDescription = {
                    rowLabel: {type: 'HtmlContent', atts: {style: {backgroundColor: '#BEBEBE', fontWeight: 700}, value: messages.forCurrentRow}},
                    colLabel: {type: 'HtmlContent', atts: {style: {backgroundColor: '#BEBEBE', fontWeight: 700}, value: messages.forCurrentCol}}
                },
                headerRowLayout = {
                    headerRow: {tableAtts: {cols: 1, customClass: 'labelsAndValues', label: messages.modifyTableTitle}},
                    rowsAndColsRow: {
                        tableAtts: {cols: 4, customClass: 'labelsAndValues', showLabels: false}, widgets: ['rowLabel', 'insertRowBefore', 'insertRowAfter', 'deleteRow', 'colLabel', 'insertColBefore', 'insertColAfter', 'deleteCol']
                    }
                };
            return this._dialogAtts(extraWidgetsDescription, headerRowLayout, actions, actionsRow, ['verticalAlign']);
        },
        
        openDialog: function(){
            var table = this.table, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                var attWidget = paneGetWidget(att), attValue = attWidget.attValueModule.get(table, att);
                attWidget.set('value', attValue);
                if (att === 'backgroundColor' || att === 'borderColor'){
                    domStyle.set(attWidget.iconNode, att, attValue);
                }
                 paneGetWidget(att + 'CheckBox').set('checked', utils.empty(attValue) ? false : true);
            });
        },
        
        apply: function(){
            var table = this.table, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                if (paneGetWidget(att + 'CheckBox').checked){
                    var attWidget = paneGetWidget(att);
                    attWidget.attValueModule.set(table, att, attWidget.get('value'));
                }
            });
        },
        
        remove: function(){
            var table = this.table, activeAttWidgets = this.activeAttWidgets, pane = this.pane, paneGetWidget = lang.hitch(pane, pane.getWidget);
            activeAttWidgets.forEach(function(att){
                var checkBox = paneGetWidget(att + 'CheckBox');
                if (checkBox.checked){
                    paneGetWidget(att).attValueModule.remove(table, att);
                    checkBox.set('checked', false);
                }
            });
        },
        
    
       insertBefore: function(){
            dct.place(dct.create("br"), this.table, 'before');
            this.close();
        },
        
       insertAfter: function(){
            dct.place(dct.create("br"), this.table, 'after');
            this.close();
        },
        
        insertRow: function(after){
            var table = this.table, tableInfo = this.tableInfo, row = table.insertRow(tableInfo.trIndex + (after ? 1 : 0)), cols = tableInfo.cols, cell;
            for(var i=0; i< cols; i++){
                cell = row.insertCell(-1);
                cell.innerHTML = "&nbsp;";
            }
            this.tableInfo = this.getTableInfo(true);
            this.table = this.pane.table = this.tableInfo.tbl;
        },

        insertRowBefore: function(){
            this.insertRow();
        },
        
        insertRowAfter: function(){
            this.insertRow(true);
        },
        
        insertCol: function(after){
            var colIndex = this.tableInfo.colIndex + (after ? 1 : 0);
            this.tableInfo.trs.forEach(function(row){
                var cell = row.insertCell(colIndex);
                cell.innerHTML = "&nbsp;";
            });
            this.tableInfo = this.getTableInfo(true);
            this.table = this.pane.table = this.tableInfo.tbl;
        },

        insertColBefore: function(){
            this.insertCol();
        },

        insertColAfter: function(){
            this.insertCol(true);
        },
        
        deleteRow: function(){
            if (this.tableInfo.rows > 1){
                var trIndex = this.tableInfo.trIndex;
                this.table.deleteRow(trIndex);
                this.tableInfo = this.getTableInfo(true);
                this.tableInfo.trIndex = (trIndex > this.tableInfo.rows -1 ? this.tableInfo.rows -1 : trIndex);
                this.table = this.pane.table = this.tableInfo.tbl;
            }
        },
        
        deleteCol: function(){
            if (this.tableInfo.cols > 1){
                var colIndex = this.tableInfo.colIndex;
                this.tableInfo.trs.forEach(function(row){
                    row.deleteCell(colIndex);
                });
                this.tableInfo = this.getTableInfo(true);
                this.tableInfo.colIndex = (colIndex > this.tableInfo.cols -1 ? this.tableInfo.cols -1 : colIndex);
                this.table = this.pane.table = this.tableInfo.tbl;
            }
        }

    });
});
