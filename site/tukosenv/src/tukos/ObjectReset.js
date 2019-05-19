define (["dojo/_base/declare", "dojo/dom", "dijit/form/Button", "dijit/registry", "tukos/PageManager"], 
    function(declare, dom, Button, registry, Pmg){
    return declare("tukos.ObjectReset", [Button],{
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.resetDialogue = function(){
                var setResetValues = function(){
                    var theId = self.form.valueOf('id');
                    Pmg.setFeedback(Pmg.message('actionDoing'));
                    return self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Reset'), query: theId == '' ? {} : {id: theId}}, [], self.form.get('dataElts')); 
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
