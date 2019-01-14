/*
 *  Provides a grid overview capability, allowing to display contents of a tukos object table
 *      -> 'overview': used as read-only cells, selector allow to select specific actions on selected rows via the save button
 */
define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/on", "dojo/dom-construct", "dojo/string", "dojo/query", "dgrid/extensions/DnD", "tukos/utils", "tukos/PageManager", "tukos/TukosDgrid", "tukos/dstore/Request",
		 "tukos/widgets/WidgetsLoader", "dijit/form/TextBox", "dijit/form/Button", "dijit/TooltipDialog", "dijit/popup", "dijit/layout/ContentPane", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, when, on, dct, string, query, DnD, utils, Pmg, TukosDgrid, Request, WidgetsLoader, TextBox, Button, TooltipDialog, popup, ContentPane, messages){
    return declare([TukosDgrid, DnD], {
        constructor: function(args){
            args.storeArgs.sortParam = args.storeArgs.sortParam || Pmg.getItem('sortParam');
            if (!args.storeArgs.target){
               args.storeArgs.object = args.object;
               args.storeArgs.mode = args.form.mode || 'tab';
               args.storeArgs.target = Pmg.requestUrl(args.storeArgs);
            }
            args.store = new Request(args.storeArgs);
            args.store.userFilters = lang.hitch(this, this.userFilters);
            args.store.postFetchAction = lang.hitch(this, this.postFetchAction);
            args.collection = args.store.filter({contextpathid: args.form.tabContextId()});
            for (var i in args.columns){
                var column = args.columns[i], field = column['field'];
                if (field && args.objectIdCols.indexOf(field) >= 0){
                    column['renderCell'] = this.renderNamedId;
                }
            }
        },
        postCreate: function(){
            this.inherited(arguments);
            if (this.hasFilters && this.hideServerFilters !== 'yes'){
            	this.showFilters();
            }
            this.dndSource.getObject = this.getObject;
            //this.dndSource.onDropInternal = this.onDropInternal;
            //this.dndSource.onDropExternal = this.onDropExternal;
            this.modify = {values: {}, displayedValues: {}};
            this.contextMenuItems.header.push({atts: {label: messages.showhidetargetvalues  , onClick: lang.hitch(this, function(evt){this.showColValues();})}});
        },
        allowSelect: function(row){
            if (typeof row.id == 'undefined'){//is the header rather than a data row (?)
                return true;
            }else if (row.data.canEdit){
                return true;
            }else{
                return false;
            }
        },
        getObject: function(node){
            return this.grid.clickedRow.data;
        }, 
        
        postFetchAction: function(response){
        	if (response.summary){
        		this.form.setWidgets(response.summary);
        	}
        },
        showColValues: function(){
            var grid = this, pane = grid.form, headerTable = grid.headerNode.firstChild, colValuesRow = query('.dgrid-colvalues-row', headerTable), clickedField = grid.clickedColumn.field;
            if (!colValuesRow.length > 0){
                Pmg.serverDialog({object: grid.object, view: 'overview', mode: 'tab', action: 'get', query: {params: {actionModel: 'getColsWidgetDescription'}}}).then(function(response){
            		grid.colValueWidgetsDescription = response.widgetsDescription;
            		grid.colValueParentNodes = {}, grid.colValueWidgets = {};
            		colValuesRow = dct.create('tr', {'class': 'dgrid-colvalues-row'}, headerTable);
                    utils.forEach(grid.columns, function(column, i){
                        var field = column.field, td;
                    	grid.colValueParentNodes[field]  = td = dct.create('td', {className: "dgrid-colvalues-cell dgrid-column-" + i + " field-" + field}, colValuesRow);
                    	td.columnId = field;
                    	if (field !== clickedField && grid.colValueWidgetsDescription[field]){
                        	td.onClickHandler = on(td, 'click', lang.hitch(grid, grid.instantiateColValueWidget, grid, field));                    		
                    	}
                    });
                    if (grid.colValueWidgetsDescription[clickedField]){
                        grid.instantiateColValueWidget(grid, clickedField);                   	
                    }
                });
            }else{
                if (colValuesRow[0].style.display === 'none'){
                	colValuesRow[0].style.display = 'table-row';
                	if (grid.colValueWidgetsDescription[clickedField] && !grid.colValueWidgets[clickedField]){
                		grid.instantiateColValueWidget(grid, clickedField);
                	}else{
                		grid.layoutHandle.resize();
                	}
                }else{
                	colValuesRow[0].style.display = 'none';
                	grid.layoutHandle.resize();
                }
            }
        },
        instantiateColValueWidget: function(grid, field){
            var widgetDescription = grid.colValueWidgetsDescription[field];    
        	if (widgetDescription.type !== 'LazyEditor'){
                	lang.setObject('atts.style.width', '100%', widgetDescription);
            }
            widgetDescription.atts.placeHolder = messages.entertargetvalue;
        	when (WidgetsLoader.instantiate(widgetDescription.type, widgetDescription.atts), function(widget){
            	var td = grid.colValueParentNodes[field];
        		widget.on('change', function(newValue){
        			grid.modify.values[field] = newValue;
        			grid.modify.displayedValues[field] = this.displayedValue || newValue;
        		});
            	grid.colValueWidgets[field] = widget;
            	td.appendChild(widget.domNode);
            	if (td.onClickHandler){
            		td.onClickHandler.remove();
            	}
            	grid.layoutHandle.resize();
            });
        }
        
    }); 
});
