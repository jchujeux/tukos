define (["dojo/_base/declare", "dojo/_base/lang",  "dojo/dom", "dijit/form/Button", "dijit/registry", "tukos/PageManager"], 
    function(declare, lang, dom, Button, registry, Pmg){
    return declare("tukos.ObjectReset", [Button],{
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = this.form;
            this.resetDialogue = function(){
                var setResetValues = function(){
                    var sendOnReset = {}, idValue = form.valueOf('id');
                    if (form.sendOnReset){
                    	form.sendOnReset.forEach(function(widgetName){
                    		sendOnReset[widgetName] = form.valueOf(widgetName);
                    	});
                    }
                	Pmg.setFeedback(Pmg.message('actionDoing'));
                    self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Reset'), query: self.urlArgs 
							? (idValue ? lang.mixin({id: idValue}, self.urlArgs.query) : self.urlArgs.query)
							: (idValue ? {id: idValue} : {})}, sendOnReset, self.form.get('dataElts'), Pmg.message('actionDone'), idValue ? false : true).then(function(){
								self.postAction();
							}); 
                }
                form.checkChangesDialog(setResetValues, true);
            }
            this.on("click", function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setTimeout(self.resetDialogue, 100);
            });
        },
		postAction: function(){
		}
    });
});
