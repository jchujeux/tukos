define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/on", "dojo/ready", "dgrid/OnDemandGrid", "dgrid/Keyboard", "dgrid/Selector", "dgrid/extensions/DijitRegistry", "tukos/dgrid/extensions/ColumnHider", "tukos/dgrid/extensions/ColumnResizer",
		"tukos/_WidgetsExtend", "tukos/_GridSummaryMixin", "tukos/utils", "tukos/evalutils", "tukos/menuUtils", "tukos/sheetUtils", "tukos/widgetUtils", "tukos/widgets/widgetCustomUtils", "tukos/PageManager"], 
function(declare, lang, dct, dst, on, ready, Grid, Keyboard, Selector, DijitRegistry, Hider, Resizer, _WidgetsExtend, _GridSummaryMixin, utils, eutils, mutils, sutils, wutils,  wcutils, Pmg){
	lang.extend (Grid, _WidgetsExtend);    
	return declare([Grid, DijitRegistry, Keyboard, Hider, Resizer, Selector, _GridSummaryMixin], {
        constructor: function(args){
            var self = this;
			this.adjustLastColumn = false;
        	for (var i in args.columns){
                this.setColArgsFunctions(args.columns[i]);
            }
            this.contextMenuItems = Pmg.isRestrictedUser() 
            ? {row: [], idCol: [{atts: {label: Pmg.message('editinnewtab'), onClick: function(evt){self.editInNewTab(self)}}}], header: []}
            : {
                    row: [
                        {atts: {label: Pmg.message('togglerowheight'), onClick: lang.hitch(this, function(evt){this.toggleFormatterRowHeight(this);})}}, 
                        {atts: {label: Pmg.message('viewcellindialog'), onClick: lang.hitch(this, function(evt){this.viewCellInPopUpDialog(this);})}},
                        {atts: {label: Pmg.message('viewcellinwindow'), onClick: lang.hitch(this, function(evt){this.viewInSeparateBrowserWindow(this);})}}
                     ],
                     idCol: [{atts: {label: Pmg.message('editinnewtab'), onClick: function(evt){self.editInNewTab(self)}}}].concat(
                    		 [{atts: {label: Pmg.message('togglerowheight'), onClick: lang.hitch(this, function(evt){this.toggleFormatterRowHeight(this);})}}]),
                     header: [
                        {atts: {label: Pmg.message('exporttocsv'), onClick: lang.hitch(this, function(evt){this.exportToCsv(this);})}}
					]
            };
        },
        setColArgsFunctions: function(colArgs){
            ['formatter', 'get', 'renderCell', 'canEdit', 'renderHeaderCell'].forEach(
                function(col, index, array){
                    if (colArgs[col]){
                        colArgs[col] = (typeof colArgs[col] === 'string' && typeof this[colArgs[col]] === 'function') ? this[colArgs[col]] : eutils.eval(colArgs[col]);
                    }
                },
                this
            );
        },
        postCreate: function(){
            var self = this, form = this.form;
        	this.inherited(arguments);
        	this.minHeightFormatter = this.minHeightFormatter || '5em';
        	this.maxHeightFormatter = this.maxHeightFormatter || '30em';
        	if (!this.collection){
        		require(["dstore/Memory"], function(Memory){
        			self.collection = new Memory({idProperty: self.idProperty || 'id', data: []});
        		});
        	}
            ['maxHeight', 'maxWidth', 'minWidth', 'width'].forEach(function(att){
				if (self[att]){
					self.set(att, self[att]);
				}	
            });
            this.formulaCache = {};
            var copyCellCallback = function(evt){
                if (evt.ctrlKey){
                    Pmg.setCopiedCell(sutils.copyCell(this.clickedCell));
                }
            };
            this.addKeyHandler(67, copyCellCallback);
            if (this.renderCallback){
            	this.renderCallbackFunction = eutils.eval(this.renderCallback, "node, rowData, column, tdCell")
            }
            if (!this.hasOwnProperty('noDataMessage')){
            	this.noDataMessage =  Pmg.isMobile() ? Pmg.message('noDataMessageMobile') : Pmg.message('noDataMessage');
			}
            this.keepScrollPosition = true;
        	if (!this.itemCustomization){
        		this.customizationPath = 'customization.widgetsDescription.' + (form.attachedWidget ? form.attachedWidget.widgetName + '.atts.dialogDescription.paneDescription.widgetsDescription.' : '') + this.widgetName + '.atts.';
        	}
        	this.on("dgrid-cellfocusin", lang.hitch(this, function(evt){
                this.clickedRow = this.row(evt);
            	this.clickedCell = this.cell(evt);
            }));
            this.on(".dgrid-cell:click", lang.hitch(this, function(evt){
            	eutils.actionFunction(this, 'onCellClick', this.onCellClickAction, 'grid', this);
			}));
            this.on("dgrid-columnstatechange", function(evt){
                var grid = evt.grid;
                if (grid.customizationPath){
                    lang.setObject(grid.customizationPath + 'columns.' + evt.column.field + '.hidden', evt.hidden, grid.getRootForm());
                }
            });
            this.on("dgrid-columnresize", function(evt){
                var grid = evt.grid;
                if (evt.width != 'auto' && grid.customizationPath){
                	lang.setObject(grid.customizationPath + 'columns.' + grid.columns[evt.columnId].field + '.width', evt.width, grid.getRootForm());
                }
            });
            this.on("dgrid-sort", function(evt){
                var grid = evt.grid;
                if (grid.customizationPath){
                    lang.setObject(grid.customizationPath + 'sort', evt.sort, grid.getRootForm());               	
                }
            });
            this.rowHeights = {};
            if (this.dynamicColumns){//to ensure columns are not built on instantiation
            	this.set('columns', {});
            }
            this.on(on.selector(".dgrid-row, .dgrid-header", "contextmenu"), lang.hitch(this, this.contextMenuCallback));
			this.isFirstResize = true;
        },
		resize: function(){
			if (!this.layoutHandle && this.isFirstResize){
				this.setInitialColsWidth();
				this.isFirstResize = false;
			}
			this.inherited(arguments);
		},
		_setAllowApplicationFilter: function(newValue){
        	wutils.watchCallback(this, 'allowApplicationFilter', this.allowApplicationFilter, newValue);
        	this.allowApplicationFilter = newValue;
        },
        _setCollection: function(newValue){
			var self = this;        	
			this.inherited(arguments);
			ready(function(){
				wutils.watchCallback(self, 'collection', null, newValue);
			});	
        },
    	_setMaxHeight: function(value){
            this.bodyNode.style.maxHeight = value;
        },
    	_setMaxWidth: function(value){
            this.bodyNode.style.maxWidth = value;
        },
    	_setMinHeight: function(value){
            this.bodyNode.style.mainHeight = value;
        },
    	_setMinWidth: function(value){
            this.bodyNode.style.minWidth = value;
        },
        canEditRow: function(object){
            return !this.grid.disabled && ((typeof object.canEdit === "undefined") || object.canEdit);
        }, 
        formatId: function(id){
        	var newRowPrefix = this.grid.newRowPrefix;
        	if (newRowPrefix && id.indexOf(newRowPrefix) === 0){
        		return Pmg.message(newRowPrefix) + ' ' + id.substring(newRowPrefix.length);
        	}else{
        		return id || '';
        	}
        },
        formatContent: function(value, object){
        	return this.formatType ? utils.transform(value, this.formatType, this.formatOptions, Pmg) : value;
        },
        renderNamedId: function(object, value, node){
            var grid = this.grid, newRowPrefixGridName = utils.drillDown(this, ['editorArgs', 'storeArgs', 'storeDgrid']);
        	return this.grid._renderContent(this, object, node, newRowPrefixGridName ? grid.form.getWidget(newRowPrefixGridName).newRowPrefixNamedId(value) : Pmg.namedId(value));
        },
        renderNamedIdExtra: function(object, value, node){
            return this.grid._renderContent(this, object, Pmg.namedIdExtra(value));
        },
        renderNameExtra: function(object, value, node){
            return this.grid._renderContent(this, object, Pmg.namedExtra(value));
        },
		_storeDisplayedValue: function(value, column){
			//return value ? utils.findReplace(column.editorArgs.storeArgs.data, 'id', value === '0' ? '' : value, 'name', column.storeCache || (column.storeCache = {})) : value;
			return value !== undefined ? utils.findReplace(column.editorArgs.storeArgs.data, 'id', value, 'name', column.storeCache || (column.storeCache = {})) : value;
		},
        renderStoreValue: function(object, value, node){
			var self = this, grid = this.grid;            
			return grid._renderContent(this, object, node, grid._storeDisplayedValue(value, this));
        },
		_numberUnitDisplayedValue: function(value, column){
            if (value){
				var values = JSON.parse(value), count = values[0],
            		unitValue = values[1] ? utils.findReplace(column.editorArgs.unit.storeArgs.data, 'id', values[1], 'name', column.storeCache || (column.storeCache = {})) : values[1],
                	transformedValue = count + ' ' + unitValue + (count > 1 && column.formatType === 'numberunit' ? 's' : '');
			}
			return transformedValue || value;
		},
        renderNumberUnitValue: function(object, value, node){
            var grid = this.grid;
			return grid._renderContent(this, object, node, grid._numberUnitDisplayedValue(value, this));
        },
        renderCheckBox: function(object, value, node){
        	return this.grid._renderContent(this, object, node, value ? '☑' : '☐', {textAlign: 'center'});
        },        
        renderColorPicker: function(object, value, node){
        	return this.grid._renderContent(this, object, node, '', {backgroundColor: value});
        },
        renderGauge: function(object, value, node){
			const pValue = value ? JSON.parse(value) : value;
			return this.grid._renderContent(this, object, node, (pValue && typeof pValue === 'object') ? (pValue.gauge ? pValue.gauge.toString() : '') : (pValue ? pValue.toString() : ''));
		},
        renderContent: function(object, value, node, options, noCreate){
            var grid = this.grid, row = grid.row(object), idp = grid.collection.idProperty, args = {object: object, value: sutils.evalFormula(grid, value, this.field, row.data[idp])}, result;
            eutils.actionFunction(this, 'renderContent', this.renderContentAction, 'args', args);
            result = utils.transform(args.value, this.formatType, this.formatOptions, Pmg);
            return grid._renderContent(this, object, node, result, utils.in_array(this.formatType, ['currency', 'percent']) ? {textAlign: 'right'} : {}, noCreate);
        },
        _renderContent: function(column, storeRow, tdCell, innerHTML, styleAtts, noCreate){
            var row =this.row(storeRow), rowHeight = (this.rowHeights[row.id] ? this.rowHeights[row.id] : this.minHeightFormatter), atts = {style: lang.mixin({maxHeight: rowHeight, overflow: 'auto'}, styleAtts)},
            	rowId =  storeRow[this.collection.idProperty], node;
            if (!column.noMarkAsChanged &&  (this.dirty[rowId] && typeof this.dirty[rowId][column.field] !== 'undefined' && !atts.style.backgroundColor)){
                atts.style.backgroundColor =  wutils.changeColor;
            }
            if(! innerHTML || ! /\S/.test(innerHTML) || innerHTML === '~delete'){
                innerHTML = '<p> ';
            }
            if (noCreate){
            	node = tdCell;
            	node.innerHTML = innerHTML;
            	dst.set(node, atts.style);
           }else{
                atts.innerHTML= innerHTML;
                node = dct.create('div', atts);
            }
            if(typeof innerHTML === "string" && innerHTML.substring(0, 7) === '#tukos{'){
            	dst.set(node, {textDecoration: "underline", color: "blue", cursor: "pointer"});
            	node.innerHTML = Pmg.message('loadOnClick');
            	node.onClickHandler = on(node, 'click', lang.hitch(this, this.loadContentOnClick));
            }else{
            	node.innerHTML = innerHTML;
            	dst.set(node, atts.style);
            }
            if (this.renderCallbackFunction){
            	this.renderCallbackFunction(node, row.data, column, tdCell);
            }
            return node;
        },
        renderHeaderContent: function(node){
			node.innerHTML = this.label;
		},
        colDisplayedValue: function(value, colName){
        	var column = this.columns[colName];
        	switch (column.widgetType){
        		case 'StoreSelect': 
        			return this._storeDisplayedValue(value, column);
				case 'NumberUnitBox':    
					return this._numberUnitDisplayedValue(value, column);    		
				case 'ObjectSelect':
        		case 'ObjectSelectMulti':
        		case 'ObjectSelectDropDown':
        			return Pmg.namedId(value);
        		default:
        			return utils.transform(value, column.formatType, column.formatOptions, Pmg);
        	}
        },
        colDisplayedTitle: function(colName){
        	return this.columns[colName].title;
        },
        loadContentOnClick: function(evt){
        	var clickedCell = this.clickedCell, field = clickedCell.column.field, data = this.collection.getSync(this.clickedRowIdPropertyValue()), source = RegExp("#tukos{id:([^,]*),object:([^,]*),col:([^}]*)}", "g").exec(data[field]), targetCol = source[3],
        		node = evt.currentTarget;
			evt.stopPropagation();
			evt.preventDefault();
			node.onClickHandler.remove();
			dst.set(node, {textDecoration: "", color: "", cursor: ""});
			Pmg.serverDialog({object: source[2], view: 'NoView', mode: 'NoMode', action: 'RestSelect', query: {one: true, params: {getOne: 'getOne'}, storeatts: {cols: [targetCol], where: {id: source[1]}}}}).then(
					lang.hitch(this, function(response){
						node.innerHTML = data[field] = response.item[targetCol];	
        	}));
        },
        exportToCsv: function(grid){
			grid.collection.sort(grid.sort).fetch().then(function(rows){
				var element = document.createElement('a'), csvContent, activeColumns = [], headerContent = '';
				utils.forEach(grid.columns, function(column, col){
					if (!column.hidden){
						activeColumns.push(col);
						headerContent += '"' + column.label.replaceAll('"', '""') +  '"' + ",";							
					}
				});
				csvContent = headerContent.slice(0, -1);
				rows.forEach(function(rowObject){
					var rowContent = "";
					csvContent += "\r\n";
					activeColumns.forEach(function (col){
						//rowContent += '"' + (rowObject[col] ? (typeof rowObject[col] === 'string' ? rowObject[col].replaceAll('"', '""') : rowObject[col]) : '') +  '"' + ",";
						switch(typeof rowObject[col]){
							case 'undefined': rowContent += ''; break;
							case 'string'   : rowContent += rowObject[col].replaceAll('"', '""'); break;
							default: rowContent += rowObject[col];
						}
						rowContent += ',';
					});
					csvContent += rowContent.slice(0, -1);
				});
				element.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvContent));
				element.setAttribute('download', "export.csv");
				element.style.display = 'none';
				document.body.appendChild(element);
				element.click();
		  		document.body.removeChild(element);
			});
		},
		toggleFormatterRowHeight: function(grid){
            var row = grid.clickedCell.row,
                column = grid.clickedCell.column;
            grid.rowHeights[row.id] = (grid.rowHeights[row.id] == grid.maxHeightFormatter ? grid.minHeightFormatter : grid.maxHeightFormatter); 
            grid.refresh({keepScrollPosition: true});
        },
        viewCellInPopUpDialog: function(grid){
            if (!grid.viewCellDialog){
            	require (['dijit/Dialog'], function(Dialog){
            		grid.viewCellDialog = new Dialog({title: Pmg.message('Extendedview'), style: {color: 'black'}});
            		grid.viewCellDialog.set('content', grid.clickedRowValues()[grid.clickedCell.column.field]);
            		grid.viewCellDialog.show();
            	});
            }else{
                grid.viewCellDialog.set("content", grid.clickedRowValues()[grid.clickedCell.column.field]);
                grid.viewCellDialog.show();
            }
        },
        viewInSeparateBrowserWindow: function(grid){
            utils.viewInBrowserWindow(grid.clickedCell.column.field+grid.clickedCell.row.id, grid.clickedRowValues()[grid.clickedCell.column.field]);
        },
        editInNewTab: function(grid){
            var field  = grid.clickedCell.column.field,
                query = {};
            if (grid.clickedCell.column.onClickFilter){
                var object  = grid.object === 'tukos' ? grid.cellValueOf('object') : grid.object,
                    fields = grid.clickedCell.column.onClickFilter;
                for (var i in fields){
                    var field = fields[i];
                    var value = grid.cellValueOf(field);
                    if (value){
                        query[field] = value;
                    }
                }
            }else{
                var id = grid.cellValueOf(Pmg.isRestrictedUser() ? 'id' : field);
                if (id){
                    object = Pmg.objectName(id, grid.form.objectDomain);
                    query.id = id;
                }
            }
            if (!utils.empty(query)){
                Pmg.tabs.gotoTab({object: object, view: 'Edit', mode: 'Tab', action: 'Tab', query: query});
            }else{
				Pmg.setFeedback(Pmg.message('needtosavebeforeeditinnewtab'), null, null, true);
			}
        },
        showInNavigator: function(grid){
        	var targetId = grid.cellValueOf(grid.clickedCell.column.field);
        	if (targetId){
        		Pmg.showInNavigator(targetId);
        	}
        },
        clickedRowIdPropertyValue: function(){
        	return this.clickedRow.data[this.collection.idProperty]        	
        }, 
        rowValues: function(idPropertyValue){
            var result = this.collection.getSync(idPropertyValue) || this.emptyRowItem(idPropertyValue);
            return this.dirty ? lang.mixin(lang.clone(result), this.dirty[idPropertyValue]) : result;
        },
        clickedRowValues: function(){
        	var idPropertyValue = this.clickedRowIdPropertyValue();
        	if (this.dirty && this.dirty[idPropertyValue]){
            	return this.rowValues(idPropertyValue);
        	}else{
        		return this.clickedRow.data;
        	}
        },
        contextMenuCallback: function(evt){
        	evt.preventDefault();
			evt.stopPropagation();
			const row = (this.clickedRow = this.row(evt)), cell = this.clickedCell = this.cell(evt), column = cell.column;
			if (Pmg.isRestrictedUser()){
	            mutils.setContextMenuItems(this, row ? (!row.data.id ||  (row.data.updator && row.data.updator === Pmg.get('userid')) ? this.contextMenuItems.canEdit || this.contextMenuItems.idCol : this.contextMenuItems.noEdit || this.contextMenuItems.idCol) : this.contextMenuItems.header);
			}else{
				let menuItems = lang.clone(this.contextMenuItems), colItems = row ? (column.onClickFilter || utils.in_array(column.field, this.objectIdCols) ? 'idCol' : 'row') : 'header';
				this.clickedColumn = this.column(evt);
				if (colItems !== 'header' && menuItems.canEdit && row.data.canEdit !== false){
	            	menuItems[colItems] = menuItems[colItems].concat(menuItems.canEdit);
	            }
	            let menuColItems = menuItems[colItems];
				if (this.customContextMenuItems){
					menuColItems = (menuColItems || []).concat(this.customContextMenuItems());
				}                
	            if (menuColItems){
	            	const widgetToCustomize = this.customizableAttsWidget || this;
	            	mutils.setContextMenuItems(this, menuColItems ? menuColItems.concat(lang.hitch(wcutils, wcutils.customizationContextMenuItems)(widgetToCustomize)) : lang.hitch(wcutils, wcutils.customizationContextMenuItems)(widgetToCustomize));
				}
			}
        },
        cellValueOf: function(field, idPropertyValue){
			var result;            
			if (idPropertyValue){
                if (this.collection.getSync){
                    return this.collection.getSync(idPropertyValue)[field];
                }else{
					console.log("BasicGrid.cellValueOf - unsupported Rest store");
                }
            }else{
                var result = this.clickedRowValues()[field];
                return (typeof result === "undefined" || result === null) ? '' : result;
            }
        },
		cellDisplayedValueOf: function(field, idPropertyValue){
			return this.colDisplayedValue(this.cellValueOf(field, idPropertyValue), field);
		},
        selectRow: function(rowIdProperty){
            var row = this.row(rowIdProperty);
           if (row){
                //var reorderedIndex = arrayUtil.indexOf(this.store.sort(this.get('sort')).fetchSync(), this.store.getSync(rowIdProperty));
                var reorderedIndex = this.store.sort(this.get('sort')).fetchSync().indexOf(this.store.getSync(rowIdProperty));
        
                this.bodyNode.scrollTop = this.rowHeight*reorderedIndex;
                this.refresh();
            }
        },
        colId: function(field){
            var orderedCols = this.subRows[0];
            for (var  i in orderedCols){
                if (orderedCols[i].field === field){
                    return i;
                }
            }
            return -1;
        },
        colField: function(colId){
            return this.subRows[0][colId].field;
        },
        _setValue: function(value){
        	this.store.setData(value ? value : [])
        	this.set('collection');
        	this.setSummary();
        },
        _setColumns: function(columns){
        	var staticColsProperties = this.params.columns;
        	if (this.dynamicColumns){
        		for (var col in columns){
        			var column = columns[col];
        			if (staticColsProperties){
            			column = lang.mixin(column, staticColsProperties[col]);
        			}
       				this.setColArgsFunctions(column);
        		}
        	}
    		this.inherited(arguments);
        }
    });
});
