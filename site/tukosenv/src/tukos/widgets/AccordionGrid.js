define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when",  "dojo/aspect", "dojo/ready", "dijit/registry", "dijit/TitlePane", 
		"dijit/layout/ContentPane", "tukos/dstore/MemoryTreeObjects", "tukos/widgets/WidgetsLoader", "tukos/utils", "tukos/evalutils", "tukos/widgetUtils", "tukos/PageManager"], 
    function(declare, lang, when, aspect, ready, registry, TitlePane, ContentPane, MemoryObjects, WidgetsLoader, utils, eutils, wutils, Pmg){
    var iconBase = require.toUrl("tukos/mobile/resources/images/icons16.png"), domainIcon = "16,48,16,16", newIcon = "0,16,16,16", editIcon = "0,0,16,16", menuItemIcon = "0,32,16,16", objectSelectIcon = "0,0,16,16";
	return declare([ContentPane], {
        constructor: function(args){
        	args = lang.mixin(args, {style: {backgroundColor: 'DarkGrey'}, store: new MemoryObjects(args.storeArgs)});
            //args.collection = args.store.getRootCollection();
        },
		postCreate: function(){
			const self = this;
			let actionsHeading;
			this.inherited(arguments); 
			this.set('collection', this.store.getRootCollection());
			this.dirty = {};
			this.hasChangedSinceLastCollapse = false;
			this.deleted = [];
            //this.addChild(this.actionsHeading = actionsHeading = new TitlePane({}));
			dojo.when(WidgetsLoader.instantiate('TukosButton', utils.mergeRecursive({label: this.accordionAtts.addRowLabel, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, pane: this.form,
                			form: this.form, onClick: lang.hitch(this, this.addRow)}, {})), function(theWidget){
                				//theWidget.placeAt(actionsHeading.focusNode);
                				self.addChild(theWidget);
                				theWidget.layoutContainer = theWidget.domNode;
            });
			//this.setAccordion();
        },
        /*setAutoHeight: function(){
    		var accordion = this.accordion;
    		ready(function(){
        		var nodes = Array.apply(null, accordion.domNode.getElementsByClassName("mblAccordionPane"));
        		nodes.forEach(function(node){
        			node.style.height = "";
        		});
    		});
    	},*/
		getRowLabel: function(item){
			return eutils.actionFunction(this, 'getRowLabel', this.accordionAtts.getRowLabelAction, 'kwArgs', {grid: this, item: item});
		},
		rowPaneAtts: function(){
			var widgetsDescription = {}, widgets = [];
			utils.forEach(this.columns, function(column, col){
        		var widgetType = column.widgetType || 'TextBox';
        		widgetsDescription[col] = {type: widgetType, atts: lang.clone(column.editorArgs) || {label: column.label, disabled: true}};
				if (column.hidden){
					widgetsDescription[col].atts.hidden = column.hidden;
				}
        		widgets.push(col);
            });
			return {widgetsDescription: widgetsDescription, layout: this.accordionAtts.desktopRowLayout};
		},
		updateDirty: function(idPropertyValue, field, value, isNewRow, propagateChange){
			var collection = this.collection, grid = this, collectionRow = collection.getSync(idPropertyValue), oldValue;
        	if (isNewRow || ((oldValue = utils.drillDown(this.dirty, [idPropertyValue, field], undefined)|| collectionRow[field]) !== value)){
				lang.setObject(idPropertyValue + '.' + field, value, this.dirty);
				collectionRow[field] = value;
				if (propagateChange){
					this.setSummary();
				}
                wutils.markAsChanged(this, 'noStyle');
                this.hasChangedSinceLastCollapse = true;
			}
		},
		deleteDirty: function(idPropertyValue){
			delete this.dirty[idPropertyValue];
			if (utils.empty(this.dirty) && this.deleted.length === 0 && this.form.changedWidgets[this.widgetName]){
				delete this.form.changedWidgets[this.widgetName];
				delete this.form.userChangedWidgets[this.widgetName];
			}
		},
		rowWidgetsLocalAction: function(sWidget, tWidget, newValue, oldValue){
			var pane = sWidget.pane, grid = pane.grid, collection = grid.collection, idp = collection.idProperty, item = pane.item;
			console.log('here I should do something'); 
			if (newValue !== oldValue){
				grid.updateDirty(item[idp], sWidget.widgetName, sWidget.get('value'), false, true);
				//registry.byNode(pane.domNode.parentNode)._at.labelNode.innerHTML = grid.getRowLabel(item);
				pane.titlePane.set('title', grid.getRowLabel(item));
			}
			return true;
		},
		commonWidgetsAtts: function(){
			return {onChangeLocalAction: {id: {localActionStatus: {triggers: {user: true, server: false}, action: this.rowWidgetsLocalAction}}}};
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
		addRow: function(){
			var item = lang.mixin(lang.clone(this.initialRowValue), this.itemFilter()), idp = this.collection.idProperty;
			this.collection.addSync(item);
			for (var j in item){
				 if (j != idp){
				     this.updateDirty(item[idp], j, item[j], true);
				 }
			}
			this.setSummary();
			const rowTitlePane = new TitlePane({iconPos1: newIcon, title: this.accordionAtts.newRowLabel, open: false, editor: {type: 'TukosPane', atts: {form: this.form, data: {value: item}, grid: this, item: item, commonWidgetsAtts: this.commonWidgetsAtts()}}});
			aspect.before(rowTitlePane, "_onShow", lang.hitch(this, this.instantiateRow, rowTitlePane));
			aspect.before(rowTitlePane, "onHide", lang.hitch(this, this.collapseRow));
			this.accordion.addChild(rowTitlePane, 0);
		},
		deleteRow: function(evt){
			var button = registry.getEnclosingWidget(evt.originalTarget), rowTitlePane = button.rowTitlePane, editorPane = rowTitlePane.editorPane, item = editorPane.item, idp = this.collection.idProperty, idV = item[idp];
        	if (item.id != undefined){
                this.deleted.push({id: item.id, '~delete': true});
				wutils.markAsChanged(this, 'noStyle');
            }
            this.deleteDirty(idV);
            this.collection.removeSync(idV);
			this.setSummary();
            this.accordion.removeChild(rowTitlePane);
			rowTitlePane.destroyRecursive();
			console.log('in deleterow');
		},
		instantiateRow: function(rowTitlePane){
			if (!rowTitlePane.editorPane){
	           let /*editorActionsHeading, */self = this;
				when(WidgetsLoader.instantiate('TukosButton', utils.mergeRecursive({label: this.accordionAtts.deleteRowLabel, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, rowTitlePane: rowTitlePane,
        			form: this.form, onClick: lang.hitch(this, this.deleteRow)}, {})), function(theWidget){
        				rowTitlePane.addChild(theWidget);
	            });
				when(WidgetsLoader.instantiate('TukosButton', utils.mergeRecursive({label: this.accordionAtts.actualizeRowLabel, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, rowTitlePane: rowTitlePane,
        			form: this.form, onClick: lang.hitch(this, this.collapseRow)}, {})), function(theWidget){
        				rowTitlePane.addChild(theWidget);
	            });
				when(WidgetsLoader.instantiate(rowTitlePane.editor.type, lang.mixin(this.rowPaneAtts(), rowTitlePane.editor.atts)), function(editorPane){
					rowTitlePane.editorPane = editorPane;
					editorPane.titlePane = rowTitlePane;
					ready(function(){
						rowTitlePane.addChild(editorPane);
						self.expandRow(editorPane);
						//rowPane.resize();
					});
				});
			}else{
				this.expandRow(rowTitlePane.editorPane);
			}
		},
		setAccordion: function(){
			(this.accordion = new ContentPane({})).placeAt(this.domNode);
			/*aspect.after(this.accordion, "expand", lang.hitch(this, this.setAutoHeight));
			aspect.before(this.accordion, "_onShow", lang.hitch(this, this.instantiateRow));
			aspect.after(this.accordion, 'collapse', lang.hitch(this, this.collapseRow));*/
		},
		expandRow: function(){
			
		},
		collapseRow: function(){
			console.log('AccordionGrid: collapseRow');
			if (this.hasChangedSinceLastCollapse){
				this.set('collection', this.collection);
				this.hasChangedSinceLastCollapse = false;
			}
		},
		isSetAccordion: function(){
			return !this.acccordion || !this.accordion.domNode;
		},
		buildAccordion: function(){
			var self = this, form = this.form, accordion = this.accordion;
			this.collection.sort(this.get('sort')).fetchSync().forEach(function(item){
				const rowTitlePane = new TitlePane({iconPos1: editIcon, title: self.getRowLabel(item), open: false, editor: {type: 'TukosPane', atts: {form: form, data: {value: item}, grid: self, item: item, commonWidgetsAtts: self.commonWidgetsAtts()}}});
				//rowTitlePane.onHide = function(){"console.log('in custom onHide');"};
				aspect.before(rowTitlePane, "_onShow", lang.hitch(self, self.instantiateRow, rowTitlePane));
				aspect.before(rowTitlePane, "onHide", lang.hitch(self, self.collapseRow));
				accordion.addChild(rowTitlePane);
			});
		},
		_setValueAttr: function(value){
			var store = this.store, accordion = this.accordion;
			if (accordion){
				accordion.destroyRecursive();
			}
			store.setData(value || []);
			this.dirty = {};
			this.setAccordion();
			this.buildAccordion();
			this.deleted = [];
			this.set('collection', store.getRootCollection());
			this.setSummary();
		},
		_getValueAttr: function(){
            var result = new Array, j = 0, sendOnSave = this.sendOnSave || [], noSendOnSave = utils.flip(this.noSendOnSave || []), dirtyToSend;
            for (var i in this.dirty){
                dirtyToSend = {};
            	utils.forEach(this.dirty[i], function(value, col){
                	if (!noSendOnSave.hasOwnProperty(col)){
                		dirtyToSend[col] = value;
                	}
                });
                if (!utils.empty(dirtyToSend)){
                	result[j] = dirtyToSend;
	            	var storeValues = this.collection.getSync(i), id = storeValues.id;
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
            return this.deleted.concat(result);            
		},
		setSummary: function(){
			if (this.summaryRow){
				ready(lang.hitch(this, function(){
					this.set('summary', this.getStoreSummary(this.collection, this.summaryRow.cols));
				}));
			}
		},
       getStoreSummary: function (store, summaryCols) {
            var result = {}, expressions, expression, self = this;
            for (var col in summaryCols){
                result[col] = '';
                expressions = summaryCols[col].content;
                for (var i in expressions){
                    expression = expressions[i];
                    if (typeof expression == 'string'){
                        result[col] += expression;
                    }else{
                        var res = (expression.init || 0);
                        var rhs = expression.rhs.replace(/#(.+)#/, "(row['$1'] === undefined ? '' : row['$1'])");//"self.cellValue(row,'$1')");
                        var theFunction = eutils.eval(rhs, 'self, row, res');
                        store.filter(expression.filter).forEach(function(row){
                        	res = theFunction(self, row, res);
                        });
                        result[col] += res;
                    }
                }
            }
            return result;
        },
        _setCollectionAttr: function(newValue){
			var self = this;        	
			this.collection = newValue;
			ready(function(){
				wutils.watchCallback(self, 'collection', null, newValue);
			});	
        },
    }); 
});
