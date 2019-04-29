define(["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dijit/Menu", "tukos/widgets/WidgetsLoader", "tukos/PageManager"], 
function(declare, lang, when, Menu, widgetsLoader, Pmg){
	var buildMenu = function(description, mode, theMenu, addTriggers, addContext){
	    	var type = description.type || 'Menu', atts = description.atts, items = description.items,
				setTriggers = function(widget){
					return addTriggers ? addTriggers(widget) : widget;
				},
				setContext = function(item){
					return addContext ? addContext(item) : item;
				};
			if (type === 'DropDownMenu'){
				return widgetsLoader.instantiate('DropDownMenu', {items: items});
			}
			switch(mode){
				case undefined:	
				case 'full':
					if (type === 'DynamicMenu'){
						theMenu = setTriggers(new DynamicMenu(atts));
					}else{
						theMenu = when(widgetsLoader.instantiate(type, atts), function(widget){
							return setTriggers(widget);
						});
					}
				case 'itemsOnly':
		        	when(theMenu, function(theMenu){
						for (var i in items){
			        		var item = items[i], type = item.type, atts = item.atts;
			        		switch (type){
			        			case 'MenuItem':
			        			case undefined: 
			        				when(widgetsLoader.instantiate('MenuItem', atts), function(menuItem){
			        					theMenu.addChild(setTriggers(menuItem));
			        				})
			        				break;
			        			case 'PopupMenuItem':
			        			case 'PopupMenuBarItem':
			    					when(buildMenu(item.popup, 'full', null, addTriggers, addContext), function(popup){
				        				atts.popup = popup;
				        				when(widgetsLoader.instantiate(type, atts), function(popupItem){
				        					setContext(popupItem);
				        					popupItem.on('mouseover', function(evt){
				        						var self = this;
				        						if (!this.popup.isBuilt){
					                				when(buildMenu({items: this.popup.items}, 'itemsOnly', this.popup, addTriggers, addContext), function(popup){
					                					self.popup = popup;
					                					self.popup.isBuilt = true;
					                				});
					                			}
				        					});
					                		theMenu.addChild(popupItem);
				        				});
			    					});
			        				break;
			        			default:
			        				console.log('this is not supposed to happen - menuUtils.buildMenu()');
			        		}
						}
		        	});
			}
			return theMenu;
		},
		DynamicMenu = declare([Menu], {
		_openMyself: function(args){
			var self = this, items = this.items, _arguments = arguments;
			if (items){
				this.destroyDescendants();
				when(buildMenu({items: items}, 'itemsOnly', this), function(){
					delete this.items;
					self.inherited(_arguments);					
				});
			}else{
				this.inherited(arguments);
			}
		}
	});
	return {
        buildContextMenu: function(widget, args){
            if (widget.contextMenu && widget.contextMenu.menu){
                widget.contextMenu.menu.destroyRecursive();
            }
            widget.contextMenu = {description: args};
            when(buildMenu(widget.contextMenu.description), function(menu){
            	widget.contextMenu.menu = menu;
            });
            widget.contextMenu.parentWidget = widget;
        },
        setContextMenuItems: function(widget, items){
            if (widget.contextMenu && widget.contextMenu.menu){
                widget.contextMenu.menu.items = items;
            }
        },
        newObjectDropDownDescription: function(objectName, onNewAction, onTemplateAction){
        	return {
            	type: 'DropDownMenu',
            	items: [
                    {atts: {label: Pmg.message('default'),   onClick: onNewAction}},
                    {type: 'PopupMenuItem', atts: {label: Pmg.message('fromtemplate')}, popup: {
                    	type: 'ObjectSelect', atts: {placeHolder: Pmg.message('selectatemplate'), object: objectName, dropdownFilters: {grade: 'TEMPLATE'}, onChange: onTemplateAction}}
                    }
                ]
        	}
        },
        newObjectMenuDescription: function(objectName, onNewAction, onTemplateAction){
        	return {
            	type: 'Menu',
            	items: [
                    {atts: {label: Pmg.message('default'),   onClick: onNewAction}},
                    {type: 'PopupMenuItem', atts: {label: Pmg.message('fromtemplate')}, popup: {
                    	type: 'ObjectSelect', atts: {placeHolder: Pmg.message('selectatemplate'), object: objectName, dropdownFilters: {grade: 'TEMPLATE'}, onChange: onTemplateAction}}
                    }
                ]
        	}
        },
        newObjectPopupMenuItemDescription: function(objectName, label, onNewAction, onTemplateAction){
        	return {type: 'PopupMenuItem', atts: {label: label}, popup: this.newObjectDropDownDescription(objectName, onNewAction, onTemplateAction)};
        },
        buildMenu: function(description, mode, theMenu, addTriggers, addContext){
            return buildMenu(description, mode, theMenu, addTriggers, addContext);
        }
    }
});
