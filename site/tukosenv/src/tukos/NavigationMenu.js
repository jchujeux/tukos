define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/json", "dijit/Menu", "dijit/DropDownMenu", "dijit/MenuItem", "dijit/MenuBar",
         "dijit/popup", "tukos/_NavigationMenuContextMixin", "tukos/menuUtils", "tukos/PageManager"], 
    function(declare, lang, on, JSON, Menu, DropDownMenu, MenuItem, MenuBar, popup,
             _NavigationMenuContextMixin, mutils, Pmg){
    return declare([MenuBar, _NavigationMenuContextMixin], {

        onClickMenuItem: function(evt){
            Pmg.tabs.request(this.onClickArgs);
        },
        onChangeObjectSelect: function(newValue){
            if (!(newValue === '')){
                //console.log('NavigationMenu::onChangeObjectSelect - new value: ', + newValue);
                var self = this;
                this.onChangeArgs.object = this.object;
                this.onChangeArgs.query =( this.sendAsNew ? {dupid: newValue, grade: 'NORMAL'} : {id: newValue});
                Pmg.tabs.request(this.onChangeArgs);
                setTimeout(function(){
                        self.set('value', '', false, '');
                        //self.value = undefined;
                        var none = "none";
                    }, 100
                );
            }
            dijit.popup.close(dijit.getEnclosingWidget(this));                
        },

        moduleContextId: function(widget){
            if (widget.parentMenu && widget.parentMenu.navigationItem){
                return widget.parentMenu.navigationItem.descriptor.atts.contextid || this.moduleContextId(widget.parentMenu);
            }else{
                return undefined;
            }
        },

        addTriggers: function(widget){
            if (widget.onClickArgs){
                lang.setObject('query.contextpathid', this.moduleContextId(widget), this.onClickArgs);
                widget.onClick = this.onClickMenuItem;
            }
            if (widget.onChangeArgs){
                widget.onChange = this.onChangeObjectSelect;
            }
            return widget;
        },
        
        addContext: function(item){
            item.on ('mousedown', lang.hitch(this, this.mouseDownCallback, item));
            item.on ('contextmenu', this.contextMenuCallback);
            return item;
        },

        postCreate: function(){
            this.inherited(arguments);
            mutils.buildMenu({items: this.items}, 'itemsOnly', this, lang.hitch(this, this.addTriggers), lang.hitch(this, this.addContext));
        }   
    }); 
});
