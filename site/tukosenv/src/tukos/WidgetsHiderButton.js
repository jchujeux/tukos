define (["dojo/_base/declare", "dojo/_base/lang", "dojo/ready", "dijit/form/DropDownButton"], 
    function(declare, lang, ready, Button){
    return declare([Button], {
        postCreate: function(){
			var self = this;        	
			this.inherited(arguments);
        	this.loadDropDown = function(callback){
        		require(["tukos/_WidgetsHider"], function(_WidgetsHider){
        			var dropDown = self.dropDown = new _WidgetsHider({form: self.form});
					ready(function(){
						dropDown.startup();
						callback();
					});
        		});
            };
        }
    });
});
