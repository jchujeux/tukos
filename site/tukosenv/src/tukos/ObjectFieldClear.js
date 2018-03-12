/*
 *  Provides a Save button field which posts form edit contents to args.url
 *   - usage: 
 */
 
define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dojo/dom-form", "dijit/form/Button", "dojo/request", "dijit/registry"/*, "dojo/domReady!"*/], 
    function(declare, dom, on, domForm, Button, request, registry){
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
