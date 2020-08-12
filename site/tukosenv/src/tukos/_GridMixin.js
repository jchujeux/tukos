/*
 *  tukos grids  mixin for dynamic widget information handling and cell rendering (widgets values and attributes that may be modified by the user or the server)
 *   - usage: 
 */
define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/dom-construct", "dojo/dom-style", "dijit/registry", "dijit/Dialog", "tukos/utils", "tukos/evalutils", "tukos/widgetUtils", "tukos/menuUtils",
         "tukos/widgets/widgetCustomUtils", "tukos/sheetUtils", "tukos/widgets/ColorPicker", "tukos/PageManager", "dojo/number",  "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(arrayUtil, declare, lang, on, dct, dst, registry, Dialog, utils, eutils, wutils, mutils, wcutils, sutils, ColorPicker, Pmg, number, messages){
    return declare(null, {

        constructor: function(){
            this.contextMenuItems = {
                row: [
                    {atts: {label: messages.togglerowheight, onClick: lang.hitch(this, function(evt){this.toggleFormatterRowHeight(this);})}}, 
                    {atts: {label: messages.viewcellindialog, onClick: lang.hitch(this, function(evt){this.viewCellInPopUpDialog(this);})}},
                    {atts: {label: messages.viewcellinwindow, onClick: lang.hitch(this, function(evt){this.viewInSeparateBrowserWindow(this);})}}
                 ],
                 idCol: lang.hitch(this, wcutils.idColsContextMenuItems)(this).concat([{atts: {label: messages.togglerowheight, onClick: lang.hitch(this, function(evt){this.toggleFormatterRowHeight(this);})}}]),
                 header: []
            };
        },
        canEditRow: function(object){
            return !this.grid.disabled && ((typeof object.canEdit === "undefined") || object.canEdit);
        }, 
        formatId: function(id, object){
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
            return this.grid._renderContent(this, object, node, Pmg.namedIdExtra(value));
        },
        renderNameExtra: function(object, value, node){
            return this.grid._renderContent(this, object, node, Pmg.namedExtra(value));
        },
        renderStoreValue: function(object, value, node){
            return this.grid._renderContent(this, object, node, value ? utils.findReplace(this.editorArgs.storeArgs.data, 'id', value, 'name', this.storeCache || (this.storeCache = {})) : value);
        },
        renderCheckBox: function(object, value, node){
        	return this.grid._renderContent(this, object, node, value ? '☑' : '☐', {textAlign: 'center'});
        },
        renderColorPicker: function(object, value, node){
        	return this.grid._renderContent(this, object, node, '', {backgroundColor: value});
        },
        renderContent: function(object, value, node, options, noCreate){
            var grid = this.grid, row = grid.row(object), args = {object: object, value: ((value === undefined || value === null) ? "" : sutils.evalFormula(grid, value, this.field, row.data.idg))}, result;
            eutils.actionFunction(this, 'renderContent', this.renderContentAction, 'args', args);
            result = utils.transform(args.value, this.formatType, this.formatOptions, Pmg);
            return grid._renderContent(this, object, node, result, utils.in_array(this.formatType, ['currency', 'percent']) ? {textAlign: 'right'} : {}, noCreate);
        },
        
        _renderContent: function(column, storeRow, node, innerHTML, styleAtts, noCreate){
            var row =this.row(storeRow), rowHeight = (this.rowHeights[row.id] ? this.rowHeights[row.id] : column.minHeightFormatter), atts = {style: lang.mixin({maxHeight: rowHeight, overflow: 'auto'}, styleAtts)},
            	rowId =  storeRow[this.collection.idProperty], node;
            if (this.dirty[rowId] && typeof this.dirty[rowId][column.field] !== 'undefined' && !atts.style.backgroundColor){
                atts.style.backgroundColor =  wutils.changeColor;
            }
            if(! innerHTML || ! /\S/.test(innerHTML) || innerHTML === '~delete'){
                innerHTML = '<p> ';
            }
            if (noCreate){
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
			Pmg.serverDialog({object: source[2], view: 'NoView', mode: 'NoMode', action: 'RestSelect', query: {one: true, params: {getOne: 'getOne'}, storeatts: {cols: [targetCol], where: {id: source[1]}}}}).then(lang.hitch(this, function(response){
        		node.innerHTML = data[field] = response.item[targetCol];	
        	}));
        	console.log('here implement load on click');
        	
        },

        toggleFormatterRowHeight: function(grid){
            var row = grid.clickedCell.row,
                column = grid.clickedCell.column;
            grid.rowHeights[row.id] = (grid.rowHeights[row.id] == column.maxHeightFormatter ? column.minHeightFormatter : column.maxHeightFormatter); 
            grid.refresh({keepScrollPosition: true});
        },
        viewCellInPopUpDialog: function(grid){
            var myDialog = new Dialog({title: "extended view"});
            //myDialog.set("content", grid.clickedCell.row.data[grid.clickedCell.column.field]);
            myDialog.set("content", grid.clickedRowValues()[grid.clickedCell.column.field]);
            myDialog.show();
        },
        viewInSeparateBrowserWindow: function(grid){
            var newWindow = window.open('', grid.clickedCell.column.field+grid.clickedCell.row.id, 'toolbar=no,location=no,status=no,menubar=no,directories=no,copyhistory=no, scrollbars=yes');
            //newWindow.document.write(grid.clickedCell.row.data[grid.clickedCell.column.field]);
            newWindow.document.write(grid.clickedRowValues()[grid.clickedCell.column.field]);
            newWindow.document.close();
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
                var id = grid.cellValueOf(field);
                if (id){
                    object = Pmg.objectName(id, grid.form.objectDomain);
                    query.id = id;
                }
            }
            if (!utils.empty(query)){
                Pmg.tabs.gotoTab({object: object, view: 'Edit', mode: 'Tab', action: 'Tab', query: query});
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
            console.log('contextmenucallback');
        	var row = (this.clickedRow = this.row(evt)), cell = this.clickedCell = this.cell(evt), column = cell.column, clickedColumn = this.clickedColumn = this.column(evt);
                var menuItems = lang.clone(this.contextMenuItems);
                var colItems = row ? (column.onClickFilter || utils.in_array(column.field, this.objectIdCols) ? 'idCol' : 'row') : 'header';
                if (colItems !== 'header' && menuItems.canEdit && row.data.canEdit !== false){
                	menuItems[colItems] = menuItems[colItems].concat(menuItems.canEdit);
                }
                mutils.setContextMenuItems(this, menuItems[colItems].concat(lang.hitch(wcutils, wcutils.customizationContextMenuItems)(this)));
        },
/*
        editInNewTabSelector: function(grid){
            var theFields = [];
            for (var col in grid.columns){
                if (grid.columns[col].onClickFilter){
                    theFields.push(grid.columns[col].field);
                }
            }
            if (this.objectIdCols){
                return '.dgrid-row .field-' + this.objectIdCols.concat(theFields).join(', .field-');
            }else{
                return '';
            }
        },
*/        
        cellValueOf: function(field, idPropertyValue){
            if (idPropertyValue){
                if (this.collection.getSync){
                    return this.collection.getSync(idPropertyValue)[field];
                }else{
                    var query = {};
                    query[this.idProperty] = idPropertyValue;
                    return this.collection.filter(query).then(function(response){//to be tested!
                        var result =  response[0];
                        return (typeof result === "undefined" || result === null) ? '' : result;
                    });
                }
            }else{
                //var result = (this.clickedRow.data[field] ? this.clickedRow.data[field] : '');
                var result = this.clickedRowValues()[field];
                return (typeof result === "undefined" || result === null) ? '' : result;
            }
        },

        selectRow: function(rowIdProperty){
            var row = this.row(rowIdProperty);
           if (row){
                var reorderedIndex = arrayUtil.indexOf(this.store.sort(this.get('sort')).fetchSync(), this.store.getSync(rowIdProperty));
        
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
        }
    });
});
