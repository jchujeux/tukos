define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dijit/form/DropDownButton", "tukos/utils", "tukos/evalutils", "tukos/PageManager"], 
    function(declare, lang, ready, Button, utils, eutils, Pmg){
    return declare([Button], {
        postCreate: function(){
        	this.inherited(arguments);
            this.dropDownPosition = ['below-centered'];
        	this.loadDropDown = function(callback){
				if (this.processCondition()){
	                require(["tukos/ExportContentDialog"], lang.hitch(this, function(ExportContentDialog){
						var dropDown = this.dropDown = new ExportContentDialog({attachedWidget: this, form: this.form, dialogDescription: this.clientDialogDescription ? utils.mergeRecursive(this.clientDialogDescription, this.dialogDescription || {}) : this.dialogDescription});
	                    ready(function(){
	                        dropDown.startup();
	                        callback();
	                    });
	                }));
				}else{
                    Pmg.alert({title: Pmg.message('newOrFieldsModified'), content: Pmg.message('saveOrReloadFirst')});
				}
            };
        },
        processCondition: function(){
            return this.conditionDescription ?  eutils.actionFunction(this, 'condition', this.conditionDescription) : true;
        }
    });
});
