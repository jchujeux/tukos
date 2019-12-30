define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dijit/form/Button", "dijit/registry"/*, "dojo/domReady!"*/], 
    function(declare, dom, on, Button, registry){
    return declare(Button, {
        postCreate: function(){
          var self = this;
          this.inherited(arguments);
          on(this, "click", function(evt){
                    var fieldToClear = registry.byId(self.form.id + self.fieldToClear);
                    fieldToClear.set('value','');
                    setTimeout(function(){self.form.resize();}, 0);
            });
        }
    });
});
