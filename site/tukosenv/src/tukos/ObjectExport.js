define (["dojo/_base/declare", "dojo/_base/lang", "dijit/form/DropDownButton",  "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, Button, messages){
    return declare([Button], {
        postCreate: function(){
        	this.inherited(arguments);
            this.dropDownPosition = ['below-centered'];
        	this.loadDropDown = function(callback){
                require(["tukos/ExportContentDialog", "dojo/ready"], lang.hitch(this, function(ExportContentDialog, ready){
                    var dropDown = this.dropDown = new ExportContentDialog({form: this.form, dialogDescription: this.dialogDescription});
                    ready(function(){
                        dropDown.startup();
                        callback();
                    });
                }));
            };
        }
    });
});
