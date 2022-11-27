define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "tukos/_GridUserFilterMixin",
         "tukos/_GridEditMixin", "tukos/_GridEditDialogMixin", "tukos/BasicGrid", "dgrid/Tree", "tukos/dstore/MemoryTreeObjects", "tukos/dstore/LazyMemoryTreeObjects",
         "tukos/utils", "tukos/evalutils", "tukos/menuUtils", "tukos/widgetUtils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, dst, _GridUserFilterMixin, _GridEditMixin, _GridEditDialogMixin, BasicGrid, Tree, MemoryTreeObjects, LazyMemoryTreeObjects,
    		 utils, eutils, mutils, wutils, Pmg, messages){
    var widget =  declare([BasicGrid, Tree, _GridUserFilterMixin,  _GridEditMixin, _GridEditDialogMixin], {

        constructor: function(args){
			args.storeArgs.columns = args.columns;            
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
            const self = this,
            	 duplicateRow = function(evt){
            	 	self.addRow(undefined, self.copyItem(self.clickedRow.data));
            	 };
            if (!this.disabled){
            	if (Pmg.isRestrictedUser()){
            		this.contextMenuItems.canEdit = this.contextMenuItems.row.concat([
	            	    {atts: {label: Pmg.message('add'), onClick: lang.hitch(this, this.addRow)}},
            			{atts: {label: Pmg.message('edit'), onClick: lang.hitch(this, this.editInPopup)}},
            			{atts: {label: Pmg.message('duplicate'), onClick: duplicateRow}},
            			{atts: {label: Pmg.message('delete'), onClick: function(evt){self.deleteRow(false, false, true)}}},
            		]);
            		this.contextMenuItems.header = [{atts: {label: Pmg.message('add'), onClick: lang.hitch(this, this.addRow)}}];
            	}else{
	            	var addedItems = [
	            	                    mutils.newObjectPopupMenuItemDescription(this.object, messages.addrow, lang.hitch(this, this.addRow), lang.hitch(this, this.getTemplate, 'addRow')),
	            	                    mutils.newObjectPopupMenuItemDescription(this.object, messages.addsubrow, lang.hitch(this, this.addSubRow, false), lang.hitch(this, this.getTemplate, 'addSubRow')),
	            	                    {atts: {label: messages.copyrow,   onClick: function(evt){self.copyRow(evt)}}}
	            	                ];
	            	this.contextMenuItems.canEdit = [
	            	            	    {atts: {label: messages.editinpopup, onClick: lang.hitch(this, this.editInPopup)}},
	            	                    {atts: {label: messages.deleterow,   onClick: function(evt){self.deleteRow(false, false, true)}}},
	            	                    {type: 'PopupMenuItem', atts: {label: messages.forselection}, popup: {type: 'DropDownMenu', items: [{atts: {label: messages.deleteselection,   onClick: function(evt){self.deleteSelection(false, true)}}}]}}
	            	                ];
	                this.contextMenuItems.row = this.contextMenuItems.row.concat(addedItems);
	                this.contextMenuItems.idCol = this.contextMenuItems.idCol.concat(addedItems);
	                this.contextMenuItems.header.push(mutils.newObjectPopupMenuItemDescription(this.object, messages.addrow, lang.hitch(this, this.addRow), lang.hitch(this, this.getTemplate, 'appendRow')));
                }
            }
            this.revert();//Necessary for the children rows expansion / collapse to work (!)
        },
        resize: function(){
			var self = this, previousScrollPosition = this.getScrollPosition(), viewNode;
			if (!this.isBulk && !this.hidden){
				var customizationPath = this.customizationPath;// so that personnalization is not changed if a column has a width change during resize
				this.customizationPath = '';
				if (this.freezeWidth){
					if (this.layoutHandle.unfreezeWidth){
						dst.set(this.domNode, 'width', 'auto');
						this.layoutHandle.unfrozenWidths += 1;
					}else{
						if (this.form.needsToFreezeWidth){
							dst.set(this.domNode, 'width', Math.max(parseInt(dst.getComputedStyle(this.domNode).width), (this.minGridWidth || 0)) + 'px');
							this.enforceMinWidth = true;
						}
						if (this.enforceMinWidth){
							this.adjustMinWidthAutoColumns(5);
						}
					}
				}
				this.inherited(arguments);
		    	if (viewNode = this.form.domNode.parentNode){
			    	var style = this.bodyNode.style, bodyHeight = parseInt(window.getComputedStyle(document.body).getPropertyValue('height')), viewHeight = parseInt(window.getComputedStyle(viewNode).getPropertyValue('height')), oldMaxWidth = style.maxWidth, oldMaxHeight = style.maxHeight,
			    		maxHeight, newMaxHeight;
					style.maxWidth = parseInt(window.getComputedStyle(viewNode).getPropertyValue('width'));
					if (!style.maxHeight && viewHeight !== this.previousViewHeight){
				    	maxHeight = style.maxHeight === '' ? 0 : parseInt(style.maxHeight);
				    	style.maxHeight = (maxHeight + bodyHeight - viewHeight) + 'px';
				    	newMaxHeight = parseInt(style.maxHeight);
				    	this.previousViewHeight = viewHeight;
					}
					if (oldMaxWidth !== style.maxWidth || oldMaxHeight !== style.maxHeight){
						this.inherited(arguments);
					}
					setTimeout(function(){
						self.scrollTo(previousScrollPosition);
					}, 100);
				    //console.log('maxHeight: ' + maxHeight + ' bodyHeight: ' + bodyHeight + ' viewHeight: ' + viewHeight + ' newMaxHeight: ' + newMaxHeight);
		    	}
				this.customizationPath = customizationPath;
			}
        },
        deleteSelection: function(skipDeleteAction, isUserRowEdit){
        	var grid = this, toDelete = [];
        	utils.forEach(this.selection, function(status, id){
        		if (status){
    				var row = grid.row(id), item = row.data; 
					if ((typeof item.canEdit === "undefined") || item.canEdit){
						toDelete.push(item);
					}else{
						grid.deselect(id);
					}
        		}
        	});
			if (toDelete.length > 0){
				grid.deleteRows(toDelete, skipDeleteAction, isUserRowEdit);
			}
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
                    }else if (utils.isObject(filter)){
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
            const idp = this.collection.idProperty;
            if (!this.clickedRow.data.id){
                Pmg.alert({title: messages.attemptToAddSubRowtoNewRow, content: messages.saveFirst});
            }else{
                var init = {}
                    grid = this;
                this.prepareInitSubRow(init);
                item = utils.merge(init, item || {});
                this.createNewRow(item, null, 'append');
                this.expand(this.row(this.clickedRow.data[idp]), true);
                //setTimeout(function(){grid.layoutHandle.resize();}, 0);
            }
        },

        deleteRow: function(rowItem, skipDeleteAction, isUserRowEdit){
            if ((rowItem ? rowItem.canEdit : this.clickedRow.data.canEdit) || ! rowItem.id){
				this.inherited(arguments);
            }
        }
    }); 
    return widget;
});
