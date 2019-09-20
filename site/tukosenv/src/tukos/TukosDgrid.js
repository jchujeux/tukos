define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/keys", "dojo/on", "dojo/when", "dojo/query", "dojo/request", "dojo/aspect", "dojo/dom-style",
         "dgrid/OnDemandGrid", "tukos/_GridMixin", "tukos/_GridSummaryMixin", "dgrid/Selection", "dgrid/Keyboard", "dgrid/Selector", "dgrid/extensions/ColumnHider", "dgrid/extensions/ColumnResizer", 
         "dgrid/extensions/DijitRegistry", "tukos/dgrid/Editor", "tukos/utils", "tukos/widgetUtils", "tukos/evalutils"/*, "tukos/dgrid/lazytree"*/, "dgrid/Tree"/*, "tukos/ganttColumn"*/,"tukos/colFilter", 
         "tukos/PageManager", "tukos/sheetUtils", "dojo/json", "dojo/i18n!tukos/nls/messages"/*, "dojo/domReady!"*/], 
function(declare, lang, dct, keys, on, when, query, request, aspect, domStyle,
         Grid, _GridMixin, _GridSummaryMixin, Selection, Keyboard, selector, Hider, Resizer, DijitRegistry, editor, utils, wutils, eutils, tree/*, ganttColumn*/,colFilter, 
         Pmg, sutils, JSON, messages){
    
	return declare([Grid, editor, tree, selector, Selection, Keyboard, Hider, Resizer, DijitRegistry, _GridMixin, _GridSummaryMixin], {

        constructor: function(args){
            for (var i in args.columns){
                this.setColArgsFunctions(args.columns[i]);
            }
            if (this.mayHaveFilters){
            	 this.contextMenuItems.header.push({atts: {label: messages.showhidefilters, onClick: lang.hitch(this, function(evt){this.showFilters();})}}); 
            }
        },
        setColArgsFunctions: function(colArgs){
            ['formatter', 'get', 'renderCell', 'canEdit'].forEach(
                function(col, index, array){
                    if (colArgs[col]){
                        colArgs[col] = (typeof this[colArgs[col]] === 'function') ? this[colArgs[col]] : eutils.eval(colArgs[col]);
                    }
                },
                this
            );
            if (colArgs.filter){
            	this.hasFilters = true;
            }
            if (colArgs.rowsFilters){
            	this.mayHaveFilters = true;
            }
        },
        postCreate: function(){
            var grid = this, pane = grid.form;
            grid.itemCustomization = grid.itemCustomization || 'customization';
            this.formulaCache = {};
            var copyCellCallback = function(evt){
                if (evt.ctrlKey){
                    Pmg.setCopiedCell(sutils.copyCell(this.clickedCell));
                }
            };
            this.addKeyHandler(67, copyCellCallback);
            this.keepScrollPosition = true;
            this.noDataMessage =  Pmg.message('noDataMessage');
            this.inherited(arguments);
            this.set('maxHeight', this.maxHeight);
            //this.filterWidgets = {};
            this.rowHeights = {}; 
            if (this.style && this.style.width){
                domStyle.set(this.domNode, 'width', this.style.width);
            }
            aspect.after(grid, 'expand', function(){
                setTimeout(function(){grid.layoutHandle.resize();}, 500);
            });
/*
            this.on("dgrid-refresh-complete", function(evt){
                var scrollPos = grid.getScrollPosition();
                grid.layoutHandle.layout();
                grid.scrollTo(scrollPos);
                this.rowHeightToggle = false; 
            }); 
*/
            this.on("dgrid-cellfocusin", lang.hitch(this, function(evt){
                this.clickedRow = this.row(evt);
            	this.clickedCell = this.cell(evt);
            }));
            this.on("dgrid-columnstatechange", function(evt){
                var grid = evt.grid, pane = grid.form;
                lang.setObject(grid.itemCustomization + '.widgetsDescription.' + grid.widgetName + '.atts.columns.' + evt.column.field + '.hidden', evt.hidden, pane);
            });
            this.on("dgrid-columnresize", function(evt){
                if (evt.width != 'auto'){
                    var grid = evt.grid, pane = grid.form;
                lang.setObject(grid.itemCustomization + '.widgetsDescription.' + grid.widgetName + '.atts.columns.' + grid.columns[evt.columnId].field + '.width', evt.width, pane);
                }
            });
            this.on("dgrid-sort", function(evt){
                var grid = evt.grid, pane = grid.form;
                 lang.setObject(grid.itemCustomization + '.widgetsDescription.' + grid.widgetName + '.atts.sort', evt.sort, pane);
            });
            this.on("dgrid-datachange", lang.hitch(this, function(evt){
                if (evt.oldValue !== evt.value){
                    var column = evt.cell.column;
                    if (column.displayedValue == undefined){
                        column.displayedValue = [];
                    }
                    this.isNotUserEdit += 1;
                    this.onDataChangeLocalAction(this.getEditorInstance(column.field) || evt.cell.element.widget || evt.cell.element.input, evt.value);
                    this.isNotUserEdit += -1;

                    //this.formulaCache = {};
                }
            }));

            this.on("keydown", function(event){
            	switch (event.keyCode){
            		case keys.RIGHT_ARROW:
            			console.log('is right arrow');
            		default:
            	}
            });

            this.on(on.selector(".dgrid-row, .dgrid-header", "contextmenu"), lang.hitch(this, this.contextMenuCallback));
        },

        _setAllowApplicationFilter: function(newValue){
        	wutils.watchCallback(this, 'allowApplicationFilter', this.allowApplicationFilter, newValue);
        	this.allowApplicationFilter = newValue;
        },
        
/*        
        refresh: function(){
        	this.inherited(arguments);
        	console.log('refreshing ' + this.widgetName);
        },
*/        
        onDataChangeLocalAction: function(widget, newValue){
            if (typeof widget.localDataChangeActionFunctions == "undefined"){
                widget.localDataChangeActionFunctions = {};
                widget.parent = widget.grid = this;
                widget.form = this.form;
                var localAction = widget.onChangeLocalAction || (widget.onWatchLocalAction && widget.onWatchLocalAction['value']);
                if (localAction){
                    this.buildDataChangeLocalActionFunctions(widget.localDataChangeActionFunctions, localAction);
                }
            }
            var localActionFunctions = widget.localDataChangeActionFunctions;
            if (!utils.empty(localActionFunctions)){
            	this.noRefreshOnUpdateDirty = true;
            	for (var widgetName in localActionFunctions){
                    var targetWidget = this.getEditorInstance(widgetName) || this.cell(this.clickedRowIdPropertyValue(), widgetName).element.widget;
                    var widgetActionFunctions =  localActionFunctions[widgetName];
                    for (var att in widgetActionFunctions){
                        when (targetWidget, lang.hitch(this, function(targetWidget){
                        	var result = widgetActionFunctions[att].action(widget, targetWidget, newValue);
                        	if (att === 'value'){
                            	this.setCellValueOf(result, widgetName);
                            }
                        }));
                    }
                }
            	this.noRefreshOnUpdateDirty = false;
                setTimeout(lang.hitch(this, this.refresh), 0);
            }
        },


        buildDataChangeLocalActionFunctions: function(localActionFunctions, actionDescriptions){
            for (var widgetName in actionDescriptions){
                localActionFunctions[widgetName] = {};
                var description = actionDescriptions[widgetName];
                for (var att in description){
                    localActionFunctions[widgetName][att] = {};
                    attDescription = description[att];
                    localActionFunctions[widgetName][att].triggers = attDescription.triggers ? attDescription.triggers : {server: false, user: true};
                    localActionFunctions [widgetName][att].action = eutils.eval((attDescription.action ? attDescription.action : attDescription), 'sWidget, tWidget, newValue');
                }
            }
        },
        onFilterChange: function(filterWidget){
            lang.setObject('widgetsDescription.' + this.widgetName + '.atts.columns.' + filterWidget.col + '.filter', filterWidget.get('value'), this.pane.customization);        	
        },
        onFilterKeyDown: function(event){
        	if (event.keyCode === 13) {
    			var grid = this.grid;
				grid.onFilterChange(this);
				grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
			}        	
        },
        showFilters: function(){
            var grid = this, pane = grid.form,
                headerTable = grid.headerNode.firstChild,
                filtersRow = query('.dgrid-filter-row', headerTable);
            var onFilterChange = lang.hitch(grid, grid.onFilterChange), onFilterKeyDown = grid.onFilterKeyDown;
/*
            	var onFilterChange = function(filterWidget){
                lang.setObject('widgetsDescription.' + grid.widgetName + '.atts.columns.' + filterWidget.col + '.filter', filterWidget.get('value'), pane.customization);
            }
*/
            if (filtersRow.length > 0){
                dct.destroy(filtersRow[0]);
            }else{
                filtersRow = dct.create('tr', {'class': 'dgrid-filter-row'}, headerTable);
                utils.forEach(grid.columns, function(column, i){
                    var colId = column.field;
                    var td  = dct.create('td', {className: "dgrid-filter-cell dgrid-column-" + i + " field-" + colId}, filtersRow);
                    td.columnId = colId;
                    if (column.rowsFilters){
                        var width = column.width ? column.width + 'px' : '7em', 
                        	filter = new colFilter({onFilterChange: onFilterChange, onKeyDown: onFilterKeyDown, grid: grid, col: colId, filters: column.rowsFilters, oprAtts: {style: {width: width}}, entryAtts: {style: {width: width}}}, td);
                        if (column.filter){
                            filter.set('value', column.filter);
                        }
                        if (grid.columns[i].rowsFilters === 'disabled'){
                            filter.set('disabled', true);
                        }
                        column.filterWidget = filter;
                    }
                });
            }
            setTimeout(function(){grid.layoutHandle.resize();}, 0);
        },
        userFilters: function(){
        	var columnsCustomization = lang.getObject('widgetsDescription.' + this.widgetName + '.atts.columns', false, this.form.customization) || {}, userFilters = {};
        	utils.forEach(this.columns, function(column, field){
        		var customFilter = (columnsCustomization[field] || {}).filter||{}, customFilterOpr = customFilter[0];
        		if (customFilterOpr/* && (!utils.in_array(customFilterOpr, ['RLIKE', 'NOT RLIKE', 'BETWEEN']) || customFilter[1])*/){
        			userFilters[field] = columnsCustomization[field].filter;
        		}else if ((column.filter || {})[0]){
        			userFilters[field] = column.filter;
        		}
        	});
        	return userFilters;
        },
        _setMaxHeight: function(value){
            this.bodyNode.style.maxHeight = value;
        }
    }); 
});
