define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom", "dojo/on", "dijit/form/Button", "dijit/registry", "tukos/utils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/json", "dojo/domReady!"], 
    function(declare, lang, dom, on, Button, registry, utils, Pmg, messages, JSON){
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
                        lang.setObject('custom.' + form.viewMode + '.' + form.paneMode, form.itemCustomization, changedValues);
                        delete form.itemCustomization;
                	}
                    if (utils.empty(changedValues)){
                        Pmg.setFeedback(messages.noChangeToSubmit);
                    }else if(!changedValues.permission && utils.in_array(form.valueOf('permission'), ['PL', 'RL'])){
                    	Pmg.setFeedback(Pmg.message('itemislocked')); Pmg.beep();
                    }else{
                        Pmg.setFeedback(messages.actionDoing);
                        self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Save'), query: self.urlArgs ? lang.mixin({id: form.valueOf('id')}, self.urlArgs.query) : {id: form.valueOf('id')}}, changedValues, form.get('dataElts'), messages.actionDone); 
                    }
                }, 100);
           });
        }
    });
});
