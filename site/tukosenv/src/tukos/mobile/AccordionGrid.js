define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when",  "dojo/aspect", "dojo/ready", "dijit/registry", "dojox/mobile/Container", "dojox/mobile/Heading",  "dojox/mobile/Accordion", 
		"dojox/mobile/ContentPane", "tukos/dstore/MemoryTreeObjects", "tukos/widgets/WidgetsLoader", "tukos/utils", "tukos/evalutils", "tukos/widgetUtils", "tukos/PageManager"], 
    function(declare, lang, when, aspect, ready, registry, Container, Heading, Accordion, ContentPane, MemoryObjects, WidgetsLoader, utils, eutils, wutils, Pmg){
    var iconBase = require.toUrl("tukos/mobile/resources/images/icons16.png"), domainIcon = "16,48,16,16", newIcon = "0,16,16,16", editIcon = "0,0,16,16", menuItemIcon = "0,32,16,16", objectSelectIcon = "0,0,16,16";
	return declare([Container], {
        constructor: function(args){
        	args = lang.mixin(args, {style: {backgroundColor: 'DarkGrey'}, store: new MemoryObjects(args.storeArgs)});
            //args.collection = args.store.getRootCollection();
        },
		postCreate: function(){
			var actionsHeading;
			this.inherited(arguments); 
			this.set('collection', this.store.getRootCollection());
			this.dirty = {};
			this.hasChangedSinceLastCollapse = false;
			this.deleted = [];
            this.addChild(this.actionsHeading = actionsHeading = new Heading({}));
			dojo.when(WidgetsLoader.instantiate('TukosButton', utils.mergeRecursive({label: this.accordionAtts.addRowLabel, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, pane: this.form,
                			form: this.form, onClick: lang.hitch(this, this.addRow)}, {})), function(theWidget){
                				actionsHeading.addChild(theWidget);
                				theWidget.layoutContainer = theWidget.domNode;
            });
			//this.setAccordion();
        },
        setAutoHeight: function(){
    		var accordion = this.accordion;
    		ready(function(){
        		var nodes = Array.apply(null, accordion.domNode.getElementsByClassName("mblAccordionPane"));
        		nodes.forEach(function(node){
        			node.style.height = "";
        		});
    		});
    	},
        getNewId: function(){
        	this.maxId += 1;
            return this.maxId;
        },
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
			return {widgetsDescription: widgetsDescription, layout: {tableAtts: {cols: 1, customClass: 'labelsAndValues', showLabels: true, orientation: this.accordionAtts.orientation}, widgets: widgets}};
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
				registry.byNode(pane.domNode.parentNode)._at.labelNode.innerHTML = grid.getRowLabel(item);
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
            if (this.initialId){
                item.id = item.id || this.getNewId();
            }
			this.collection.addSync(item);
			for (var j in item){
				 if (j != idp){
				     this.updateDirty(item[idp], j, item[j], true);
				 }
			}
			this.setSummary();
			this.accordion.addChild(new ContentPane({iconPos1: newIcon, label: this.accordionAtts.newRowLabel, selected: true, editor: {type: 'MobileTukosPane', atts: {form: this.form, data: {value: item}, grid: this, item: item, commonWidgetsAtts: this.commonWidgetsAtts()}}}), 0);
		},
		deleteRow: function(evt){
			var button = registry.getEnclosingWidget(evt.originalTarget), rowPane = button.rowPane, editorPane = rowPane.editorPane, item = editorPane.item, idp = this.collection.idProperty, idV = item[idp];
        	if (item.id != undefined){
                this.deleted.push({id: item.id, '~delete': true});
				wutils.markAsChanged(this, 'noStyle');
            }
            this.deleteDirty(idV);
            this.collection.removeSync(idV);
			this.setSummary();
            this.accordion.removeChild(rowPane);
			rowPane.destroyRecursive();
			console.log('in deleterow');
		},
		instantiateRow: function(rowPane){
			if (!rowPane.editorPane){
	           let /*editorActionsHeading, */self = this;
	            //rowPane.addChild(rowPane.editorActionsHeading = editorActionsHeading = new Heading({}));
				dojo.when(WidgetsLoader.instantiate('TukosButton', utils.mergeRecursive({label: this.accordionAtts.deleteRowLabel, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, rowPane: rowPane,
        			form: this.form, onClick: lang.hitch(this, this.deleteRow)}, {})), function(theWidget){
        				rowPane.addChild(theWidget);
        				//theWidget.layoutContainer = theWidget.domNode;
	            });
				dojo.when(WidgetsLoader.instantiate('TukosButton', utils.mergeRecursive({label: this.accordionAtts.actualizeRowLabel, style: {backgroundColor: 'DarkGrey', paddingLeft: 0, paddingRight: 0, fontSize: '12px'}, rowPane: rowPane,
        			form: this.form, onClick: lang.hitch(this, this.collapseRow)}, {})), function(theWidget){
        				rowPane.addChild(theWidget);
        				//theWidget.layoutContainer = theWidget.domNode;
	            });
				when(WidgetsLoader.instantiate(rowPane.editor.type, lang.mixin(this.rowPaneAtts(), rowPane.editor.atts)), function(editorPane){
					rowPane.editorPane = editorPane;
					ready(function(){
						rowPane.addChild(editorPane);
						self.expandRow(editorPane);
						//rowPane.resize();
					});
				});
			}else{
				//this.setPaneWidgetsValue();
				this.expandRow(rowPane.editorPane);
			}
		},
		setAccordion: function(){
			(this.accordion = new Accordion({iconBase: iconBase, "class":"mblAccordionRoundRect"})).placeAt(this.domNode);
			aspect.after(this.accordion, "expand", lang.hitch(this, this.setAutoHeight));
			aspect.before(this.accordion, "expand", lang.hitch(this, this.instantiateRow));
			aspect.after(this.accordion, 'collapse', lang.hitch(this, this.collapseRow));
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
				accordion.addChild(new ContentPane({iconPos1: editIcon, label: self.getRowLabel(item), editor: {type: 'MobileTukosPane', atts: {form: form, data: {value: item}, grid: self, item: item, commonWidgetsAtts: self.commonWidgetsAtts()}}}));
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
            var maxId = this.maxId = 0;
            this.store.forEach(function(row){
                if (row.id > maxId){
                    maxId = row.id;
                }
            });
            this.maxId = maxId;
            if (!this.form.markIfChanged && maxId > this.maxServerId){
            	this.maxServerId = maxId;
            }
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
			setTimeout(function(){
				wutils.watchCallback(self, 'collection', null, newValue);
			}, 100);	
        }
    }); 
});
