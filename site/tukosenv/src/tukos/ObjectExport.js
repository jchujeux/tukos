define (["dojo/_base/declare", "dojo/on", "dojo/dom-form", "dijit/form/Button", "dijit/registry", "tukos/utils", "tukos/DialogConfirm", "tukos/_ExportContentMixin",
         "tukos/PageManager",  "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, on, domForm, Button, registry, utils, DialogConfirm, _ExportContentMixin, Pmg, messages){
    return declare([Button, _ExportContentMixin], {
        postCreate: function(){
            this.inherited(arguments);
            on(this, "click", function(evt){
                this.openExportDialog();
            });
        }
    });
});
