define(["dojo/_base/lang", "dijit/Menu", "dijit/MenuItem", "dijit/PopupMenuItem", "dijit/PopupMenuBarItem", "dijit/DropDownMenu", "tukos/ObjectSelect", "dojo/i18n!tukos/nls/messages"], 
	function(lang, Menu, MenuItem, PopupMenuItem, PopupMenuBarItem, DropDownMenu, ObjectSelect, messages){
	return {

        setContextMenu: function(widget, args){
            if (widget.contextMenu && widget.contextMenu.menu){
                widget.contextMenu.menu.destroyRecursive();
            }
            widget.contextMenu = {description: args};
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
        
        newObjectPopupMenuItemDescription: function(objectName, label, onNewAction, onTemplateAction){
        	return {type: 'PopupMenuItem', atts: {label: label}, popup: this.newObjectDropDownDescription(objectName, onNewAction, onTemplateAction)};
        },

        buildMenu: function(description, mode, theMenu, addTriggers, addContext){
        	var type = description.type || 'Menu', atts = description.atts, items = description.items, buildMenu = lang.hitch(this, this.buildMenu),
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
    	        				item.atts.popup = this.buildMenu(item.popup, 'full', null, addTriggers, addContext);
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
        
        showContextMenu: function(widget){
        	if (widget.contextMenu){
                if (!widget.contextMenu.menu){
                    widget.contextMenu.menu = this.buildMenu(widget.contextMenu.description);
                    widget.contextMenu.parentWidget = widget;
                    //widget.contextMenu.menu.startup();
                }
            }
        }
    }
});
