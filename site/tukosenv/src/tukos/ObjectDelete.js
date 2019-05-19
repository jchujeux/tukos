define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dijit/form/Button", "dijit/registry", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, dom, on, Button, registry, Pmg, messages){
    return declare("tukos.ObjectDelete", [Button], {
        postCreate: function(){
            this.inherited(arguments);
            this.on("click", function(evt){
                var self = this;
                evt.stopPropagation();
                evt.preventDefault();
                
                Pmg.setFeedback(' ');

                var idValue = self.form.valueOf('id');
                if (idValue == ''){/* is new entry, nothing to delete on the server side*/
                	Pmg.alert({title: Pmg.message('nothingToDelete'), content: Pmg.message('newItemResetInstead')});
                }else{
                    Pmg.confirm({title: Pmg.message('deleteExistingItem'), content: Pmg.message('sureWantToDeleteItem')}).then(
                        function(){
                            var postValues = {'id': idValue};
                            var updatedWidget = registry.byId(self.form.id + 'updated');
                            if (updatedWidget){
                                postValues['updated'] = updatedWidget.get('value');
                            }
                            self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Delete')}, postValues, self.form.get('dataElts'), messages.actionDone); 
                        },
                        function(){Pmg.setFeedback(Pmg.message('actionCancelled'));});
                }
            });                    
        }
    });
});
