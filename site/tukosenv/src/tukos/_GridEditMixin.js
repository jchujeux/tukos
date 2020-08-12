/*
 *  tukos grids  mixin for dynamic widget information handling and cell rendering (widgets values and attributes that may be modified by the user or the server)
 *   - usage: 
 */
define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/promise/all", "dojo/on", "dojo/when", "dijit/registry", "dijit/focus", "tukos/utils", "tukos/dateutils", "tukos/evalutils", "tukos/sheetUtils", 
         "tukos/widgetUtils", "tukos/menuUtils", "tukos/widgets/widgetCustomUtils", "tukos/widgets/WidgetsLoader", "tukos/PageManager", "tukos/TukosTooltipDialog", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(arrayUtil, declare, lang, all, on, when, registry, focusUtil, utils, dutils, eutils, sutils, wutils, mutils, wcutils, WidgetsLoader, Pmg, TukosTooltipDialog, messages){
    var mixin = declare(null, {

        constructor: function(args){
            if (args.newColumnArgs){
                args.newColumnArgs.input = lang.clone(args.newColumnArgs)
            	this.setColArgsFunctions(args.newColumnArgs);
            }
            this.deleted = [];
            this.maxId = this.maxServerId = this.newRowPrefixId = 0;
            //this.isNotUserEdit = 0;
            this.isUserEdit = false;
        },
        
        postCreate: function(){
            this.inherited(arguments);
            this.noCopyCols = this.noCopyCols || ['id', 'idg',  'rowId', 'connectedIds'];
            var pasteCellCallback = function(evt){
                var copiedCell = Pmg.getCopiedCell();
                if (evt.ctrlKey && copiedCell){
                    this.setCellValueOf(sutils.pasteCell(copiedCell, sutils.copyCell(this.clickedCell)));
                    //this.refresh({keepScrollPosition: true});
                }
            }
            this.addKeyHandler(86, pasteCellCallback);
            this.connectedWidgets = {};
            this.on("dgrid-editor-show", lang.hitch(this, function(evt){
                var editor = evt.editor, column = evt.column;
                editor.widgetType = column.widgetType;
                if (!editor.contextMenu){
                    mutils.buildContextMenu(editor,{type: 'DynamicMenu', atts: {targetNodeIds: [editor.domNode]}, items: lang.hitch(wcutils, wcutils.customizationContextMenuItems)(editor, column)});
                }
            	//focusUtil.focus(editor.containerNode);// required for LazyEditor which is a ContentCOntainer and then does not get focused by default. Focus is required for onBlur to be activated and shared editor be removed
            }));
        },
        newRowPrefixNamedId: function(id){
        	var newRowPrefix = this.newRowPrefix;
        	if (id && newRowPrefix && id.indexOf(newRowPrefix) === 0){
        		return this.store.filter({id: id}).fetchSync()[0].name + ' (' + Pmg.message(newRowPrefix) + ' ' + id.substring(newRowPrefix.length) + ')';
        	}else{
        		return Pmg.namedId(id);
        	}
        },
        newRowPrefixNamedIdItem: function(id){
        	var newRowPrefix = this.newRowPrefix;
        	if (id && newRowPrefix && id.indexOf(newRowPrefix) === 0){
        		return {id: id, name: this.store.filter({id: id}).fetchSync()[0].name + ' (' + Pmg.message(newRowPrefix) + ' ' + id.substring(newRowPrefix.length) + ')'};
        	}else{
        		return {id: id, name: Pmg.namedId(id)};
        	}
        },
        emptyRowItem: function(idPropertyValue){
            var emptyItem = {};
            emptyItem[this.collection.idProperty] = idPropertyValue;
            return emptyItem;
        }, 
        cellValueOf: function(field, idPropertyValue){
            //var id = idProperty ? idProperty : this.clickedRow.data[this.collection.idProperty];
        	var id = idPropertyValue || this.clickedRowIdPropertyValue();
            var result = (this.dirty[id] && this.dirty[id][field] ? this.dirty[id][field] : this.collection.getSync(id)[field]);
            return (typeof result === "undefined" || result === null) ? '' : result;
        },

        setCellValueOf: function(value, field, idPropertyValue){
            if (value !== undefined){
            	var id = idPropertyValue || this.clickedRowIdPropertyValue();
                var field = field || this.clickedCell.column.field;
                var item = this.rowValues(id);
                if (value !== item[field]){
                    this.updateDirty(id, field, value);
                }
            }
        },
        
        idPropertyOf: function(field, value){
            for (var idProperty in this.dirty){
                if (this.dirty[idProperty][field] === value){
                    return idProperty;
                }
            }

            var filter = {}, idProperty;
            filter[field] = value;
            this.collection.filter(filter).forEach(function(item){
                idProperty = item.idg;
            });
            return idProperty;
        },

        cellValue: function(row, field){
            var id = row[this.collection.idProperty];
            return (this.dirty[id] && this.dirty[id][field] !== undefined ? this.dirty[id][field] : (row[field] ? row[field] : ''));    
        },
        getNewId: function(){
            if (this.newRowPrefix){
            	this.newRowPrefixId += 1;
            	return this.newRowPrefix + this.newRowPrefixId;
            }else{
            	this.maxId += 1;
                return this.maxId;
            }
        },
        getLastNewRowPrefixId: function(){
        	return this.newRowPrefix ? this.newRowPrefixId : '';
        },
        updateDirty: function(idPropertyValue, field, value, isNewRow){
            var collection = this.collection, grid = this, collectionRow = this.collection.getSync(idPropertyValue), oldValue;
            if (isNewRow || ((oldValue = utils.drillDown(this, ['dirty', idPropertyValue, field], undefined)|| collectionRow[field]) !== value)){
                this.inherited(arguments);
                collectionRow[field] = value;
                if (!grid.noRefreshOnUpdateDirty){
                	sutils.refreshFormulaCells(grid);
                }
                if (this.isUserEdit){
                	if (this.onChangeNotify){
                		var item = lang.mixin(lang.clone(this.dirty[idPropertyValue]), {idg: idPropertyValue, connectedIds: this.collection.getSync(idPropertyValue).connectedIds});
                    	this.notifyWidgets({action: 'update',  item: item, sourceWidget: this});            		
                	}
                    if (this.columns[field]){
                    	this.onCellChangeLocalAction(idPropertyValue, this.columns[field], value, oldValue);
                    }
                    this.setSummary();
                    this.isUserEdit = false;
                }
                wutils.markAsChanged(this, 'noStyle');            	
            }
        },
                
        deleteDirty: function(idPropertyValue){
            var wName = this.widgetName;
        	delete this.dirty[idPropertyValue];
            if (this.onChangeNotify && this.isUserEdit){
                this.notifyWidgets({action: 'delete', item: lang.mixin(this.collection.getSync(idPropertyValue), this.dirty[idPropertyValue])});
            }
            if (utils.empty(this.dirty) && this.deleted.length === 0 && this.form.changedWidgets[wName]){
                delete this.form.changedWidgets[wName];
                delete this.form.userChangedWidgets[wName]
            }
            this.setSummary();
        },

        onCellChangeLocalAction: function(idPropertyValue, column, newValue, oldValue){
            var editorArgs = column.editorArgs;
        	if (typeof column.localDataChangeActionFunctions == "undefined"){
                column.localDataChangeActionFunctions = {};
                var localAction = editorArgs.onChangeLocalAction || (editorArgs.onWatchLocalAction && (editorArgs.onWatchLocalAction['value'] || editorArgs.onWatchLocalAction['checked']));
                if (localAction){
                    this.form.buildLocalActionFunctions(column.localDataChangeActionFunctions, localAction);
                }
            }
            var localActionFunctions = column.localDataChangeActionFunctions;
            if (!utils.empty(localActionFunctions)){
            	var sourceCell = this.cell(idPropertyValue, column.field), idPropertyValue, sourceWidget = this.getEditorInstance(column.field) || sourceCell.element.widget || sourceCell.element.input,
            		allowedNestedRowWatchActions = this.allowedNestedRowWatchActions;
            	this.noRefreshOnUpdateDirty = true;
                this.nestedRowWatchActions = this.nestedRowWatchActions || 0;
                column.nestedWatchActions = column.nestedWatchActions || 0;
            	for (var colName in localActionFunctions){
                    var targetCell = this.cell(idPropertyValue, colName), widgetActionFunctions =  localActionFunctions[colName], result, source = sourceWidget;
                    for (var att in widgetActionFunctions){
                    	if (att === 'value' || att === 'localActionStatus'){
                            if ((allowedNestedRowWatchActions === undefined || (this.nestedRowWatchActions <= allowedNestedRowWatchActions)) && column.nestedWatchActions < 1){
	                        	this.nestedRowWatchActions += 1;
	                        	column.nestedWatchActions += 1;
	                        	//if (this.nestedRowWatchActions > 1 || !sourceWidget){
	                        		source = sourceWidget || sourceCell;
	                        		if (!source.parent){
	                        			source = lang.mixin(source, {parent: this, form: this.form, valueOf: wutils.valueOf, setValueOf: wutils.setValueOf, setValuesOf: wutils.setValuesOf});
	                        		}
	                        	//}
	                        	var result = widgetActionFunctions[att].action(source, targetCell, newValue, oldValue);
	                        	if (att === 'value'){
	                            	this.updateDirty(idPropertyValue, colName, result);
	                        	}
	                        	this.nestedRowWatchActions += -1;
	                        	column.nestedWatchActions += -1;
                            }
                        }
                    }
                }
            	this.noRefreshOnUpdateDirty = false;
                setTimeout(lang.hitch(this, this.refresh), 0);
            }
        },
        prepareInitRow: function(init){
            for (var col in this.initialRowValue){
                init[col] = this.initialRowValue[col];
            }
            return init;
        },

        offsetRowsId: function(fromRowId, increment){
            var self = this, newRows = {}, noRefresh = this.noRefreshOnUpdateDirty;
            this.collection.forEach(function(object){
                if (object.rowId >= fromRowId){
                    newRows[object.rowId] = object.rowId + increment;
                }
            });
            this.noRefreshOnUpdateDirty = true;
            sutils.updateRowReferences(this, newRows);
            this.collection.forEach(function(object){
                if (object.rowId >= fromRowId){
                    object.rowId +=increment;
                    self.collection.putSync(object, {overwrite: true});//can be removed ?
                    self.updateDirty(object.idg, 'rowId', object.rowId);
                }
            });
            this.noRefreshOnUpdateDirty = noRefresh;
        },
        lastRowId: function(){
            var maxRowId = 0;
            this.collection.forEach(function(object){
                if (object.rowId > maxRowId){
                    maxRowId = object.rowId;
                }
            });
            return maxRowId;
        },
        createNewRow: function(item, currentRowData, where){
            var noRefresh = this.noRefreshOnUpdateDirty;
        	this.noRefreshOnUpdateDirty = true;
            if ('rowId' in this.columns && where !== undefined){
                if (where === 'before'){
                    //item.rowId = currentRowData.rowId;
                    this.offsetRowsId(item.rowId = currentRowData.rowId, 1);
                }else{
                    item.rowId = this.lastRowId() + 1;
                }
            }
            if (this.initialId || this.newRowPrefix){
                item.id = this.getNewId();
            }else{
                //delete item.id;
            }
            this.store.addSync(item, (where === 'before' ? {beforeId: currentRowData.idg} : {}));
            if (this.onChangeNotify){
                this.notifyWidgets({action: 'add', item: item});
            }
            //this.isNotUserEdit += 1;
           for (var j in item){
                if (j != 'idg' && 'j' !== 'connectedIds'){
                    this.updateDirty(item.idg, j, item[j], true);
                }
            }
            //this.isNotUserEdit += -1;
            this.noRefreshOnUpdateDirty = noRefresh;
        },
        addRow: function(where, item){
            var init={};
            this.prepareInitRow(init);
            item = utils.merge(init, item||{});
            this.createNewRow(item, (where === 'before' ? this.clickedRow.data : {}), where);
            //if (!this.noRefreshOnUpdateDirty){
            	//this.refresh({keepScrollPosition: true});
            //}
            return item;
        },
        updateRow: function(item, replace){
        	var idPropertyValue = item[this.collection.idProperty], storeItem = this.collection.getSync(idPropertyValue) || {}, noRefresh = this.noRefreshOnUpdateDirty;
        	//this.isNotUserEdit += 1;
        	this.noRefreshOnUpdateDirty = true;
        	if(replace){
            	utils.forEach(storeItem, lang.hitch(this, function(value, col){
            		if (!utils.in_array(col, ['connectedIds', 'idg', 'rowId', 'id'])){
            			this.updateDirty(idPropertyValue, col, item[col] || '');
            		}
            	}));        		
        	}
        	utils.forEach(item, lang.hitch(this, function(value, col){
        		if (value !== storeItem[col] && col !== 'connectedIds'){
        			this.updateDirty(idPropertyValue, col, value);
        		}
        	}));       		
        	if (this.onChangeNotify){
        		if (storeItem.connectedIds){
        			item.connectedIds = storeItem.connectedIds;
        		}
        		this.notifyWidgets({action: 'update', item: item});
        	}
            this.noRefreshOnUpdateDirty = noRefresh;
        	if (!noRefresh){
                this.refresh({keepScrollPosition: true});
        	}
        	//this.isNotUserEdit += -1;
        },
        copyItem: function(item){
            var newItem = lang.clone(item), noCopyCols = this.noCopyCols;
            for (var col in noCopyCols){
                delete(newItem[noCopyCols[col]]);
            }
            return newItem;
        },
        
        copyRow: function(evt){
            var numberOfCopies = 1, self = this;
            var applyCallback = function(){
                var numberOfCopies = parseInt(this.pane.valueOf('copies') || '1'), incrementFrom = this.pane.valueOf('incrementfrom') , noRefresh = self.noRefreshOnUpdateDirty;
                self.noRefreshOnUpdateDirty = true;
                this.pane.close();
                var data = self.clickedRowValues(), name = data.name || '';;
                if (incrementFrom && 'name' in self.columns){
                	var hasIncrement = true;
                	data.name = name + ' ' + incrementFrom;
                	self.updateDirty(data[self.collection.idProperty], 'name', data.name);
                	incrementFrom = parseInt(incrementFrom);
                }else{
                	var hasIncrement = false;
                	incrementFrom = 0;
                }
                var item = self.copyItem(data);
                if ('rowId' in self.columns){
                        self.offsetRowsId(data.rowId + 1, numberOfCopies);
                }
                for (var i = 1; i <= numberOfCopies; i++){
                    if ('rowId' in self.columns){
                    	item.rowId = data.rowId + i;
                    }
                	item.name = name + (hasIncrement ? ' ' + i + incrementFrom : '');
                    utils.forEach(item, function(value, col){
                    	if (typeof value === 'string' && value[0] === '='){
                    		item[col] = sutils.offsetFormula(self, value, i, 0);
                    	}
                    });
                	self.addRow(undefined, item);
                }
                this.noRefreshOnUpdateDirty = noRefresh;
                if (!noRefresh){
                    this.refresh({keepScrollPosition: true});
                }
            };
            var dialog = new TukosTooltipDialog({paneDescription: {
                    widgetsDescription: {
                        copies: {type: 'TextBox', atts: {title: messages.numberofcopies, placeHolder: messages.numberofcopies + '  ...', style: {width: '5em'}}},
                        incrementfrom: {type: 'TextBox', atts: {title: messages.incrementfrom, placeHolder: messages.incrementfrom + '  ...', style: {width: '5em'}}},
                        cancel: {type: 'TukosButton', atts: {label: messages.close, onClickAction:  'this.pane.close();'}},
                        apply: {type: 'TukosButton', atts: {label: messages.apply, onClick:applyCallback}}
                    },
                    layout:{
                        tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},
                        contents: {
                           row1: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},  widgets: ['copies', 'incrementfrom']},
                           row2: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},  widgets: ['cancel', 'apply']}
                        }
                    }
                }});
            dialog.open({x: evt.clientX, y: evt.clientY});
        },
        
        deleteRow: function(rowItem, skipDeleteAction){
            this.deleteRowItem(rowItem || this.clickedRow.data, skipDeleteAction);
            var grid = this;
            this.refresh({keepScrollPosition: true});
        },

        deleteRowItem: function(item, skipDeleteAction){
            var noRefresh = this.noRefreshOnUpdateDirty;
            this.noRefreshOnUpdateDirty = true;
			if (!skipDeleteAction){
				eutils.actionFunction(this, 'deleteRow', this.deleteRowAction, 'row', item);
			}
        	if (item.id != undefined && (this.newRowPrefix ? item.id.substring(0,3) !== this.newRowPrefix : true) && (this.initialId ? item.id <= this.maxServerId : true)){
                var toSendOnDelete = {id: item.id, '~delete': true};
            	if (this.sendOnDelete){
            		this.sendOnDelete.forEach(function(col){
	                	var value = item[col];
	                	if (value){
	                		toSendOnDelete[col] = value;
	                	}
            		});
            	}
                this.deleted.push(toSendOnDelete);
                wutils.markAsChanged(this, 'noStyle');
            }
            var idgToDelete = item.idg;
            if ('rowId' in this.columns){
                this.offsetRowsId(item.rowId, -1);
            }
            this.collection.removeSync(idgToDelete);
            this.deleteDirty(idgToDelete);
            this.noRefreshOnUpdateDirty = noRefresh;
        },
        deleteRows: function(rows, skipDeleteAction){
        	var self = this;
        	rows.forEach(lang.hitch(this, function(row){
        		this.deleteRowItem(row, skipDeleteAction);
        	}));
        },
        moveRow: function(itemToMove, currentRowData, where){
            if ('rowId' in this.columns){
                var noRefresh = this.noRefreshOnUpdateDirty;
            	this.noRefreshOnUpdateDirty = true;
            	this.offsetRowsId(itemToMove.rowId, -1);
                if (where === 'before'){
                    targetRowId = currentRowData.rowId;
                    this.offsetRowsId(targetRowId, 1);
                    itemToMove.rowId = targetRowId;
                }else{
                    itemToMove.rowId = this.lastRowId()+1;
                }
                this.collection.putSync(itemToMove, (where === 'before' ? {beforeId: currentRowData.idg, overwrite: true}: {}));
                this.noRefreshOnUpdateDirty = noRefresh;
                this.updateDirty(itemToMove.idg, 'rowId', itemToMove.rowId);
            } 
        },

        addColumn: function(){
            sutils.insertColumn(this, this.clickedCell.column);
        },

        deleteColumn: function(){
            if (this.clickedCell.column.cannotDelete){
                Pmg.setFeedback(messages.cannotdeletecolumn, undefined, true);
            }else{
                columns = sutils.deleteColumn(this, this.clickedCell.column);
            }
        },

        _getValue: function(){// Caution: only returns modified (from dirty) and deleted (from deleted) to send back those modified values to the server
            var rowCount = 0, result = new Array, j = 0, sendOnSave = this.sendOnSave || [], noSendOnSave = utils.flip(this.noSendOnSave || []), dirtyToSend;
            for (var i in this.dirty){
                dirtyToSend = {};
            	utils.forEach(this.dirty[i], function(value, col){
                	if (!noSendOnSave.hasOwnProperty(col)){
                		dirtyToSend[col] = value;
                	}
                });
                if (!utils.empty(dirtyToSend)){
                	result[j] = dirtyToSend;
	            	var storeValues = this.collection.getSync(i), id = storeValues.id;//, updated = storeValues.updated;
	                if (id != undefined){
	                    result[j].id = id;
	                    sendOnSave.forEach(function(col){
	                    	var value = storeValues[col];
	                    	if (value){
	                    		result[j][col] = value;
	                    	}
	                    });
	                }
	                delete result[j].connectedIds;
                }
                j++;
            }
            //result = result.concat(this.deleted);
            return this.deleted.concat(result);            
        },
        
        keepChanges: function(){
            return {data: this.store.fetchSync(), dirty: this.dirty, deleted: this.deleted};
        },
        
        iterate: function(callback){
        	var idProperty = this.collection.idProperty;
        	this.collection.fetchSync().forEach(lang.hitch(this, function(item){
        		callback(lang.mixin(lang.clone(item), this.dirty[idProperty]));
        	}));
        },
        
        restoreChanges: function (changes){
            this.store.setData(changes.data);
            this.dirty = changes.dirty;
            this.deleted = changes.deleted;
             if(!utils.empty(this.dirty) || this.deleted.length > 0){
                //this.form.changedWidgets[this.widgetName] = this;
                 wutils.markAsChanged(this, 'noStyle');
            }
            this.refresh({keepScrollPosition: true});
            return true;
        },
        _setValue: function(value){
        	var noRefresh = this.noRefreshOnUpdateDirty, resetCounters = true, idp = this.collection.idProperty;
        	this.noRefreshOnUpdateDirty = true;
        	this.formulaCache = {};
            if (value == ''){//the Memory store needs to be emptied
                this.store.setData([]); 
                this.dirty = {};
            }else if(value instanceof Array){//a new  memory store needs to be filled in with the array value
            	if (this.form.markIfChanged && this.initialId){// is to be considered as a change, not reflecting then server store content
            		var rowsToDelete = [];
            		this.store.forEach(lang.hitch(this, function(row){
            			for (var r in value){
            				var rValue = value[r];
            				if (row.id === rValue.id){
            		            rValue[idp] = row[idp];
            					this.updateRow(rValue);
            					delete value[r];
            					return;
            				}
            			}
            			rowsToDelete.push(row);
            		}));
            		this.deleteRows(rowsToDelete, true);
            		for (var r in value){
            			this.addRow(null, value[r]);
            		}
            		resetCounters = false;
            	}else{
                	this.store.setData(value); 
                    if (this.onChangeNotify/* && this.isUserEdit*/){
                        this.notifyWidgets({action: 'create'});
                    }
                    this.dirty = {};
                    if (this.form && this.form.markIfChanged){
                        this.store.forEach(lang.hitch(this, function(row){
                            if (!this.isSubObject ||!row['id']){
                                for (var i in row){
                                    if (i != 'idg' && i !== 'updator' && i !== 'updated' && i !== 'canEdit' && i !== 'connectedIds'){// warging: there may be other read-only fields to exclude from dirty here
                                        this.updateDirty(row['idg'], i, row[i]);
                                    }
                                }
                            }
                        }));
                    }          		
            	}
            }else{//current memory store needs to be updated with contents of current object, then saved (to empty dirty)
/*
            	for (var row in value){
                    for (var col in value[row]){
                        this.updateDirty(row, col, value[row][col]);
                    }
                }
*/
                this.store.setData([]);
                this.dirty = {};
            	for (var row in value){
                	this.store.addSync(value[row]);
                }
                console.log('_GridEditMixin: thought this was not in use!!')
                //this.save();
            }
            if (resetCounters){
                this.newRowPrefixId = 0;
                this.deleted = [];
            }
            var maxId = this.maxId = 0;
            this.store.forEach(function(row){
                if (row.id > maxId){
                    maxId = row.id;
                }
            });
            this.maxId = maxId;
            if (!this.form.markIfChanged && maxId > this.maxServerId){
            	this.maxServerId = maxId;
            }
            this.noRefreshOnUpdateDirty = noRefresh;
            this.set('collection', this.store.getRootCollection());
            this.setSummary();
        },

        _setDuplicate: function(value){
            var data = this.collection.fetchSync({}), noRefresh = this.noRefreshOnUpdateDirty;
            this.noRefreshOnUpdateDirty = true;
            data.forEach(function(item){
                item['id'] = null;
                for (var col in item){
                    if (col != 'canEdit' && col != 'idg' && col != 'updator' && col != 'updated'){
                        this.updateDirty(item[idProperty], col, item[col]);
                    }
                }
                this.collection.putSync({idg: item.idg});
            });
            this.noRefreshOnUpdateDirty = noRefresh;
        },

        getObject: function(node){
            var grid = this.grid;
            var rowIdProperty = node.id.slice(grid.id.length + 5);
            var row = grid.collection.getSync(rowIdProperty);
            if (grid.dirty[rowIdProperty]){
                row = lang.mixin(row, grid.dirty[rowIdProperty]);
            }
            return row;
        }, 

        onDropInternal: function (nodes, copy, targetItem) {//override dgrid/extensions/dnd
            var grid = this.grid,
                store = grid.collection,
                targetSource = this,
                anchor = targetSource._targetAnchor,
                targetRow,
                nodeRow;
    
            if (anchor) { // (falsy if drop occurred in empty space after rows)
                targetRow = this.before ? anchor.previousSibling : anchor.nextSibling;
            }
    
            nodeRow = grid.row(nodes[0]);
            if (!copy && (targetRow === nodes[0] ||
                    (!targetItem && nodeRow && grid.down(nodeRow).element === nodes[0]))) {
                return;//drop is not moving anything
            }
    
            nodes.forEach(function (node) {
                when(targetSource.getObject(node), function (object) {
                    var id = store.getIdentity(object);
                    if (copy){
                        grid.createNewRow(lang.clone(object), targetItem, (targetItem ? 'before' : 'append'));
                    }else{
                        grid.moveRow(object, targetItem, (targetItem ? 'before' : 'append'));
                    }
                    // Self-drops won't cause the dgrid-select handler to re-fire,
                    // so update the cached node manually
                    if (targetSource._selectedNodes[id]) {
                        targetSource._selectedNodes[id] = grid.row(id).element;
                    }
                });
            });
            grid.refresh({keepScrollPosition: true});
        },

        onDropExternal: function (sourceSource, nodes, copy, targetItem) {
            var tGrid = this.grid, sGrid = sourceSource.grid, noRefresh = this.noRefreshOnUpdateDirty;
            this.noRefreshOnUpdateDirty = true;
        	if (tGrid.onDropCondition){
            	if (!tGrid.onDropConditionFunction){
            		tGrid.onDropConditionFunction = eutils.eval(tGrid.onDropCondition);
            	}
            	if (!tGrid.onDropConditionFunction(sGrid, tGrid)){
            		return;
            	}
            }
        	var store = tGrid.collection, mapping = tGrid.onDropMap && tGrid.onDropMap[sGrid.widgetName];
            if (mapping){
                var dropMode = mapping.mode, fieldsMapping = mapping.fields;
            }
            if (mapping && dropMode ===  'update'){
                nodes.forEach(function(node){
                    when (sourceSource.getObject(node), function(object){
                        for (field in fieldsMapping){
                            var sourceField = fieldsMapping[field];
                            if (object[sourceField]){
                                targetItem[field] = object[sourceField];
                                tGrid.updateDirty(targetItem.idg, field, targetItem[field]);
                            }
                        }
                        tGrid.collection.putSync(targetItem, {overwrite: true});
                    });
                });
            }else{    
                // TODO: bail out if sourceSource.getObject isn't defined?
                nodes.forEach(function (node) {
                    when(sourceSource.getObject(node), function (object) {
                        if (!copy) {
                            if (sGrid) {                            
                                sGrid.deleteRowItem(object);
                            }
                            else {
                                sourceSource.deleteSelectedNodes();
                            }
                        }
                        if (mapping){
                             var newItem = {};
                             for (field in fieldsMapping){
                                var sourceField = fieldsMapping[field];
                                if (object[sourceField]){
                                    newItem[field] = object[sourceField];
                                }
                            }
                        }else{
                            var newItem = lang.clone(object);
                        }
                        var init={};
                        tGrid.prepareInitRow(init);
                        tGrid.createNewRow(lang.mixin(init, utils.filter(newItem)), targetItem, (targetItem ? 'before' : 'append'));
                    });
                });
            }
            if (!copy){
                sourceSource.selectNone(); // deselect all
            }
            if (sGrid){
                sGrid.setSummary();
            } 
            tGrid.setSummary();
            this.noRefreshOnUpdateDirty = noRefresh;
            if (!noRefresh){
                tGrid.refresh({keepScrollPosition: true});           	
            }
            setTimeout(
                function(){
                    tGrid.layoutHandle.resize();
                    tGrid.bodyNode.scrollTop = tGrid.bodyNode.scrollHeight;
                    if (sGrid){
                        sGrid.bodyNode.scrollTop = sGrid.bodyNode.scrollHeight;
                    }
                },
                0
            );
        },

        notifyWidgets: function(args){
            this.form.notifyWidgetsDepth = this.form.notifyWidgetsDepth || 0;
        	if (!(this.inNotifyWidgets || this.noNotifyWidgets)){
                this.form.notifyWidgetsDepth += 1;
            	this.inNotifyWidgets = true;
            	var self = this, action = args.action;
            	args.sourceWidget = this;
                for (var widgetName in this.onChangeNotify){
                    var directive = ((this.onChangeNotifyDirectives || {})[widgetName] || {})[action] || {};
                	if (directive !== false){
                		args.forceNotify = directive.forceNotify;
                		var widget = this.form.getWidget(widgetName);
                        var filter = directive.targetFilter ? lang.hitch(widget, widget.itemFilter)() : {};// here if mapping on a filter colomn between this ans widget is not identity, filter should be converted accordingly
                        if (action === 'create'){
                        	this.store.filter(filter).fetchSync().forEach(function(item){
                                lang.setObject('connectedIds.' + self.widgetName, item.idg, item);
                                widget.set('notify', {action: 'create', item: item, sourceWidget: self});
                            });
                        }else if(action === 'add'){
                            var item = args.item;
                            if (this.matchesFilter(item, filter)){
                            	lang.setObject('connectedIds.' + this.widgetName, item.idg, item);
                            	widget.set('notify', args);
                            }
                        }else if(typeof (args.item.connectedIds || {})[widgetName] === "undefined"){
                        	if(action === 'update'){
	                        	var item = this.rowValues(args.item[this.collection.idProperty]);
	                        	if (this.matchesFilter(item, filter)){
	                            	item.connectedIds = args.item.connectedIds;
	                            	widget.set('notify', {action: 'create', sourceWidget: args.sourceWidget, item: item});                        		
	                        	}
                        	}
                        }else{
                            widget.set('notify', args);
                        }                    	
                    }
                }
                this.inNotifyWidgets = false;
                this.form.notifyWidgetsDepth -= this.form.notifyWidgetsDepth;
            }
        },
        
        itemFilter: function(){
            return {};
        },
        
        matchesFilter: function(item, itemFilter){
            return true;
        },

        targetItem: function(sourceItem, sourceWidget, mapping){
            var mapping = mapping || ((sourceWidget || {}).onChangeNotify || [])[this.widgetName] || {},
                gridItem = {}, self = this;
            utils.forEach(sourceItem, function(value, col){
            	var targetCol = mapping[col] ? mapping[col] : ((utils.empty(mapping) && !utils.in_array(col, self.noCopyCols) && self.columns[col]) ? col : undefined);
            	if (targetCol){
                    if (value !== null && typeof value !== 'string'){
                        switch (self.columns[targetCol] && self.columns[targetCol].formatType){
                            case 'date' : gridItem[targetCol] = dutils.formatDate(value); break;
                            case 'datetime':
                            case 'datetimestamp':  gridItem[targetCol] = dojo.date.stamp.toISOString(value, {zulu: true}); break;
                        }
                    }else{
                    	gridItem[targetCol] = value;
                    }
            	}
            });
            if (sourceItem.connectedIds){
                 gridItem.connectedIds = sourceItem.connectedIds;
            }
            if (this.matchesFilter(gridItem, this.itemFilter())){
                return gridItem;
            }else{
                return undefined;
            }
        },


        _setNotify: function(args){
        	var notifyCallers = this.notifyCallers = this.notifyCallers || {}, widgetName = this.widgetName;
            this.inSetNotify = (this.inSetNotify || 0);
            //if (!(this.inSetNotify || this.inNotifyWidgets)){
            if ((args.forceNotify || !this.inNotifyWidgets) && !this.inSetNotify){
        	//if (!(this.inNotifyWidgets &&
            		//notifyCallers[widgetName])){
		        //notifyCallers[widgetName] = (notifyCallers[widgetName] || 0) + 1;
	        	this.inSetNotify +=1;
            	//this.isNotUserEdit += 1;
	            var self = this, action = args.action;
	            if (action === 'create' || action === 'add' || action === 'update'){
	            	var item = this.targetItem(args.item, args.sourceWidget, args.mapping);
	                var idPropertyValue = (args.item.connectedIds || {})[widgetName];
	                if ((idPropertyValue && this.store.getSync(idPropertyValue))|| action === 'update'){//transform into an update
	                	item[this.collection.idProperty] = idPropertyValue;
	                	this.updateRow(item);
	                }else{
	                    if (action === 'create' && item !== undefined){
	                        this.store.addSync(item);
	        		        this.notifyWidgets({action: action, item: item});
	                    }else{
	                        item = this.addRow('append', utils.filter(item));
	                    }
	                }
	            }else if (action === 'delete'){
	                this.deleteRow(this.collection.getSync(args.item.connectedIds[this.widgetName]));		            	
			        this.notifyWidgets({action: action, item: args.item});
	            }
		        //notifyCallers[widgetName] += -1;
	            //this.isNotUserEdit += -1;
	            this.inSetNotify += -1;
            //}
            }
        }
    });
/*
    mixin.loadDependingWidgets = function(Widget, atts){
        var loadingDependingWidgets = {};
        if (atts.newColumnArgs){
        	atts.newColumnArgs.input = lang.clone(atts.newColumnArgs)
        }
        for (var i in atts.columns){
            var dependingWidget = atts.columns[i].editor;
            if (dependingWidget){
                loadingDependingWidgets[i] = WidgetsLoader.loadWidget(dependingWidget);
            }
        }
        var newColumnEditor = (atts.newColumnArgs || {}).editor;
        if (newColumnEditor){
            loadingDependingWidgets.newColumnEditor = WidgetsLoader.loadWidget(newColumnEditor);
        }
        return all(loadingDependingWidgets).then(function(Widgets){
            for (var i in loadingDependingWidgets){
                if (i === 'newColumnEditor'){
                    atts.newColumnArgs.editor = Widgets[i];                            	
                }else{
                	atts.columns[i].editor = Widgets[i];                    	
                }
            }
            return Widget;
        });
    };
*/
    return mixin;
});
