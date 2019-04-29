define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojo/json", "dijit/MenuBar",
         "dijit/popup", "tukos/_NavigationMenuContextMixin", "tukos/menuUtils", "tukos/PageManager"], 
    function(declare, lang, on, JSON, MenuBar, popup,
             _NavigationMenuContextMixin, mutils, Pmg){
    return declare([MenuBar, _NavigationMenuContextMixin], {
        onClickMenuItem: function(evt){
            Pmg.tabs.request(this.onClickArgs);
        },
        onChangeObjectSelect: function(newValue){
            if (!(newValue === '')){
                var self = this;
                this.onChangeArgs.object = this.object;
                this.onChangeArgs.query =( this.sendAsNew ? {dupid: newValue, grade: 'NORMAL'} : {id: newValue});
                Pmg.tabs.request(this.onChangeArgs);
                setTimeout(function(){
                        self.set('value', '', false, '');
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
            item.on ('contextmenu', lang.hitch(this, this.contextMenuCallback, item));
            return item;
        },
        postCreate: function(){
            this.inherited(arguments);
            mutils.buildMenu({items: this.items}, 'itemsOnly', this, lang.hitch(this, this.addTriggers), lang.hitch(this, this.addContext));
        }   
    }); 
});
