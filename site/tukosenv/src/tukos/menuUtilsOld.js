define(["dojo/_base/declare", "dojo/_base/lang", "dijit/Menu", "dijit/MenuItem", "dijit/PopupMenuItem", "dijit/PopupMenuBarItem", "dijit/DropDownMenu", "tukos/ObjectSelect", "dojo/i18n!tukos/nls/messages"], 
function(declare, lang, Menu, MenuItem, PopupMenuItem, PopupMenuBarItem, DropDownMenu, ObjectSelect, messages){//ObjectSelect required as may be instantiated. To be general could use WidgetsLoader instead
	var buildMenu = function(description, mode, theMenu, addTriggers, addContext){
	    	var type = description.type || 'Menu', atts = description.atts, items = description.items,
			setTriggers = function(widget){
				return addTriggers ? addTriggers(widget) : widget;
			};
			setContext = function(item){
				return addContext ? addContext(item) : item;
			}
			if (type === 'DropDownMenu'){
				return new DropDownMenu({items: items});
			}
			switch(mode){
				case undefined:	
				case 'full':
					theMenu =  setTriggers(new (eval(type))(atts));
				case 'itemsOnly':
		        	for (var i in items){
		        		var item = items[i], type = item.type, atts = item.atts;
		        		switch (type){
		        			case 'MenuItem':
		        			case undefined: 
		        				theMenu.addChild(setTriggers(new MenuItem(atts)));
		        				break;
		        			case 'PopupMenuItem':
		        			case 'PopupMenuBarItem':
		        				item.atts.popup = buildMenu(item.popup, 'full', null, addTriggers, addContext);
		    					var thePopupItem = new (eval(type))(item.atts);
		        				setContext(thePopupItem);
		                		thePopupItem.on('mouseover', function(evt){
		                			if (!this.popup.isBuilt){
		                				this.popup = buildMenu({items: this.popup.items}, 'itemsOnly', this.popup, addTriggers, addContext);
		                				this.popup.isBuilt = true;
		                			}
		                		});
		                		theMenu.addChild(thePopupItem);
		        				break;
		        			default:
		        				console.log('this is not supposed to happen - menuUtils.buildMenu()');
		        		}
		        }
			}
			return theMenu;
		},
		DynamicMenu = declare([Menu], {
		_openMyself: function(args){
			var self = this, items = this.items;
			if (items){
				this.destroyDescendants();
				buildMenu({items: items}, 'itemsOnly', this);
				delete this.items;
			}
			this.inherited(arguments);
		}
	});
	return {

        buildContextMenu: function(widget, args){
            if (widget.contextMenu && widget.contextMenu.menu){
                widget.contextMenu.menu.destroyRecursive();
            }
            widget.contextMenu = {description: args};
            widget.contextMenu.menu = buildMenu(widget.contextMenu.description);
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
                    {atts: {label: messages.adefault,   onClick: onNewAction}},
                    {type: 'PopupMenuItem', atts: {label: messages.fromtemplate}, popup: {
                    	type: 'ObjectSelect', atts: {placeHolder: messages.selecttemplate, object: objectName, dropdownFilters: {grade: 'TEMPLATE'}, onChange: onTemplateAction}}
                    }
                ]
        	}
        
        },
        newObjectMenuDescription: function(objectName, onNewAction, onTemplateAction){
        	return {
            	type: 'Menu',
            	items: [
                    {atts: {label: messages.adefault,   onClick: onNewAction}},
                    {type: 'PopupMenuItem', atts: {label: messages.fromtemplate}, popup: {
                    	type: 'ObjectSelect', atts: {placeHolder: messages.selecttemplate, object: objectName, dropdownFilters: {grade: 'TEMPLATE'}, onChange: onTemplateAction}}
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
