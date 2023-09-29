define (["dojo/_base/declare", "dojo/_base/lang", "dojo/store/Memory",  "dojo/store/Observable", "tukos/widgets/calendar/DnD", 
         "tukos/utils", "tukos/dateutils", "tukos/menuUtils", "tukos/PageManager"], 
    function(declare, lang, Memory, Observable, DnD, utils, dutils, mutils, Pmg){
    return declare([DnD], {
        constructor: function(args){
            args.store=  Observable(new Memory({}));
        },
        
        postCreate: function(){
            this.inherited(arguments);
            this.isNotUserEdit = 0;
            //this.isUserEdit = false;
            var observedSet = this.store.query();
            if (this.onCalendarChange){
                observedSet.observe(lang.hitch(this, this[this.onCalendarChange]), true);
            }

            if (this.onChangeNotify){
                observedSet.observe(lang.hitch(this, this.notifyWidgets), true);
            }

            this.on ('itemClick', function(e){
                var grid = this.getGrid();
                grid.selectRow(e.item.connectedIds ? e.item.connectedIds[this.gridWidget] : e.item[grid.store.idProperty]);//idProperty to support legacy
            });
        	this.on('itemContextMenu', function(evt){
                const self = this, isRestricted = Pmg.isRestrictedUser();
        		mutils.setContextMenuItems(this, (isRestricted ? [] : [{atts: {label: Pmg.message('editinnewtab'), onClick: lang.hitch(this, this.editInTab)}}]).concat([
                    {atts: {label: isRestricted ? Pmg.message('edit') : Pmg.message('editinpopup'), onClick: lang.hitch(this, this.editSelectedItem)}},
                    {atts: {label: isRestricted ? Pmg.message('duplicate') : Pmg.message('duplicateitem'), onClick: lang.hitch(this, this.duplicateSelectedItem)}},
                    {atts: {label: isRestricted ? Pmg.message('delete') : Pmg.message('deleteitem'), onClick: lang.hitch(this, this.deleteSelectedItem)}}],
                    isRestricted ? [] : [{type: 'PopupMenuItem', atts: {label: Pmg.message('forselection')}, popup: {type: 'DropDownMenu', items: [
                        {atts: {label: Pmg.message('deleteitems'),   onClick: lang.hitch(this, this.deleteSelectedItems)}}
                ]}}]));
        		setTimeout(function(){self.contextMenu.menu.items = self.contextMenu.description.items;}, 100);
        	});
            this.nextItemId = 0;

        }, 

        _setSelectedItemsAttr: function(value){
			var oldSelectedItems = this.selectedItems;

			this.selectedItems = value;
			this.selectedItem = null;

			if(oldSelectedItems != null && oldSelectedItems.length>0){
				this.updateRenderers(oldSelectedItems, false);
			}
			if(this.selectedItems && this.selectedItems.length>0){
				this.selectedItem = this.selectedItems[0];
				this.updateRenderers(this.selectedItems, false);
			}
		},
		
        keepChanges: function(){
            return {date: this.date};
        },
        restoreChanges: function (changes){
            this.set('date', changes.date);
            return true;
        },
		deleteSelectedItem: function(){
			this.store.remove(this.selectedItem.id);
		},

		duplicateSelectedItem: function(){
			var grid = this.getGrid(), item = grid.collection.getSync(this.selectedItem.connectedIds[grid.widgetName]);
			grid.addRow(undefined, grid.copyItem(item));
		},

		deleteSelectedItems: function(){
			var store = this.store;
			this.selectedItems.forEach(function(item){
				store.remove(item.id);
			});
			this.contextMenu.menu.onExecute();
		},
		editInTab: function(){
			var grid = this.getGrid(), item = grid.collection.getSync(this.selectedItem.connectedIds[grid.widgetName]);
            if (item.id){
            	Pmg.tabs.gotoTab({object: grid.object, view: 'Edit', query: {id: item.id, googlecalid: item.googlecalid}});
			}else{
				Pmg.setFeedback(Pmg.message('needtosavebeforeeditinnewtab'), null, null, true);
			}
		},
		editSelectedItem: function(evt){
			var grid = this.getGrid(), item = grid.collection.getSync(this.selectedItem.connectedIds[grid.widgetName]);
			//grid.openEditDialog(item, {x: evt.clientX, y: evt.clientY});
			grid.openEditDialog(item, {x: 0, y: 0});
		},
        defaultItemAtts: function(item){
        	var id = item._item.googlecalid || item._item.parentid, custom = this.customization;
        	if (id){
        		if (!custom.defaultItemsAtts){
        			custom.defaultItemsAtts = this.buildDefaultItemsAtts(custom.calendars);
        		}
        		return custom.defaultItemsAtts[id];
        	}else{
        		return undefined;
        	}
        },
        buildDefaultItemsAtts: function(calendars){
        	var sources = calendars.sources, styles = calendars.style, defaultItemsAtts = {};
        	sourceWidget = this.form.getWidget(sources); 
        	sourceWidget.iterate(function(item){
        		var id = item['googleid'] || item['tukosparent'];
        		if (id){
            		var style = {};
        			utils.forEach(styles, function(styleAttValue, styleAtt){
            			style[styleAtt] = item[styleAttValue.field];
            		});        			
        			defaultItemsAtts[id] = {style: style};// only style is implemented for now
        		}
        	});
        	return defaultItemsAtts;
        },

        createItemFunc: function(view, d, e, subColumn){
        
              // create item by maintaining control key
              if(!e.ctrlKey || e.shiftKey || e.altKey){
                return;
              }
            
              var start, end, calendar = view.owner.owner || view.owner;
              var colView = calendar.columnView;
              var cal = calendar.dateModule;
            
              if(view == colView){
                start = calendar.timeMode === 'duration' ? calendar.floorToDay(d) : calendar.floorDate(d, "minute", colView.timeSlotDuration);
                end = cal.add(start, "minute", colView.timeSlotDuration);
              }else{
                start = calendar.floorToDay(d);
                end = cal.add(start, "day", 1);
              }
            
              var item = {
                id: calendar.nextItemId,
                summary: Pmg.message("newevent", calendar.form.object),// + ' ' + calendar.nextItemId,
                startTime: start,
                endTime: end,
                allDay: view.viewKind == "matrix"
              };
            
              calendar.nextItemId++;
            
              return item;
        },

        getGrid: function(){
            return this.grid || (this.grid = this.form.getWidget(this.gridWidget));//registry.byId(this.form.id + this.gridWidget));
        },

        addItem: function(sourceItem, sourceWidget, start){// in support of DnD.onDrop
            if (this.timeMode === 'duration'){
                start = new Date(start.getFullYear(), start.getMonth(), start.getDate());
            }
            var gridWidget = this.getGrid(), gridWidgetName = this.gridWidget;
            var startDateProperty = this.onChangeNotify[gridWidgetName].startTime;
            var item = lang.clone(sourceItem), mapping = ((gridWidget.onDropMap || {})[sourceWidget.widgetName] || {}).fields;
            if ((sourceItem.connectedIds || {})[gridWidgetName]){
            	item.connectedIds = sourceItem.connectedIds;
            }else if (sourceWidget.onDropMode === 'linked'){
            	item.connectedIds = {};
            	item.connectedIds[sourceWidget.widgetName] = sourceItem[sourceWidget.collection.idProperty];
            }
            item[startDateProperty] = start;
            if (this.timeMode !== 'duration'){
            	var endDateProperty = this.onChangeNotify[gridWidgetName].endTime, durationProperty = (mapping || {})[this.onChangeNotify[gridWidgetName].duration] || this.onChangeNotify[gridWidgetName].duration;
            	item[endDateProperty] = dutils.addDurationString(item[durationProperty] || gridWidget.initialRowValue[durationProperty] || '[5, "minute"]', start);
            }
            //delete item.connectedIds;
            gridWidget.set('notify', {action:  'add', sourceWidget: sourceWidget, item: item, mapping: mapping});
            //this.notifyWidgets(item, -1);
        },

        notifyWidgets: function(object, removedFrom, insertedInto){
            if (!(this.inNotifyWidget || this.noNotifyWidgets)){
                this.inNotifyWidget = true;
            	object.duration = dutils.durationString(object.startTime, object.endTime, object.duration, false, this.durationFormat);
                lang.setObject('connectedIds.' + this.widgetName, object.id, object);
                if (typeof object.allDay !== "undefined"){
                	object.allDay = object.allDay ? 'YES' : 'NO';
                }
                for (var widgetName in this.onChangeNotify){
                    var widget = this.form.getWidget(widgetName);
                    widget.set('notify', {action: (removedFrom == -1 ?  'add' : (insertedInto == -1 ? 'delete' : 'update')), item: object, sourceWidget: this});
                }
                if (typeof object.allDay !== "undefined"){
                	object.allDay = object.allDay === 'YES' ? true : false;
                }
                this.inNotifyWidget = false;
            }
        },

        targetItem: function(sourceItem, sourceWidget, currentCalendarItem){
            var mapping = sourceWidget.onChangeNotify[this.widgetName], targetItem = {};
            for (var i in mapping){
                if (sourceItem.hasOwnProperty(i)){
                    var j = mapping[i];
                    targetItem[j] = sourceItem[i];
                }
            }
            if (typeof targetItem.startTime === 'string'){
            	targetItem.startTime = dutils.parseDate(targetItem.startTime);
            }else{
            	if (targetItem.duration && (currentCalendarItem && currentCalendarItem.startTime)){
            		targetItem.startTime = currentCalendarItem.startTime;
            	}
            }
			if (targetItem.startTime && typeof targetItem.duration === 'string'){
            	targetItem.endTime = dutils.addDurationString(targetItem.duration >= 'T00:15:00' ? targetItem.duration : 'T00:15:00', targetItem.startTime, this.durationFormat);
            }else if (typeof targetItem.endTime === 'string') {
            	targetItem.endTime = dutils.parseDate(targetItem.endTime);
            }
            if (targetItem.allDay){
            	targetItem.allDay = targetItem.allDay === 'NO' ? false : true;
            }
            if (sourceItem.connectedIds){
            	targetItem.connectedIds = sourceItem.connectedIds;
           }
            return targetItem;
        },
        
        addDefaultProperties: function(newItem, sourceItem){
            newItem.endTime = newItem.endTime|| dojo.date.add(newItem.startTime, 'minute', 5);
            newItem.summary = newItem.summary || '*';
            newItem.connectedIds = sourceItem.connectedIds;
            return newItem;
        },
        
        _setNotifyAttr: function(args){
            this.inSetNotify = (this.inSetNotify || 0);
            if (!(this.inSetNotify || this.inNotifyWidgets)){
	        	this.inSetNotify +=1;
            //if (!this.inNotifyWidget){
	            //this.isNotUserEdit += 1;
	            var self = this, calItemId = ((args.item || {}).connectedIds || {})[this.widgetName], isNotConnected = !calItemId && calItemId !== 0, action = args.action;
	            action = (isNotConnected && action === 'update') ? 'add' : ((!isNotConnected && action === 'add') ? 'update' : action);
	            if (action === 'create' || action === 'add'){
                    var item = this.targetItem(args.item, args.sourceWidget);
                    if (item.startTime){
                        item = this.addDefaultProperties(item, args.item);
                        item.id = this.nextItemId;
                        //lang.setObject('connectedIds.' + args.targetWidget.id, item.id, item);
                        item.connectedIds[this.widgetName] = item.id;
                        this.store.add(item);
                        this.nextItemId += 1;
                    }
	            }else if (action === 'delete'){
	            	this.store.remove(args.item.connectedIds[this.widgetName]);
                	delete args.item.connectedIds[this.widgetName];
	            }else if (action === 'update'){
	                var oldItem = this.store.get( args.item.connectedIds[this.widgetName]);
	                var newItem = this.targetItem(args.item, args.sourceWidget, oldItem);
	                if (newItem.startTime === null){
	                	this.store.remove(args.item.connectedIds[this.widgetName]);
	                	delete args.item.connectedIds[this.widgetName];
	                }else{
	                	if (newItem.startTime && ! newItem.endTime){
	                		newItem.endTime = dojo.date.add(newItem.startTime, "millisecond", oldItem.endTime - oldItem.startTime);
		                }
		                var needsRefresh = newItem.hasOwnProperty('allDay') && (newItem.allDay !== oldItem.allDay);
		                this.store.put(lang.mixin(oldItem, newItem), {overwrite: true});
		                if (needsRefresh){
		                	this.currentView.refreshRendering(true);
		                }
	                }
	             }
	             //this.isNotUserEdit += -1;
	             this.inSetNotify += -1;
            }
       },

        _setValueAttr: function(value){// only serves to empty the calendar store
            if (value === '' && this.nextItemId > 0){
                //this.isNotUserEdit += 1;
                var self = this;
                this.noNotifyWidgets = true;
                this.store.query().forEach(function(item){
                    self.store.remove(item.id);
                });
                this.noNotifyWidgets = false;
                this.nextItemId = 0;
                //this.isNotUserEdit += -1;
            }
        }
    
    }); 
});
