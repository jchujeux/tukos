define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "tukos/dgrid/Editor", "tukos/utils", "tukos/dateutils", "tukos/evalutils", "tukos/sheetUtils", 
         "tukos/widgetUtils", "tukos/menuUtils", "tukos/widgets/widgetCustomUtils", "tukos/PageManager", "tukos/TukosTooltipDialog", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
function(declare, lang, when, Editor, utils, dutils, eutils, sutils, wutils, mutils, wcutils, Pmg, TukosTooltipDialog, messages){
	var copyDialogTooltip, itemsColDialogTooltip, 
		applyCopyCallback = function(){
	        var grid = copyDialogTooltip.grid, numberOfCopies = parseInt(copyDialogTooltip.pane.valueOf('copies') || '1'), incrementFrom;
	        copyDialogTooltip.pane.close();
	        var data = grid.clickedRowValues(), name = data.name || '', re = /(^.*)([0-9]+$)/g, match = re.exec(name), item = grid.copyItem(data);
			if (match){
				name = match[1];
				incrementFrom = parseInt(match[2]);
			}else{
				incrementFrom = false;
			}
	        if ('rowId' in grid.columns){
	                grid.offsetRowsId(data.rowId + 1, numberOfCopies);
	        }
	        for (var i = 1; i <= numberOfCopies; i++){
	            if ('rowId' in grid.columns){
	            	item.rowId = data.rowId + i;
	            }
	        	item.name = name + (incrementFrom === false ? '' : (i + incrementFrom));
	            utils.forEach(item, function(value, col){
	            	if (typeof value === 'string' && value[0] === '='){
	            		item[col] = sutils.offsetFormula(grid, value, i, 0);
	            	}
	            });
	        	grid.addRow(undefined, item);
	        }
    	},
        applyItemsColCallback = function(){
        	var grid = itemsColDialogTooltip.grid, pane = itemsColDialogTooltip.pane, col = grid.untranslateColName(pane.valueOf('col')), newValue = pane.valueOf('newValue');
        	if (utils.empty(grid.selection)){
				Pmg.setFeedbackAlert('Needtoselectrows');
			}else{
	        	utils.forEach(grid.selection, function(status, id){
	        		if (status){
	    				var row = grid.row(id), item = row.data; 
						if ((typeof item.canEdit === "undefined") || item.canEdit){
							if (newValue !== "undefined" || typeof item[col] !== 'undefined'){
								grid.updateDirty(id, col, newValue === "undefined" ? undefined : newValue);
							}else{
								Pmg.setFeedbackAlert('Colareadyundefinednothingdone');
							}
						}else{
							grid.deselect(id);
						}
	        		}
	        	});
        	}
			this.contextMenu.menu.onExecute();
        },
		setItemsColDialog = function(grid){
			itemsColDialogTooltip = itemsColDialogTooltip || new TukosTooltipDialog({paneDescription: {
	            widgetsDescription: {
	                col: {type: 'TextBox', atts: {label: Pmg.message('Column'), placeHolder: Pmg.message('Column') + '  ...', style: {width: '8em'}}},
	                newValue: {type: 'TextBox', atts: {label: Pmg.message('NewValue'), placeHolder: Pmg.message('newValue') + '  ...', style: {width: '8em'}}},
	                cancel: {type: 'TukosButton', atts: {label: Pmg.message('close'), onClickAction:  'this.pane.close();'}},
	                apply: {type: 'TukosButton', atts: {label: Pmg.message('apply'), onClick:applyItemsColCallback}}
	            },
	            layout:{
	                tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},
	                contents: {
	                   row1: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: true, labelWidth: 100},  widgets: ['col', 'newValue']},
	                   row2: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},  widgets: ['cancel', 'apply']}
	                }
	            }
	        }});
			itemsColDialogTooltip.grid = grid;
			//itemsColDialogTooltip.pane.setValuesOf({copies: 1, incrementfrom: ''});
			return itemsColDialogTooltip;
		},
		copyDialog = function(grid){
			copyDialogTooltip = copyDialogTooltip || new TukosTooltipDialog({paneDescription: {
	            widgetsDescription: {
	                copies: {type: 'TextBox', atts: {label: Pmg.message('numberofcopies'), placeHolder: Pmg.message('numberofcopies') + '  ...', style: {width: '5em'}}},
	                //incrementfrom: {type: 'TextBox', atts: {title: Pmg.message('incrementfrom'), placeHolder: Pmg.message('incrementfrom') + '  ...', style: {width: '5em'}}},
	                cancel: {type: 'TukosButton', atts: {label: Pmg.message('close'), onClickAction:  'this.pane.close();'}},
	                apply: {type: 'TukosButton', atts: {label: Pmg.message('apply'), onClick:applyCopyCallback}}
	            },
	            layout:{
	                tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},
	                contents: {
	                   row1: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: true, labelWidth: 100},  widgets: ['copies'/*, 'incrementfrom'*/]},
	                   row2: {tableAtts: {cols: 2, customClass: 'labelsAndValues', showLabels: false, labelWidth: 100},  widgets: ['cancel', 'apply']}
	                }
	            }
	        }});
			copyDialogTooltip.grid = grid;
			copyDialogTooltip.pane.setValuesOf({copies: 1, incrementfrom: ''});
			return copyDialogTooltip;
		};
    return declare([Editor], {
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
            this.noCopyCols = ['id', this.store.idProperty,  'rowId', 'connectedIds'].concat(this.noCopyCols || []);
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
            this.on("dgrid-datachange", lang.hitch(this, function(evt){
                if (evt.oldValue !== evt.value){
                    var column = evt.cell.column;
                    if (column.displayedValue == undefined){
                        column.displayedValue = [];
                    }
                    this.isUserEdit = true;
                }
            }));
        },
        untranslateColName: function(name){
			const untranslation = utils.some(this.columns, function(column, field){
				return name === column.title ? field : false;
			});
			if (untranslation){
				return untranslation;
			}else{
				return utils.untranslate(name, this.translations);
			}
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

            var filter = {}, idp = this.collection.idProperty, idProperty;
            filter[field] = value;
            this.collection.filter(filter).forEach(function(item){
                idProperty = item[idp];
            });
            return idProperty;
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
        updateDirty: function(idPropertyValue, field, value, isNewRow, isUserEdit, noMarkAsChanged){
            var collection = this.collection, grid = this, idp = collection.idProperty, collectionRow = collection.getSync(idPropertyValue), oldValue;
            //if (isNewRow || ((oldValue = utils.drillDown(this, ['dirty', idPropertyValue, field], undefined)|| collectionRow[field]) !== value)){
            if (isNewRow || !utils.isEquivalent(oldValue = utils.drillDown(this, ['dirty', idPropertyValue, field], undefined)|| collectionRow[field], value)){
                if (!this.nestedRowWatchActions){
					this.currentUpdateDirtyRow = idPropertyValue;
				}
                this.inherited(arguments);
                collectionRow[field] = value;
                if (isUserEdit || this.isUserEdit){
                    if (this.columns[field]){
                    	this.onCellChangeLocalAction(idPropertyValue, this.columns[field], value, oldValue);
                    }
				}
                if (idPropertyValue !== this.currrentUpdateDirtyRow){
					collectionRow = collection.getSync(this.currentUpdateDirtyRow);
				}
                if (!this.nestedRowWatchActions || idPropertyValue !== this.currentUpdateDirtyRow){
	                if (!grid.noRefreshOnUpdateDirty){
	                	sutils.refreshFormulaCells(grid);
						this.collection.putSync(collectionRow, {overwrite: true});
	                }
	                if (isUserEdit || this.isUserEdit){
	                	if (this.onChangeNotify){
	                		var item = lang.mixin(lang.clone(this.dirty[idPropertyValue]), utils.newObj([[idp, idPropertyValue], ['connectedIds', collectionRow.connectedIds]]));
	                    	this.notifyWidgets({action: 'update',  item: item, sourceWidget: this});            		
	                	}
	                    /*if (this.columns[field]){
	                    	this.onCellChangeLocalAction(idPropertyValue, this.columns[field], value, oldValue);
	                    }*/
	                    if (!this.nestedRowWatchActions){
							this.setSummary();
	                   		this.isUserEdit = false;
	                   	}
	                }
					this.currentUpdateDirtyRow = idPropertyValue;
				}
                if (!(noMarkAsChanged || this.noMarkAsChanged || (this.columns[field] && this.columns[field].noMarkAsChanged))){
                	wutils.markAsChanged(this, 'noStyle', 'user');    
                }        	
            }
        },
                
        deleteDirty: function(idPropertyValue, isUserRowEdit){
            var wName = this.widgetName;
            if (this.onChangeNotify && isUserRowEdit){
                this.notifyWidgets({action: 'delete', item: lang.mixin(this.collection.getSync(idPropertyValue), this.dirty[idPropertyValue])});
            }
        	delete this.dirty[idPropertyValue];
            if (utils.empty(this.dirty) && this.deleted.length === 0 && this.form.changedWidgets[wName]){
                delete this.form.changedWidgets[wName];
                delete this.form.userChangedWidgets[wName]
            }
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
            	var sourceCell = this.cell(idPropertyValue, column.field), sourceWidget = this.getEditorInstance(column.field) || sourceCell.element.widget || sourceCell.element.input,
            		allowedNestedRowWatchActions = this.allowedNestedRowWatchActions;
                this.nestedRowWatchActions = this.nestedRowWatchActions || 0;
                column.nestedWatchActions = column.nestedWatchActions || 0;
				when(sourceWidget, lang.hitch(this, function(sourceWidget){
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
				}));
            }
        },
        prepareInitRow: function(init){
            for (var col in this.initialRowValue){
                init[col] = this.initialRowValue[col];
            }
            return init;
        },

        offsetRowsId: function(fromRowId, increment){
            var self = this, newRows = {}, noRefresh = this.noRefreshOnUpdateDirty, collection = this.collection, idp = collection.idProperty;
            collection.forEach(function(object){
                if (object.rowId >= fromRowId){
                    newRows[object.rowId] = object.rowId + increment;
                }
            });
            this.noRefreshOnUpdateDirty = true;
            sutils.updateRowReferences(this, newRows);
            collection.forEach(function(object){
                if (object.rowId >= fromRowId){
                    //object.rowId +=increment;
                    //collection.putSync(object, {overwrite: true});//can be removed ?
                    self.updateDirty(object[idp], 'rowId', object.rowId + increment);
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
			const idp = this.collection.idProperty;
			eutils.actionFunction(this, 'createRow', this.createRowAction, 'row', item);
            if ('rowId' in this.columns && where !== undefined){
                if (where === 'before'){
                    //item.rowId = currentRowData.rowId;
                    this.offsetRowsId(item.rowId = currentRowData.rowId, 1);
                }else{
                    item.rowId = this.lastRowId() + 1;
                }
            }
            if (this.initialId || this.newRowPrefix){
                item.id = item.id || this.getNewId();
            }else{
                //delete item.id;
            }
            this.store.addSync(item, (where === 'before' ? {beforeId: currentRowData[idp]} : {}));
            if (this.onChangeNotify){
                this.notifyWidgets({action: 'add', item: item});
            }
           for (var j in item){
                if (j != idp && 'j' !== 'connectedIds'){
                    this.updateDirty(item[idp], j, item[j], true);
                }
            }
            return item;
        },
        addRow: function(where, item){
            var init={};
            this.prepareInitRow(init);
            item = utils.merge(init, item||{});
            return this.createNewRow(item, (where === 'before' ? this.clickedRow.data : {}), where);
        },
        updateRow: function(item){
        	var idp = this.collection.idProperty, idPropertyValue = item[idp], storeItem = this.collection.getSync(idPropertyValue) || {};
			eutils.actionFunction(this, 'updateRow', this.updateRowAction, 'row', item);
        	utils.forEach(item, lang.hitch(this, function(value, col){
        		if (value !== storeItem[col] && col !== 'connectedIds' && col !== idp){
        			this.updateDirty(idPropertyValue, col, value);
        		}
        	}));       		
        	if (this.onChangeNotify){
        		if (storeItem.connectedIds){
        			item.connectedIds = storeItem.connectedIds;
        		}
        		this.notifyWidgets({action: 'update', item: item});
        	}
        },
        copyItem: function(item){
            var newItem = lang.clone(item), noCopyCols = this.noCopyCols;
            for (var col in noCopyCols){
                delete(newItem[noCopyCols[col]]);
            }
            return newItem;
        },
        
        copyRow: function(evt){
			copyDialog(this).open({x: evt.clientX, y: evt.clientY, parent: this});
        },
        setSelectionCol: function(evt){
			setItemsColDialog(this).open({x: evt.clientX, y: evt.clientY, parent: this});
        },
        
        deleteRow: function(rowItem, skipDeleteAction, isUserRowEdit){
            this.deleteRowItem(rowItem || this.clickedRow.data, skipDeleteAction, isUserRowEdit);
            this.refresh({keepScrollPosition: true});
        },

        deleteRowItem: function(item, skipDeleteAction, isUserRowEdit){
			const idp = this.collection.idProperty;
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
            var idpToDelete = item[idp];
            this.deleteDirty(idpToDelete, isUserRowEdit);
            this.collection.removeSync(idpToDelete);
            if ('rowId' in this.columns){
                this.offsetRowsId(item.rowId, -1);
            }
			this.setSummary();
        },
        deleteRows: function(rows, skipDeleteAction, isUserRowEdit){
        	rows.forEach(lang.hitch(this, function(row){
        		this.deleteRowItem(row, skipDeleteAction, isUserRowEdit);
        	}));
			this.refresh({keepScrollPosition: true});
        },
        moveRow: function(itemToMove, currentRowData, where){
            const self = this, idp = this.collection.idProperty;
            if ('rowId' in this.columns){
                var noRefresh = this.noRefreshOnUpdateDirty;
            	this.noRefreshOnUpdateDirty = true;
				let lowRowId, highRowId, increment;
				const newItemToMoveRowId = currentRowData.rowId;		
				if (itemToMove.rowId > newItemToMoveRowId){
					lowRowId = newItemToMoveRowId - 1; highRowId = itemToMove.rowId; increment = 1;
				}else{
					lowRowId = itemToMove.rowId; highRowId = newItemToMoveRowId - 1; increment = -1;					
				}
				this.collection.forEach(function(object){
				    if (object.rowId > lowRowId && object.rowId < highRowId){
				        self.updateDirty(object[idp], 'rowId', object.rowId + increment);
				    }
				});
                this.noRefreshOnUpdateDirty = noRefresh;
				this.updateDirty(itemToMove[idp], 'rowId', newItemToMoveRowId);
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
                		dirtyToSend[col] = value === undefined ? '~delete' : value;
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
            return {/*data: this.store.fetchSync(), */dirty: this.dirty, deleted: this.deleted};
        },
        
        iterate: function(callback){
        	var idProperty = this.collection.idProperty;
        	this.collection.fetchSync().forEach(lang.hitch(this, function(item){
        		callback(lang.mixin(lang.clone(item), this.dirty[idProperty]));
        	}));
        },
        
        restoreChanges: function (changes){
            //this.set('value', changes.data);
            const self = this, idProperty = this.collection.idProperty;
            this.isBulkRowAction = true;
            utils.forEach(changes.dirty, function(row, idPropertyValue){
				row[idProperty] = idPropertyValue;
				if (self.collection.getSync(idPropertyValue)){
					self.updateRow(row);
				}else{
					self.createNewRow(row);
				}
			});
			this.isBulkRowAction = false;
			changes.deleted.forEach(function(item){
				self.deleteRowItem(self.store.filter({id: item.id}).fetchSync()[0], false, true);
			});
            if(!utils.empty(this.dirty) || this.deleted.length > 0){
                 wutils.markAsChanged(this, 'noStyle');
            }
            this.refresh({keepScrollPosition: true});
            return true;
        },
        _setValue: function(value){
        	const self = this, idp = this.store.idProperty;
        	var noRefresh = this.noRefreshOnUpdateDirty, resetCounters = true;
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
                                    if (i != idp && i !== 'updator' && i !== 'updated' && i !== 'canEdit' && i !== 'connectedIds'){// warging: there may be other read-only fields to exclude from dirty here
                                        this.updateDirty(row[idp], i, row[i]);
                                    }
                                }
                            }
                        }));
                    }          		
            	}
            }else{//current memory store needs to be updated with contents of current object, then saved (to empty dirty)
                this.store.setData([]);
                this.dirty = {};
            	for (var row in value){
                	this.store.addSync(value[row]);
                }
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
            const idp = this.collection.idProperty;
            var data = this.collection.fetchSync({}), noRefresh = this.noRefreshOnUpdateDirty;
            this.noRefreshOnUpdateDirty = true;
            data.forEach(function(item){
                item['id'] = null;
                for (var col in item){
                    if (col != 'canEdit' && col != idp && col != 'updator' && col != 'updated'){
                        this.updateDirty(item[idProperty], col, item[col]);
                    }
                }
                this.collection.putSync(utils.newObj([[idp, item[idp]]]));
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
        notifyWidgets: function(args){
            const idp = this.collection.idProperty;
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
                                lang.setObject('connectedIds.' + self.widgetName, item[idp], item);
                                widget.set('notify', {action: 'create', item: item, sourceWidget: self});
                            });
                        }else if(action === 'add'){
                            var item = args.item;
                            if (this.matchesFilter(item, filter)){
                            	lang.setObject('connectedIds.' + this.widgetName, item[idp], item);
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
							default: gridItem[targetCol] = value;
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
});
