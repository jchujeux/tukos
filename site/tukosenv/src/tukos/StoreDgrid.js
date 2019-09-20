define (["dojo/_base/declare", "dojo/_base/array", "dojo/_base/lang", "dojo/on", "dgrid/extensions/DnD",
         "tukos/_GridEditMixin", "tukos/_GridEditDialogMixin", "tukos/TukosDgrid", "tukos/dstore/MemoryTreeObjects", "tukos/dstore/LazyMemoryTreeObjects",
         "tukos/utils", "tukos/evalutils", "tukos/menuUtils", "tukos/widgetUtils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, arrayUtil, lang, on, DnD, _GridEditMixin, _GridEditDialogMixin, TukosDgrid, MemoryTreeObjects, LazyMemoryTreeObjects,
    		 utils, eutils, mutils, wutils, Pmg, messages){
    var widget =  declare([TukosDgrid, DnD, _GridEditMixin, _GridEditDialogMixin], {

        constructor: function(args){
            if (args.storeType === 'LazyMemoryTreeObjects'){
                args.storeArgs.childrenUrlArgs = function(parentId){
                    return {object: args.form.object, view: args.form.viewMode, mode: args.form.paneMode, action: 'SubObject', query: {action: 'getChildren', subObjectWidget: args.widgetName, parentid: parentId, id: args.form.valueOf('id')}};
                }
                args.store = new LazyMemoryTreeObjects(args.storeArgs);
            }else{
                args.store = new MemoryTreeObjects(args.storeArgs);
            }
            args.collection = args.store.getRootCollection();
        },

        postCreate: function(){
            this.inherited(arguments);
            this.noCopyCols = this.noCopyCols.concat(['parentid', 'permission', 'updator', 'updated', 'creator' , 'created']);
            this.dndSource.getObject = this.getObject;
            this.dndSource.onDropInternal = this.onDropInternal;
            this.dndSource.onDropExternal = this.onDropExternal;
            var self = this;
            if (!this.disabled){
            	var addedItems = [
            	                    mutils.newObjectPopupMenuItemDescription(this.object, messages.addrow, lang.hitch(this, this.addRow), lang.hitch(this, this.getTemplate, 'addRow')),
            	                    mutils.newObjectPopupMenuItemDescription(this.object, messages.addsubrow, lang.hitch(this, this.addSubRow, false), lang.hitch(this, this.getTemplate, 'addSubRow')),
            	                    {atts: {label: messages.copyrow,   onClick: function(evt){self.copyRow(evt)}}}
            	                ];
            	this.contextMenuItems.canEdit = [
            	            	    {atts: {label: messages.editinpopup, onClick: lang.hitch(this, this.editInPopup)}},
            	                    {atts: {label: messages.deleterow,   onClick: function(evt){self.deleteRow(false)}}},
            	                    {type: 'PopupMenuItem', atts: {label: messages.forselection}, popup: {type: 'DropDownMenu', items: [{atts: {label: messages.deleteselection,   onClick: lang.hitch(this, this.deleteSelection)}}]}}
            	                ];
                this.contextMenuItems.row = this.contextMenuItems.row.concat(addedItems);
                this.contextMenuItems.idCol = this.contextMenuItems.idCol.concat(addedItems);
                this.contextMenuItems.header.push(mutils.newObjectPopupMenuItemDescription(this.object, messages.addrow, lang.hitch(this, this.addRow), lang.hitch(this, this.getTemplate, 'appendRow')));
            }
            if (this.hasFilters && this.hideServerFilters !== 'yes'){
            	this.setUserCollectionFilters();
            	dojo.ready(lang.hitch(this, function(){
                	this.showFilters();
            	}));
            }
            this.revert();//Necessary for the children rows expansion / collapse to work (!)
        },
        setUserCollectionFilters: function(){
        	var userCollectionFilter = new this.store.Filter(), map = {'=': 'eq', '<>': 'ne', '>': 'gt', '<': 'lt', '>=': 'gte', '<=': 'lte', 'RLIKE': 'rlike', 'NOT RLIKE': 'notrlike', 'BETWEEN': 'between'}, columns = this.columns;
        	utils.forEach(this.userFilters(), function(filter, col){
        		var opr = filter[0], value = filter[1], column = columns[col];
        		if (opr && value){
        			if (column.widgetType === "StoreSelect"){
        				value = utils[(opr === 'RLIKE' || opr === 'NOT RLIKE') ? 'includesReplace' : 'findReplace'](column.editorArgs.storeArgs.data, 'name', value, 'id', {}, true, true);
        			}
        			userCollectionFilter = userCollectionFilter[map[opr]](col, value);
        		}
        	});
        	this.store.userCollectionFilter = userCollectionFilter;
        },
        onFilterChange: function(filterWidget){
        	this.inherited(arguments);
        	this.setUserCollectionFilters();
        	this.set('collection', this.store.getRootCollection());
        },
        onFilterKeyDown: function(event){
			if (event.keyCode === 13) {
				var grid = this.grid;
				this.onFilterChange(this);
			}        	
        },
        _setCollection: function(newValue){
        	this.inherited(arguments);
        	wutils.watchCallback(this, 'collection', null, newValue);
        },
        deleteSelection: function(){
        	var deselect = 0, grid = this;
        	utils.forEach(this.selection, function(status, id){
        		if (status){
    				var row = grid.row(id), item = row.data; 
					if ((typeof item.canEdit === "undefined") || item.canEdit){
						grid.deleteRowItem(item);
					}else{
						grid.deselect(id);
						deselect += 1;
					}
        		}
        	});
			this.contextMenu.menu.onExecute();
        },
        
        getTemplate: function(mode, newValue){
        	if (newValue !== ''){
                var self = this;
        		Pmg.serverDialog({object: this.object, view: 'NoView', mode: this.form.paneMode, action: 'Get', query: {params: {actionModel: 'GetTemplate'}, dupid: newValue}}, {}, messages.actionDone).then(
                        function (response){
                        	var newRow = response.data;
                        	newRow.grade = 'NORMAL';
                            newRow.contextid = self.form.valueOf('contextid');
                            newRow = lang.mixin(newRow, self.itemFilter());
                        	switch (mode){
                        		case 'addRow'   : self.addRow('', newRow); break;
                        		case 'appendRow': self.addRow('apppend', newRow); break;
                        		case 'addSubRow': lang.hitch(self, self.addSubRow(newRow));
                        	}
                        }
                );
        	}
        },
        
        itemFilter: function(){
            var result = {};
            for (var col in this.filters){
            	var filter = this.filters[col];
                if (typeof col === "string" && col[0] === '#'){
                	continue;
                }else if(typeof col === "string" && col[0] === '&'){
                	var filtersCallbacks = this.filtersCallbacks = this.filtersCallbacks || {}, callbackName = col.substring(1), callback = filtersCallbacks[callbackName];
                	if (!callback){
                		filtersCallbacks[callbackName] = callback = eutils.eval(filter, 'grid, item');
                	}
                	callback(this, result);
                }else{
                    if (typeof filter == 'string'){
                        result[col] = (filter.charAt(0) == '@' ? this.form.valueOf(filter.substring(1)) : filter);
                    }else if (typeof filter === 'object'){
                        if (filter[0] === '='){
                            result[col] = filter[1];
                        }
                    }else{
                        result[col] = filter;
                    }                    	
                }
            }
            return result;
        },
        
        matchesFilter: function(item, itemFilter){
            for (var col in itemFilter){
                if (typeof item[col] !== 'undefined' && item[col] !== itemFilter[col]){
                    return false;
                }
            }
            return true;
        },
        
        prepareInitRow: function(init){
            this.inherited(arguments);
            init['contextid'] = this.form.valueOf('contextid');
            init = lang.mixin(init, this.itemFilter());
            return init;
        },

        prepareInitSubRow: function(init){
            this.prepareInitRow(init);
            init['parentid'] = this.clickedRow.data.id;
            if (!this.clickedRow.data['hasChildren']){
                this.clickedRow.data['hasChildren'] = true;
                this.store.putSync(this.clickedRow.data, {overwrite: true});
            }
        },

        addSubRow: function(item){
            if (!this.clickedRow.data.id){
                Pmg.alert({title: messages.attemptToAddSubRowtoNewRow, content: messages.saveFirst});
            }else{
                var init = {}
                    grid = this;
                this.prepareInitSubRow(init);
                item = utils.merge(init, item || {});
                this.createNewRow(item, null, 'append');
                this.expand(this.clickedRow.data.idg, true);
                setTimeout(function(){grid.layoutHandle.resize();}, 0);
            }
        },

        deleteRow: function(rowItem){
            if ((rowItem ? rowItem.canEdit : this.clickedRow.data.canEdit) || ! rowItem.id){
                this.inherited(arguments);
            }
        }
    }); 
    return widget;
});
