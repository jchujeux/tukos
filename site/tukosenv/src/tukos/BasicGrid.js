define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/dom-style", "dojo/on", "dgrid/OnDemandGrid", "dgrid/Selector", "dgrid/extensions/DijitRegistry", "dgrid/extensions/ColumnHider", "dgrid/extensions/ColumnResizer",
	"tukos/utils", "tukos/evalutils", "tukos/menuUtils", "tukos/widgets/widgetCustomUtils", "tukos/PageManager"], 
function(declare, lang, dct, dst, on, Grid, Selector, DijitRegistry, Hider, Resizer, utils, eutils, mutils, wcutils, Pmg){
    return declare([Grid, DijitRegistry, Hider, Resizer, Selector], {
        constructor: function(args){
            var self = this;
        	for (var i in args.columns){
                this.setColArgsFunctions(args.columns[i]);
            }
            this.contextMenuItems = {
                    row: [
                        {atts: {label: Pmg.message('togglerowheight'), onClick: lang.hitch(this, function(evt){this.toggleFormatterRowHeight(this);})}}, 
                        {atts: {label: Pmg.message('viewcellindialog'), onClick: lang.hitch(this, function(evt){this.viewCellInPopUpDialog(this);})}},
                        {atts: {label: Pmg.message('viewcellinwindow'), onClick: lang.hitch(this, function(evt){this.viewInSeparateBrowserWindow(this);})}}
                     ],
                     idCol: [{atts: {label: Pmg.message('editinnewtab'), onClick: function(evt){self.editInNewTab()}}}].concat(
                    		 [{atts: {label: Pmg.message('togglerowheight'), onClick: lang.hitch(this, function(evt){this.toggleFormatterRowHeight(this);})}}]),
                     header: []
            };
        },
        setColArgsFunctions: function(colArgs){
            ['formatter', 'get', 'renderCell', 'canEdit', 'renderHeaderCell'].forEach(
                function(col, index, array){
                    if (colArgs[col]){
                        colArgs[col] = (typeof this[colArgs[col]] === 'function') ? this[colArgs[col]] : eutils.eval(colArgs[col]);
                    }
                },
                this
            );
        },
        postCreate: function(){
            var self = this, form = this.form;
        	this.inherited(arguments);
        	if (!this.collection){
        		require(["dstore/Memory"], function(Memory){
        			self.collection = new Memory({data: []});
        		});
        	}
            ['maxHeight', 'maxWidth', 'minWidth', 'width'].forEach(function(att){
            	self.set(att, self[att]);
            });
            if (this.renderCallback){
            	this.renderCallBackFunction = eutils.eval(this.renderCallback, "node, rowData")
            }
            this.keepScrollPosition = true;
        	if (!this.itemCustomization){
        		this.customizationPath = 'customization.widgetsDescription.' + (form.attachedWidget ? form.attachedWidget.widgetName + '.atts.dialogDescription.paneDescription.widgetsDescription.' : '') + this.widgetName + '.atts.';
        	}
        	this.on("dgrid-cellfocusin", lang.hitch(this, function(evt){
                this.clickedRow = this.row(evt);
            	this.clickedCell = this.cell(evt);
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
        },
    	_setMaxHeight: function(value){
            this.bodyNode.style.maxHeight = value;
        },
        canEditRow: function(object){
            return !this.grid.disabled && object.canEdit;
        }, 
        formatContent: function(value, object){
        	return this.formatType ? utils.transform(value, this.formatType, this.formatOptions, Pmg) : value;
        },
        renderNamedId: function(object, value, node){
            return this.grid._renderContent(this, object, Pmg.namedId(value));
        },
        renderNamedIdExtra: function(object, value, node){
            return this.grid._renderContent(this, object, Pmg.namedIdExtra(value));
        },
        renderNameExtra: function(object, value, node){
            return this.grid._renderContent(this, object, Pmg.namedExtra(value));
        },
        renderStoreValue: function(object, value, node){
            return this.grid._renderContent(this, object, value ? utils.findReplace(this.editorArgs.storeArgs.data, 'id', value, 'name', this.storeCache || (this.storeCache = {})) : value);
        },
        renderCheckBox: function(object, value, node){
        	return this.grid._renderContent(this, object, value ? '☑' : '☐', {textAlign: 'center'});
        },        
        renderColorPicker: function(object, value, node){
        	return this.grid._renderContent(this, object, '', {backgroundColor: value});
        },
        renderContent: function(object, value, node){
            var grid = this.grid, row = grid.row(object), 
            	result = utils.transform((value === undefined || value === null) ? '' : grid.evalFormula ? grid.evalFormula(grid, value, this.field, row.data[grid.collection.idProperty]) : value, this.formatType, this.formatOptions, Pmg);
            return grid._renderContent(this, object, result, utils.in_array(this.formatType, ['currency', 'percent']) ? {textAlign: 'right'} : {});
        },
        _renderContent: function(column, object, innerHTML, styleAtts){
            var grid = column.grid, row =this.row(object), rowHeight = ((this.rowHeights || {})[row.id] ? this.rowHeights[row.id] : column.minHeightFormatter), atts = {style: lang.mixin({maxHeight: rowHeight, overflow: 'auto'}, styleAtts)},
            	rowId =  object[this.collection.idProperty], node;
            if (this.dirty[rowId] && this.dirty[rowId][column.field] !== undefined && !atts.style.backgroundColor && grid.changeColor){
                atts.style.backgroundColor =  grid.changeColor;
            }
            if(! innerHTML || ! /\S/.test(innerHTML) || innerHTML === '~delete'){
                innerHTML = '<p> ';
            }
            atts.innerHTML= innerHTML;
            node = dct.create('div', atts);
            if(typeof innerHTML === "string" && innerHTML.substring(0, 7) === '#tukos{'){
            	dst.set(node, {textDecoration: "underline", color: "blue", cursor: "pointer"});
            	node.innerHTML = Pmg.message('loadOnClick');
            	node.onClickHandler = on(node, 'click', lang.hitch(this, this.loadContentOnClick));
            }
            if (this.renderCallbackFunction){
            	this.renderCallbackFunction(node, row.data);
            }
            return node;
        },
        colDisplayedValue: function(value, colName){
        	var column = this.columns[colName];
        	switch (column.widgetType){
        		case 'StoreSelect': 
        			return value ? utils.findReplace(column.editorArgs.storeArgs.data, 'id', value, 'name', this.storeCache || (this.storeCache = {})) : value;
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
        	var clickedCell = this.clickedCell, field = clickedCell.column.field, data = clickedCell.row.data, source = RegExp("#tukos{id:([^,]*),object:([^,]*),col:([^}]*)}", "g").exec(data[field]), targetCol = source[3],
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
        toggleFormatterRowHeight: function(grid){
            var row = grid.clickedCell.row,
                column = grid.clickedCell.column;
            grid.rowHeights[row.id] = (grid.rowHeights[row.id] == column.maxHeightFormatter ? column.minHeightFormatter : column.maxHeightFormatter); 
            grid.refresh({keepScrollPosition: true});
        },
        viewCellInPopUpDialog: function(grid){
            if (!grid.viewCellDialog){
            	require (['dijit/Dialog'], function(Dialog){
            		grid.viewCellDialog = new Dialog({style: {color: 'black'}});
            		grid.viewCellDialog.set('content', grid.clickedRowValues()[grid.clickedCell.column.field]);
            		grid.viewCellDialog.show();
            	});
            }else{
                grid.viewCellDialog.set("content", grid.clickedRowValues()[grid.clickedCell.column.field]);
                grid.viewCellDialog.show();
            }
        },
        viewInSeparateBrowserWindow: function(grid){
            var newWindow = window.open('', grid.clickedCell.column.field+grid.clickedCell.row.id, 'toolbar=no,location=no,status=no,menubar=no,directories=no,copyhistory=no, scrollbars=yes');
            newWindow.document.write(grid.clickedRowValues()[grid.clickedCell.column.field]);
            newWindow.document.close();
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
        _setValue: function(value){
        	this.collection.setData(value ? value : [])
        	this.set('collection', this.collection);
        },
        _setColumns: function(columns){
        	var staticColsProperties = this.params.columns;
        	if (this.dynamicColumns){
        		for (var col in columns){
        			if (staticColsProperties){
            			columns[col] = lang.mixin(columns[col], staticColsProperties[col]);
        			}
        			this.setColArgsFunctions(columns[col]);
        		}
        	}
    		this.inherited(arguments);
        },
        contextMenuCallback: function(evt){
            console.log('contextmenucallback');
        	var row = (this.clickedRow = this.row(evt)), cell = this.clickedCell = this.cell(evt), column = cell.column, clickedColumn = this.clickedColumn = this.column(evt);
                var menuItems = lang.clone(this.contextMenuItems);
                var colItems = row ? (column.onClickFilter || utils.in_array(column.field, this.objectIdCols) ? 'idCol' : 'row') : 'header';
                if (colItems !== 'header' && menuItems.canEdit && row.data.canEdit !== false){
                	menuItems[colItems] = menuItems[colItems].concat(menuItems.canEdit);
                }
                mutils.setContextMenuItems(this, menuItems[colItems].concat(lang.hitch(wcutils, wcutils.customizationContextMenuItems)(this)));
        },
        editInNewTab: function(){
            var grid = this, field  = grid.clickedCell.column.field, id = grid.clickedRowValues()[grid.clickedCell.column.field];
            if (id){
                Pmg.tabs.gotoTab({object: Pmg.objectName(id, grid.form.objectDomain), view: 'Edit', mode: 'Tab', action: 'Tab', query: {id: id}});
            }
            if (!utils.empty(query)){
            }
        }
    });
});
