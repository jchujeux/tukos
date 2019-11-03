define (["dojo/_base/declare", "dojo/_base/lang", "tukos/ObjectSelect", "dijit/registry", "dijit/focus", "tukos/PageManager"], 
    function(declare, lang, ObjectSelect, registry, focusUtil, Pmg){
    return declare(ObjectSelect, {
        postCreate: function(args){
            this.inherited(arguments);
            var form = registry.byId(this.form.id);
            this.on("change", function(newValue){
                var self = this;
                var item = this.item

                if (! (item.id == '')){// this test is needed due to fact that this.reset() fires the 'change' event again :-(.
                    var setEditValues = function(){
                        form.serverDialog(lang.mixin(self.urlArgs || {action: 'Edit'}, {query: {id: item.id}}), [], self.form.get('dataElts'), Pmg.message('actionDone')); 
                    }
                    if(!form.hasChanged()){
                        setEditValues();
                    }else{
                        Pmg.setFeedback(' ');
                        Pmg.confirmForgetChanges().then(
                        		function(){setEditValues();}, 
                        		function(){Pmg.setFeedback(Pmg.message('actionCancelled'));}
                        );
                    }
                    this.reset();
                }
            });
        }
    });
});
