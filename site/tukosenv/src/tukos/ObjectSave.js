define (["dojo/_base/declare", "dojo/_base/lang", "dojo/when", "dojo/on", "dijit/form/Button", "tukos/utils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, lang, when, on, Button, utils, Pmg, messages){
    return declare("tukos.ObjectSave", [Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = this.form;
            on(this, "click", function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setTimeout(function(){
                    var changedValues = form.changedValues();
                    if (form.itemCustomization){
                        lang.setObject('custom.' + form.viewMode.toLowerCase() + '.' + form.paneMode.toLowerCase(), form.itemCustomization, changedValues);
                        delete form.itemCustomization;
                	}
                    if (utils.empty(changedValues)){
                        Pmg.setFeedback(messages.noChangeToSubmit);
                    }else if(!changedValues.permission && utils.in_array(form.valueOf('permission'), ['PL', 'RL', 'UL'])){
                    	Pmg.setFeedback(Pmg.message('itemislocked')); Pmg.beep();
                    }else{
                        Pmg.setFeedback(messages.actionDoing);
                        when(self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Save'), query: self.urlArgs ? lang.mixin({id: form.valueOf('id')}, self.urlArgs.query) : {id: form.valueOf('id')}}, changedValues, form.get('dataElts'), 
                        	messages.actionDone), function(response){
								if (response !== false){
									self.postAction();
								}
						}); 
                    }
                }, 100);
           });
        },
		postAction: function(){
		}
    });
});
