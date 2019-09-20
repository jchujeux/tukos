define (["dojo/_base/declare", "dojo/_base/lang",  "dojo/dom", "dijit/form/Button", "dijit/registry", "tukos/PageManager"], 
    function(declare, lang, dom, Button, registry, Pmg){
    return declare("tukos.ObjectReset", [Button],{
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = this.form;
            this.resetDialogue = function(){
                var setResetValues = function(){
                    var sendOnReset = {};
                    if (form.sendOnReset){
                    	form.sendOnReset.forEach(function(widgetName){
                    		sendOnReset[widgetName] = form.valueOf(widgetName);
                    	});
                    }
                	Pmg.setFeedback(Pmg.message('actionDoing'));
                    return self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Reset'), query: self.urlArgs ? lang.mixin({id: form.valueOf('id')}, self.urlArgs.query) : {id: form.valueOf('id')}}, 
                    	sendOnReset, self.form.get('dataElts')); 
                }
                if (!self.form.hasChanged()){
                    return setResetValues();
                }else{
                    Pmg.confirm({title: Pmg.message('fieldsHaveBeenModified'), content: Pmg.message('sureWantToForget')}).then(
                            function(){return setResetValues()},
                            function(){Pmg.setFeedback(Pmg.message('actionCancelled'));
                    });
                }
            }
            this.on("click", function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setTimeout(self.resetDialogue, 100);
            });
        }
    });
});
