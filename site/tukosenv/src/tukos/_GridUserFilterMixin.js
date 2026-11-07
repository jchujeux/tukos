define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-construct", "dojo/store/Memory", "dojo/query", "dijit/_WidgetBase", "dijit/form/TextBox", "dijit/form/Select", "dijit/registry", "tukos/utils", "tukos/PageManager"], 
function(declare, lang, dct, Memory, query, Widget, TextBox, Select, registry, utils, Pmg){
    var oprStore = new Memory({data: [
        	{name:  '--' + Pmg.message('Select') + '--', id:  ''}, {name:  '=', id:  '='}, {name: '<>', id: '<>'}, {name: '>', id: '>'}, {name: '<', id: '<'},
        	{name: '>=', id: '>='}, {name: '<=', id: '<='}, {name: Pmg.message('rlike'), id: 'RLIKE'},
        	{name: Pmg.message('notrlike'), id: 'NOT RLIKE'}, {name: Pmg.message('inrange'), id: 'BETWEEN'}
        ]}),
        ColFilter = declare(Widget, {
        	postCreate: function(){
        		this.inherited(arguments);
        		var self = this, onChange = function(){self.hasChanged = true;}, 
					onBlur = function(){
						if(self.hasChanged){
							self.onFilterChange(self);
							self.hasChanged = false;
						}
					},
        			onKeyDown = function(evt){
						if (evt.keyCode === 13){
							var grid = self.grid, store = grid.store;
							self.onFilterChange(self); 
							//grid.set('collection', typeof store.getRootCollection === 'function' ? store.getRootCollection() : (grid.storeFilter ? store.filter(grid.storeFilter) : (store.getCollectionFilter ? store.filter(store.getCollectionFilter()) : store)))
							grid.set('collection');
						}
					};
        		this.inherited(arguments);
        		this.oprWidget = new Select(lang.mixin({placeHolder: Pmg.message('selectfilter'), labelAttr: 'name',
        			store: typeof this.filters === 'object' ? new Memory({data: this.filters}) : oprStore, onBlur: onBlur, onKeyDown: onKeyDown, onChange: onChange}, this.oprAtts
        		));
        		this.entryWidget = new TextBox(lang.mixin({id: this.id + 'entry', onBlur: onBlur, onKeyDown: onKeyDown, onChange: onChange}, this.entryAtts));
        		this.domNode.appendChild(this.oprWidget.domNode);
        		this.domNode.appendChild(this.entryWidget.domNode);
        	},
            _setValueAttr: function(values){
                if (typeof values === 'string'){
                    this.oprWidget.set('value', '=', false);
                    this.entryWidget.set('value', values, false);               
                }else{
                    this.oprWidget.set('value', values[0], false);
                    this.entryWidget.set('value', values[1], false);
                }
            },

            _getValueAttr: function(){
                return [this.oprWidget.get('value'), this.entryWidget.get('value')];
            },

            _setDisabledAttr: function(value){
                    this.oprWidget.set('disabled', value, false);
                    this.entryWidget.set('disabled', value, false);
            }
        });
	return declare(null, {
        constructor: function(args){
            for (var i in args.columns){
                var column = args.columns[i], field = column['field'];
                if (column.filter){
                	this.hasFilters = true;
                }
                if (column.rowsFilters){
                	this.mayHaveFilters = true;
                }
            }
        },
        postCreate: function(){
        	this.inherited(arguments);
			if (this.mayHaveFilters){
				this.contextMenuItems.header.push({atts: {label: Pmg.message('showhidefilters'), onClick: lang.hitch(this, function(evt){this.showHideFilters();})}}); 
			}       	 	
            this.store.userFilters = lang.hitch(this, this.userFilters);
    		if (this.hasFilters && this.hideServerFilters !== 'yes'){
            	this.showHideFilters();
            }
        },
        _setCollection: function(newValue){
			if (newValue){
				this.inherited(arguments);
			}else{
				const store = this.store;
				this.inherited(arguments, [typeof store.getRootCollection === 'function' ? store.getRootCollection(this.storeFilter) : (store.getCollectionFilter ? store.filter(store.getCollectionFilter(this.storeFilter)) : store)]);
			}
        },
        showHideFilters: function(){
            var grid = this,
                headerTable = grid.headerNode.firstChild,
                filtersRow = query('.dgrid-filter-row', headerTable);
            if (filtersRow.length == 0){
                this.buildFiltersRow(headerTable);
            }else{
                //filtersRow[0].set('style', {display: filtersRow[0].get('style').display === 'none' ? 'block' : 'none'});
                filtersRow[0].style.display = filtersRow[0].style.display === 'none' ? 'block' : 'none';
                //this.resize();
            }
            if (grid.layoutHandle){
                setTimeout(function(){grid.layoutHandle.resize();}, 0);            	
            }
        },
        buildFiltersRow: function(headerTable){
            var grid = this, pane = grid.form, 
            	onFilterChange = function(filterWidget){
	                const theNewFilter = filterWidget.get('value');
	                if (grid.customizationPath){
	             		lang.setObject(grid.customizationPath + 'columns.' + filterWidget.col + '.filter', theNewFilter, grid.getRootForm());
	             	}
            		grid.columns[filterWidget.col].filter = theNewFilter;
        		};
            filtersRow = dct.create('tr', {'class': 'dgrid-filter-row'}, headerTable);
            utils.forEach(grid.columns, function(column, i){
                var colId = column.field;
                var td  = dct.create('td', {className: "dgrid-filter-cell dgrid-column-" + i + " field-" + colId}, filtersRow);
                td.columnId = colId;
                if (column.rowsFilters){
                    var filter = new ColFilter({onFilterChange: onFilterChange, grid: grid, col: colId, filters: column.rowsFilters, oprAtts: {style: 'width: 7em;color: Black;'}, entryAtts: {style: 'width: 7em;color: Black;'}}, td);
                    if (column.filter){
                        filter.set('value', column.filter);
                    }
                    if (grid.columns[i].rowsFilters === 'disabled'){
                        filter.set('disabled', true);
                    }
                    column.filterWidget = filter;
                }
            });        	
        },
        userFilters: function(){
        	//var columnsCustomization = lang.getObject('widgetsDescription.' + this.widgetName + '.atts.columns', false, this.form.customization) || {}, userFilters = {};
        	var columnsCustomization = lang.getObject(this.customizationPath + 'columns', false, this.form) || {}, userFilters = {};
        	utils.forEach(this.columns, function(column, field){
        		var customFilter = (columnsCustomization[field] || {}).filter||{}, customFilterOpr = customFilter[0];
        		if (customFilterOpr && (!utils.in_array(customFilterOpr, ['RLIKE', 'NOT RLIKE', 'BETWEEN']) || customFilter[1])){
        			userFilters[field] = columnsCustomization[field].filter;
        		}else if ((column.filter || {})[0]){
        			userFilters[field] = column.filter;
        		}
        	});
			if (this.extraUserFilters){
				userFilters = lang.mixin(userFilters, this.extraUserFilters);
			}
        	return userFilters;
        },
       _setColumns: function(columns){
        	if (this.dynamicColumns){
        		for (var col in columns){
        			var column = columns[col];
	                 if (column.filter){
	                	this.hasFilters = true;
	                }
	                if (column.rowsFilters){
	                	this.mayHaveFilters = true;
	                }
        		}
				if (this.mayHaveFilters && !this.hasShowHideMenuItem){
					this.contextMenuItems.header.push({atts: {label: Pmg.message('showhidefilters'), onClick: lang.hitch(this, function(evt){this.showHideFilters();})}}); 
					this.hasShowHideMenuItem = true;
				}       	 	
        	}
    		this.inherited(arguments);
    		if (this.hasFilters && this.hideServerFilters !== 'yes'){
            	this.showHideFilters();
            }
        }
    });
}); 

