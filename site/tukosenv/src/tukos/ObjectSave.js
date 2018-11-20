define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom", "dojo/on", "dojo/dom-form", "dijit/form/Button", "dijit/registry", "tukos/utils", "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/json", "dojo/domReady!"], 
    function(declare, lang, dom, on, domForm, Button, registry, utils, Pmg, messages, JSON){
    return declare("tukos.ObjectSave", [Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = this.form;
            on(this, "click", function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setTimeout(function(){
                    var changedValues = form.changedValues();
                    console.log('object save just got changedValues');
                    if (form.itemCustomization){
                        //changedValues['custom'] = utils.assign({}, form.viewMode, form.itemCustomization);
                        lang.setObject('custom.' + form.viewMode + '.' + form.paneMode, form.itemCustomization, changedValues);
                        delete form.itemCustomization;
                	}
                    if (utils.empty(changedValues)){
                        Pmg.setFeedback(messages.noChangeToSubmit);
                    }else{
                        Pmg.setFeedback(messages.actionDoing);
                        self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'save'), query: self.urlArgs ? lang.mixin({id: form.valueOf('id')}, self.urlArgs.query) : {id: form.valueOf('id')}}, changedValues, form.get('dataElts'), messages.actionDone); 
                    }
                }, 100);
           });
        }
    });
});
