define (["dojo/_base/declare", "dojo/_base/lang", "tukos/ObjectSelect", "tukos/PageManager", "dojo/domReady!"], 
    function(declare, lang, ObjectSelect, Pmg){
    return declare(ObjectSelect, {
        postCreate: function(args){
            this.inherited(arguments);
            this.on("change", function(newValue){
                var self = this;
                var item = this.item

                if (item.id != ''){// this test is needed due to fact that this.reset() fires the 'change' event again :-(.
                    Pmg.tabs.gotoTab(lang.mixin(self.urlArgs || {}, {object: this.form.object, query: {id: item.id}}));
                    this.reset();
                }
            });
        }
    });
});
