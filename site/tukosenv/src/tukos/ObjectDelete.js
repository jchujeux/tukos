define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dijit/form/Button", "dijit/registry", "tukos/utils", "tukos/PageManager"], 
    function(declare, dom, on, Button, registry, utils, Pmg){
    return declare("tukos.ObjectDelete", [Button], {
        postCreate: function(){
            this.inherited(arguments);
            this.on("click", function(evt){
                var self = this, form = self.form;
                evt.stopPropagation();
                evt.preventDefault();
                
                Pmg.setFeedback(' ');

                var idValue = self.form.valueOf('id');
                if (idValue == ''){/* is new entry, nothing to delete on the server side*/
                	Pmg.alert({title: Pmg.message('nothingToDelete'), content: Pmg.message('newItemResetInstead')});
                }else if(!form.changedValues().permission && utils.in_array(form.valueOf('permission'), ['PL', 'RL', 'UL'])){
                    	Pmg.setFeedback(Pmg.message('itemislocked')); Pmg.beep();
                }else{
                    Pmg.confirm({title: Pmg.message('deleteExistingItem'), content: Pmg.message('sureWantToDeleteItem')}).then(
                        function(){
                            var postValues = {'id': idValue};
                            var updatedWidget = registry.byId(self.form.id + 'updated');
                            if (updatedWidget){
                                postValues['updated'] = updatedWidget.get('value');
                            }
                            self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Delete')}, postValues, self.form.get('dataElts'), Pmg.message('actionDone'), true).then(function(){
								self.postAction();
							}); 
                        },
                        function(){Pmg.setFeedback(Pmg.message('actionCancelled'));});
                }
            });                    
        },
		postAction: function(){
		}
    });
});
