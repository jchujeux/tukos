define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "dojo/dom-style", "dojo/dom-class", "dojo/dom-construct", "dojo/string", "tukos/utils", "tukos/expressions", "tukos/PageManager",
		 "tukos/widgets/editor/plugins/_TagEditDialog"], 
    function(declare, lang, domAttr, domStyle, dcl, dct, string, utils, expressions, Pmg, TagEditDialog){

    var actions = ['insertBefore', 'insertAfter', 'apply', 'close', 'insertRowBefore', 'insertRowAfter', 'deleteRow', 'insertColBefore', 'insertColAfter', 'deleteCol'];

    return declare(TagEditDialog, {
       
        dialogAtts: function(){
            var actionsRow = {
                tableAtts: {cols: 3, customClass: 'labelsAndValues', showLabels: true, orientation: 'vert'},
                contents: {
                        col1: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: Pmg.message('insertLineBreak')}, widgets: ['insertBefore', 'insertAfter']},
                        col2: {tableAtts: {cols: 2,   customClass: 'labelsAndValues', showLabels: false, label: '', style: {verticalAlign: 'top'}}, widgets: ['apply']},
                        col3: {tableAtts: {cols: 1,   customClass: 'labelsAndValues', showLabels: false, style: {verticalAlign: 'top'}}, widgets: ['close']}
                    }
                },
                extraWidgetsDescription = {
                    rowLabel: {type: 'HtmlContent', atts: {style: {backgroundColor: '#BEBEBE', fontWeight: 700, whiteSpace: 'nowrap'}, value: Pmg.message('forCurrentRow')}},
                    colLabel: {type: 'HtmlContent', atts: {style: {backgroundColor: '#BEBEBE', fontWeight: 700, whiteSpace: 'nowrap'}, value: Pmg.message('forCurrentCol')}},
            		isWorksheet: {type: 'CheckBox', atts: {title: Pmg.message('is worksheet'), hidden: true, disabled: true}, attValueModule: domAttr},
            		sheetName: {type: 'TextBox', atts: {title: Pmg.message('sheetName'), style: {width: '10em'}, hidden: true, disabled: true}, attValueModule: domAttr},
                },
                headerRowLayout = {theRow: {
                	tableAtts: {cols: 2, customClass: 'labelsAndValues', label: Pmg.message('modifyTable'), showLabels: false},
                	contents: {
                		col1: {tableAtts: {cols: 4, customClass: 'labelsAndValues', showLabels: false}, widgets: ['rowLabel', 'insertRowBefore', 'insertRowAfter', 'deleteRow', 'colLabel', 'insertColBefore', 'insertColAfter', 'deleteCol']},
                		col2: {tableAtts: {cols: 2, customClass: 'labelsandValues', showLabels: true}, widgets: ['isWorksheet', 'sheetName']}
                	}
                }};
            return this._dialogAtts(extraWidgetsDescription, headerRowLayout, actions, actionsRow, this.editableAtts);
        },
        openDialog: function(){
            var table = this.table, pane = this.pane/*, paneGetWidget = lang.hitch(pane, pane.getWidget)*/, paneSetWidgets = lang.hitch(pane, pane.setWidgets);
            this.inherited(arguments);
            if (dcl.contains(table, 'tukosWorksheet')){
            	var tableInfo = this.tableInfo, disabledRowValue = tableInfo.trIndex === 0 ? true : false, disabledColValue = tableInfo.colIndex === 0 ? true : false;
            	paneSetWidgets({checked: {isWorksheet: true}, value: {sheetName: table.id}, hidden: {sheetName: false}, 
            					disabled: {insertRowBefore: disabledRowValue, insertColBefore: disabledColValue, deleteRow: disabledRowValue, deleteCol: disabledColValue}});
            }else{
            	paneSetWidgets({checked: {isWorksheet: false}, hidden: {sheetName: true}, disabled: {insertRowBefore: false, insertColBefore: false, deleteRow: false, deleteCol: false}});
            }
            return true;
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
            var table = this.table, tableInfo = this.tableInfo, newRowNumber = tableInfo.trIndex + (after ? 1 : 0), row = table.insertRow(newRowNumber), cols = tableInfo.cols, cell, isWorksheet = dcl.contains(table, 'tukosWorksheet'),
            	sheetName = table.id, template = expressions.template();
            for(var c=0; c< cols; c++){
                cell = row.insertCell(-1);
                if (isWorksheet){
                	domAttr.set(cell, c === 0 
                		? {contentEditable: false, innerHTML: newRowNumber, style: {textAlign: "center", backgroundColor: 'lightgrey', fontWeight: 'bold'}}
                		: {innerHTML: string.substitute(template,  {name: sheetName + '!' + utils.alphabet(c) + newRowNumber, value: ' ', formula: '', visualPreTag: '', visualPostTag: ''}),
                        	onclick: "parent.tukos.onTdClick(this);", ondblclick: "parent.tukos.onTdDblClick(this);"
                    	  }
                	);
                }else{
                    cell.innerHTML = "&nbsp;";
                }
            }
            tableInfo = this.tableInfo = this.getTableInfo(true);
            table = this.table = this.pane.table = tableInfo.tbl;
            if (isWorksheet){
            	var rows = tableInfo.rows, trs = tableInfo.trs;
            	for (var r = rows-1; r > newRowNumber; r--){
            		var tds = Array.apply(null, trs[r].children);
            		tds[0].innerHTML = r;
            		for (var c = 1; c < cols; c++){
            			expressions.setName(tds[c].children[0], {name: sheetName, col: utils.alphabet(c), row: r}, true);
            		}
            	}
            }
            tableInfo.tds[0].id = '';
            this.editor._tablePluginHandler._prepareTable(table);
        },
        insertRowBefore: function(){
            this.insertRow();
        },
        insertRowAfter: function(){
            this.insertRow(true);
        },
        insertCol: function(after){
            var tableInfo = this.tableInfo, colIndex = tableInfo.colIndex + (after ? 1 : 0), cell, table = this.table, isWorksheet = dcl.contains(table, 'tukosWorksheet'),
        	sheetName = table.id, template = expressions.template();
            tableInfo.trs.forEach(function(row, r){
                cell = row.insertCell(colIndex);
                if (isWorksheet){
                	domAttr.set(cell, r === 0 
                    		? {contentEditable: false, innerHTML: utils.alphabet(colIndex), style: {textAlign: "center", backgroundColor: 'lightgrey', fontWeight: 'bold'}}
                    		: {innerHTML: string.substitute(template,  {name: sheetName + '!' + utils.alphabet(colIndex) + r, value: ' ', formula: '', visualPreTag: '', visualPostTag: ''}),
                            	onclick: "parent.tukos.onTdClick(this);", ondblclick: "parent.tukos.onTdDblClick(this);"
                        	  }
                    	);                	
                }else{
                    cell.innerHTML = "&nbsp;";
                }
            });
            tableInfo = this.tableInfo = this.getTableInfo(true);
            table = this.table = this.pane.table = this.tableInfo.tbl;
            if (isWorksheet){
            	var rows = tableInfo.rows, trs = tableInfo.trs, cols = tableInfo.cols, tds = Array.apply(null, trs[0].children);
            	for (var c = colIndex+1; c < cols; c++){
            		tds[c].innerHTML = utils.alphabet(c);
            	}
            	for (var r = 1; r < rows; r++){
            		tds = Array.apply(null, trs[r].children);
            		tds[0].innerHTML = r;
            		for (var c = cols-1; c > colIndex; c--){
            			expressions.setName(tds[c].children[0], {name: sheetName, col: utils.alphabet(c), row: r}, true);
            		}
            	}
            }
            tableInfo.tds[0].id = '';
            this.editor._tablePluginHandler._prepareTable(table);
        },
        insertColBefore: function(){
            this.insertCol();
        },
        insertColAfter: function(){
            this.insertCol(true);
        },
        deleteRow: function(){
            var tableInfo = this.tableInfo;
        	if (tableInfo.rows > 1){
                var tableInfo = this.tableInfo, trIndex = tableInfo.trIndex, table = this.table, isWorksheet = dcl.contains(table, 'tukosWorksheet');
                table.deleteRow(trIndex);
                tableInfo = this.tableInfo = this.getTableInfo(true);
                table = this.pane.table = this.table = this.tableInfo.tbl;
                if (isWorksheet){
                	var rows = tableInfo.rows, trs = tableInfo.trs, sheetName = table.id, cols = tableInfo.cols;
                	for (var r = trIndex; r < rows; r++){
                		var tr = trs[r], tds = Array.apply(null, tr.children);
                		tds[0].innerHTML = r;
                		for (var c = 1; c < cols; c++){
                			expressions.setName(tds[c].children[0], {name: sheetName, col: utils.alphabet(c), row: r}, true);
                		}
                	}
                }
                tableInfo.trIndex = (trIndex > this.tableInfo.rows -1 ? tableInfo.rows -1 : trIndex);
                tableInfo.tds[0].id = '';
                this.editor._tablePluginHandler._prepareTable(table);
            }
        },
        deleteCol: function(){
            var tableInfo = this.tableInfo, cols = tableInfo.cols;
        	if (cols > 1){
                var colIndex = tableInfo.colIndex, trs = tableInfo.trs, table = this.table, isWorksheet = dcl.contains(table, 'tukosWorksheet');
                tableInfo.trs.forEach(function(row){
                    row.deleteCell(colIndex);
                });
                tableInfo = this.tableInfo = this.getTableInfo(true);
                table = this.table = this.pane.table = this.tableInfo.tbl;
                if (isWorksheet){
                	var rows = tableInfo.rows, trs = tableInfo.trs, sheetName = table.id, tds = Array.apply(null, trs[0].children);
                	cols = tableInfo.cols;
                	for (var c = colIndex; c < cols; c++){
                		tds[c].innerHTML = utils.alphabet(c);
                	}
                	for (var r = 1; r < rows; r++){
                		var tr = trs[r], tds = Array.apply(null, tr.children);
                		for (var c = colIndex; c < cols; c++){
                			expressions.setName(tds[c].children[0], {name: sheetName, col: utils.alphabet(c), row: r}, true);
                		}
                	}
                }
                tableInfo.colIndex = (colIndex > this.tableInfo.cols -1 ? this.tableInfo.cols -1 : colIndex);
                tableInfo.tds[0].id = '';
                this.editor._tablePluginHandler._prepareTable(table);
            }
        }
    });
});
