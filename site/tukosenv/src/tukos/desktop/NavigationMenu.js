define (["dojo/_base/declare", "dojo/_base/lang", "dijit/MenuBar", "tukos/_NavigationMenuMixin", "tukos/menuUtils"], 
    function(declare, lang, MenuBar, _NavigationMenuMixin, mutils){
    return declare([MenuBar, _NavigationMenuMixin], {
        postCreate: function(){
            this.inherited(arguments);
            mutils.buildMenu({items: this.items}, 'itemsOnly', this, lang.hitch(this, this.addTriggers), lang.hitch(this, this.addContext));
        }   
    }); 
});
