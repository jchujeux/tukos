define (["dojo/_base/array", "dojo/_base/lang", "dojo/json", "tukos/utils",  "tukos/evalutils", "tukos/widgetUtils", "dojo/i18n!tukos/nls/messages"], 
    function(arrayUtil, lang, JSON, utils, eutils, wutils, messages){
    return {

        cellPattern: "([$]?)([a-zA-Z]+)([$]?)(\\d+)", widgetPattern: "@(\\w+)",

        copyCell: function(cell){
            var row = cell.row, column = cell.column, grid = column.grid, idProperty = grid.collection.idProperty, rowValues = lang.hitch(grid, grid.rowValues)(row.data[idProperty]), colId = column.field;
            return {cell: cell, value: rowValues[colId], rowId: rowValues.rowId, idProperty: idProperty, column: column, grid: grid, colId: colId};
        },
        
        pasteCell: function(copiedCell, targetCell){       	
            var copiedValue = copiedCell['value'];
            if (copiedValue[0] === '='){
                var copiedColId = copiedCell['colId'].charCodeAt(0), copiedRowId = copiedCell['rowId'],
                    targetColId = targetCell['colId'].charCodeAt(0), targetRowId = targetCell['rowId'];
                return this.offsetFormula(copiedCell['grid'], copiedValue, targetRowId - copiedRowId, targetColId - copiedColId);
            }else{
                return copiedValue;
            }
        },
            
        offsetFormula: function(grid, formula, rowOffset, colOffset){
            var callback = function(match, p1, p2, p3, p4){
                return p1 + (p1 != '$' && colOffset != 0 ? grid.colField(parseInt(grid.colId(p2)) + colOffset) : p2) + p3 + (p3 != '$' ? parseInt(p4) + rowOffset : p4);
            };
            if (rowOffset !== 0 || colOffset !== 0){
                return formula.replace(new RegExp(this.cellPattern, 'g'), callback);
            }else{
                return formula;
            }
        },

        RCtoRelative: function(formula, field, id){//to support legacy RiCj notation, not to be used anymore. uses old algorithm (idProprty tather than rowId, cols assume notation A, B, ...
            var callback = function(match, p1, p2, p3, p4){
                var newId = id, newField = field;
                var RorCToRel = function(RorC, increment){
                    if (RorC === 'R'){
                        newId = (parseInt(id)+parseInt(increment)).toString();
                    }else if (RorC === 'C'){
                        newField = String.fromCharCode(field.charCodeAt(0)+parseInt(increment));
                    }
                }
                RorCToRel(p1, p2);
                if (p3 != undefined){
                    RorCToRel(p3, p4);
                }
                return newField + newId;
            }   
            return formula.replace(new RegExp("(C|R)([+-]\\d+)((C|R)([+-]\\d+))?", 'g'), callback);
        },

        cellRangesToArray: function(grid, formula){
            var callback = function(match, p1, p2, p3, p4, p5, p6, p7, p8){
                var theArray = [];
                var i = 0;
                var firstRowId = parseInt(p4), lastRowId = parseInt(p8);
                for (var rowId = firstRowId; rowId <= lastRowId; rowId++){
                    theArray[i] = [];
                    var idProperty = grid.idPropertyOf('rowId', rowId);
                    var j = 0, firstColId = grid.colId(p2), lastColId = grid.colId(p6);
                    for (var colId = firstColId; colId <= lastColId; colId++){
                        var field = grid.colField(colId);
                        theArray[i][j] = this.evalFormula(grid, grid.cellValueOf(field, idProperty), field, idProperty, true);
                        j += 1;
                    }
                    i += 1;
                }
                return JSON.stringify(theArray);
            }
            return formula.replace(new RegExp(this.cellPattern + ':' + this.cellPattern, 'g'), lang.hitch(this, callback));
        },
        
        cellsToValue: function(grid, formula){
            return formula.replace(new RegExp(this.cellPattern, 'g'), lang.hitch(this, function(match, p1, p2, p3, p4){
                    return this.parseCell(grid, p2, p4);
                })
            );
        },
    
        parseCell: function(grid, field, rowId){
            var idProperty = grid.idPropertyOf('rowId', parseInt(rowId));
            return this.evalFormula(grid, grid.cellValueOf(field, idProperty), field, idProperty, true);
        },
    
        widgetsToValue: function(grid, formula){
            return formula.replace(new RegExp(this.widgetPattern, 'g'), function(match, p1){
                return grid.form.valueOf(p1);
            });
        },
        
        parseFormula: function(grid, formula, field, idProperty){
            if (grid.formulaCache[idProperty][field] ===  '%inprocess%'){
                throw messages.circularReference;
            }
            grid.formulaCache[idProperty][field] = '%inprocess%';
            return this.cellsToValue(grid, this.cellRangesToArray(grid, this.widgetsToValue(grid, this.RCtoRelative(formula, field, idProperty))));
        },
            
        evalFormula: function(grid, formulaOrValue, field, idProperty, needsStringDelimiters){
            if (formulaOrValue == undefined){
                return '';
            }else if (typeof formulaOrValue === 'string' && formulaOrValue.charAt(0) === '='){
                try{
                    if (!grid.formulaCache[idProperty]){
                        grid.formulaCache[idProperty] = {};
                    }
                    return grid.formulaCache[idProperty][field] || (grid.formulaCache[idProperty][field] = eutils.eval(eutils.nameToFunction(this.parseFormula(grid, formulaOrValue.slice(1), field, idProperty))));
                }catch(err){
                    return '%' + err;
                }
            }else{
                return (needsStringDelimiters && typeof formulaOrValue === 'string' && isNaN(formulaOrValue) ? '"' + formulaOrValue + '"' : formulaOrValue);
            }
        },

        formulaesMap: function(grid, callback){
            for (var idProperty in grid.formulaCache){
                var formulaesInRow = grid.formulaCache[idProperty];
                for (var field in formulaesInRow){
                    callback(grid, field, idProperty);
                }
            }
        },

        updateRowReferences: function(grid, newRows){
            var rowPattern = "([a-zA-Z]+)(" + Object.keys(newRows).join('|') + ")([$]?)([^\\d])";
            var formulaUpdate = function(grid, field, idProperty){
                var callback = function(match, p1, p2, p3, p4){
                    return p1 + newRows[p2] + p3 + p4;
                }      
                var formula = grid.cellValueOf(field, idProperty);
                var newFormula = formula.replace(new RegExp(rowPattern, 'g'), callback);
                if (newFormula !== formula){
                    grid.setCellValueOf(newFormula, field, idProperty);
                }
            }
            this.formulaesMap(grid, formulaUpdate);
        },

        updateColumnReferences: function(grid, newFields){
            var columnPattern = "(" + Object.keys(newFields).join('|') + ")([$]?)(\\d+)";
            var formulaUpdate = function(grid, field, idProperty){
                var callback = function(match, p1, p2, p3){
                    return newFields[p1] + p2 + p3;
                }      
                var formula = grid.cellValueOf(field, idProperty);
                var newFormula = formula.replace(new RegExp(columnPattern, 'g'), callback)
                if (newFormula !== formula){
                    grid.setCellValueOf(newFormula, field, idProperty);
                }
            }
            this.formulaesMap(grid, formulaUpdate);
            this.formulaCache = {};
        },

        insertColumn: function(grid, currentColumn){
            var columns = grid.get('columns'), newColumn = grid.newColumnArgs, newColumns = {}, oldFields = {}, newFields = {}, currentField = currentColumn.field, newField, columnIsInserted = false, pane = grid.form;
            for (var field in columns){
                if (! columnIsInserted){
                    newColumns[field] = columns[field];
                    if (field === currentField){
                        newField = (field === 'rowId' ? 'A' : String.fromCharCode(field.charCodeAt(0)+1));
                        newColumns[newField] = lang.mixin(lang.clone(newColumn), {field: newField, label: newField, id: newField});
                        oldFields[newField] = '*+';
                        columnIsInserted = true;
                    }
                }else{
                    var newField = String.fromCharCode(field.charCodeAt(0)+1);
                    newColumns[newField] = lang.mixin(lang.mixin({}, columns[field]), {field: newField, label: newField, id: newField});
                    newFields[field] = newField;
                    oldFields[newField] = field;
                }
            }
            if (!utils.empty(newFields)){
                	this.updateColumnReferences(grid, newFields);
            }
            newFields[newField] = '*+';
            grid.dirty = this.updatedDirtyForColumns(grid, oldFields);
            grid.formulaCache = {};
            this.updateCustomizationForColumns(grid, oldFields);
            grid.set('columns', newColumns);
        }, 

        deleteColumn: function(grid, currentColumn){
            var columns = grid.get('columns'), newColumns = {}, oldFields = {}, newFields = {}, currentField = currentColumn.field, columnIsDeleted = false, lastField, pane = grid.form;
            for (var field in columns){
                if (!columnIsDeleted){
                    if (field !== currentField){
                        newColumns[field] = columns[field];
                    }else{
                        columnIsDeleted = true;
                    }
                }else{
                    var newField = String.fromCharCode(field.charCodeAt(0)-1);
                    newColumns[newField] = lang.mixin(columns[field], {field: newField, label: newField, id: newField});
                    newFields[field] = newField;
                    oldFields[newField] = field;
                }
                lastField = field;
            }
            if (!utils.empty(newFields)){
                this.updateColumnReferences(grid, newFields);
            }
            newFields[lastField] = oldFields[lastField] = '*-';
            grid.dirty = this.updatedDirtyForColumns(grid, oldFields);
            this.updateCustomizationForColumns(grid, oldFields);
            grid.formulaCache = {};
            grid.set('columns', newColumns);
        },    


        updatedDirtyForColumns: function(grid, oldFields){
            var dirty = grid.dirty, data = grid.store.fetchSync(), idProperty = grid.store.idProperty;
            var newDirty = {};
            data.forEach(function(rowData){
                var id = rowData[idProperty], newRowDirty = newDirty[id] = {}, rowDirty = dirty[id] || {};
                var valueOf = function(field){
                        return (typeof rowDirty[field] !== "undefined" ? rowDirty[field] : rowData[field]);                    
                }
                var setNewDirty = function(field, oldField){
                    var oldValue = rowData[field];
                	switch (oldField) {
                    	case '*-': 
	                        if (typeof oldValue !== "undefined"){
	                            newRowDirty[field] = '~delete';
	                        }
	                        break;
/*
                    	case '*+':
                    		if (typeof oldValue !== "undefined"){
                    			newRowDirty[field] = '~delete';
                    		}
                    		break;
*/
                    	default:
                            var newValue = valueOf(oldField);
	                        if (typeof newValue === "undefined"){
	                        	if (typeof oldValue !== "undefined"){
	                        		newRowDirty[field] = '~delete';
	                        	}
	                        }else if (newValue !== oldValue){
	                            newRowDirty[field] = newValue;
	                        }
                    }

                }
                for (field in rowDirty){
                    if (!oldFields[field]){
                        newRowDirty[field] = rowDirty[field];
                    }
                }
                for (field in oldFields){
                    var oldField = oldFields[field];
                    setNewDirty(field, oldField);
                }
            });
            wutils.markAsChanged(grid, 'noStyle');
            return newDirty;
        }, 
        
        updateCustomizationForColumns: function(grid, oldFields){
        	var newCustomization = {}, pane = grid.form, customization = ((((pane.itemCustomization || {}).widgetsDescription || {})[grid.widgetName] || {}).atts || {}).columns || {}, wasDelete = false,
        		defaultColsNumber = grid.defaultColsNumber, newColumnArgsInput = grid.newColumnArgs.input;
        	utils.forEach (oldFields, function(field, newField){
        		switch (field){
        			case '*-':
        				newCustomization[newField] = {"~replace" : "~delete"};
        				break;
        			case '*+':
        				newCustomization[newField] = lang.mixin(lang.clone(newColumnArgsInput), {label: newField, field: newField});
        				break; 
        			default:
                		var column = grid.columns[field];
        				if (!grid.columns[newField]){
            				newCustomization[newField] = lang.mixin(lang.clone(newColumnArgsInput), {label: newField, field: newField});
        				}else{
        					var newCustom = customization[newField] || {};
        					if (column.width !== newColumnArgsInput.width){
        						newCustom.width = column.width;
        					}
        					if (column.hidden){
        						newCustom.hidden = column.hidden;
        					}
        					if (!utils.empty(newCustom)){
        						newCustomization[newField] = newCustom;
        					}
        				}
        		}
        	});
        	//lang.hitch(pane, pane.addCustom)(newCustomization, ['widgetsDescription', grid.widgetName, 'atts', 'columns'], 'itemCustomization');
        	lang.setObject(grid.itemCustomization + '.widgetsDescription.' + grid.widgetName + '.atts.columns.itemCustomization', newCustomization, pane);
        }
    }
});
