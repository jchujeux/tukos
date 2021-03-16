define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dijit/form/DropDownButton", "tukos/utils"], 
    function(declare, lang, ready, Button, utils){
    return declare([Button], {
        postCreate: function(){
        	this.inherited(arguments);
            this.dropDownPosition = ['below-centered'];
        	this.loadDropDown = function(callback){
                if (this.clientDialogDescription){
	                require(["tukos/ExportContentDialog", this.clientDialogDescription], lang.hitch(this, function(ExportContentDialog, clientDialogDescription){
	                    var dropDown = this.dropDown = new ExportContentDialog({attachedWidget: this, form: this.form, dialogDescription: utils.mergeRecursive(lang.clone(clientDialogDescription), this.dialogDescription)});
	                    ready(function(){
	                        dropDown.startup();
	                        callback();
	                    });
	                }));
                }else{
	                require(["tukos/ExportContentDialog"], lang.hitch(this, function(ExportContentDialog){
	                    var dropDown = this.dropDown = new ExportContentDialog({attachedWidget: this, form: this.form, dialogDescription: this.dialogDescription});
	                    ready(function(){
	                        dropDown.startup();
	                        callback();
	                    });
	                }));
                }
            };
        }
    });
});
