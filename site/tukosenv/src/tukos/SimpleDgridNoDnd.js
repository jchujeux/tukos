define (["dojo/_base/declare", "dojo/_base/lang",  "dojo/on",
         "tukos/BasicGrid", "tukos/dstore/MemoryTreeObjects", "tukos/_GridUserFilterMixin", "tukos/_GridEditMixin", "dgrid/Tree", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, on, BasicGrid, MemoryTreeObjects, _GridUserFilterMixin, _GridEditMixin, Tree, Pmg, messages){
    var widget = declare([BasicGrid, Tree, _GridUserFilterMixin, _GridEditMixin], {

        constructor: function(args){
            args.store = new MemoryTreeObjects(args.storeArgs);
            args.collection = args.store.getRootCollection();
        },

        postCreate: function(){
            this.inherited(arguments);
			this.setEditContextMenuItems(!this.disabled);
            this.revert();//Necessary for the children rows expansion / collapse to work (!)
        },
		_setStore: function(storeArgs){
			delete this.store;
			this.store = new MemoryTreeObjects(storeArgs);
			this.collection = this.store.getRootCollection();
		},
		_setDisabled: function(newValue){
            if (newValue != this.disabled){
            	this.setEditContextMenuItems(!newValue);
            	this.disabled = newValue;
			}
		},
		_setColumns: function(){
			this.inherited(arguments);
			delete this.contextMenuItemsWithEdit;
			this.setEditContextMenuItems(!this.disabled);
			/*if ('rowId' in this.columns){
				const self = this;
				this.contextMenuItems.idCol.push({atts: {label:Pmg.message('UpdateRowIds'),   onClick: function(){self.updateRowIds()}}});
				this.contextMenuItems.row.push({atts: {label:Pmg.message('UpdateRowIds'),   onClick: function(){self.updateRowIds()}}});
			}*/
		},
		setEditContextMenuItems: function(canEdit){
            if (canEdit){
	            this.contextMenuItemsWithoutEdit = this.contextMenuItemsWithoutEdit || lang.clone(this.contextMenuItems);
	            if (!this.disabled){
	                const self = this;
	                this.contextMenuItems = this.contextMenuItemsWithEdit || function(){
		                self.contextMenuItemsWithEdit = lang.clone (self.contextMenuItemsWithoutEdit);
		                const addedItems = [
		                        {atts: {label: messages.insertrowbefore  ,   onClick: function(evt){self.addRow('before')}}}, 
		                        {atts: {label: messages.addrow    ,   onClick: function(evt){self.addRow('append')}}}, 
								{atts: {label: messages.copyrow,   onClick: function(evt){self.copyRow(evt)}}}
		                ];
		                if ('rowId' in self.columns){
							addedItems.push({atts: {label:Pmg.message('UpdateRowIds'),   onClick: function(){self.updateRowIds()}}});
						}
						if (!self.noDeleteRow){
		                	addedItems.push({atts: {label:messages.deleterow,   onClick: function(evt){self.deleteRow()}}});
		                }
		                self.contextMenuItemsWithEdit.row = self.contextMenuItemsWithoutEdit.row.concat(addedItems);
		                self.contextMenuItemsWithEdit.idCol = self.contextMenuItemsWithoutEdit.idCol.concat(addedItems);
		                self.contextMenuItemsWithEdit.header.push({atts: {label: messages.addrow   ,   onClick: function(evt){self.addRow('append')}}});
		                if (self.columnsEdit){
		                    self.contextMenuItemsWithEdit.header.push({atts: {label: messages.insertcolumn, onClick: function(evt){self.addColumn()}}});
		                    self.contextMenuItemsWithEdit.header.push({atts: {label: messages.deletecolumn, onClick: function(evt){self.deleteColumn()}}});
		                }
		                return self.contextMenuItemsWithEdit;
					}();
	            }else{
					this.contextMenuItems = this.contextMenuItemsWithEdit;
				}
			}else{
				if (this.contextMenuItemsWithoutEdit){
					this.contextMenuItems = this.contextMenuItemsWithoutEdit;
				}
			}
		}

    }); 
    return widget;
});
