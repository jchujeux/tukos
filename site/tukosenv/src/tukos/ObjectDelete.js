define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dijit/form/Button", "dijit/registry", "tukos/DialogConfirm", "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, dom, on, Button, registry, DialogConfirm, Pmg, messages){
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

                    var dialog = new DialogConfirm({title: messages.nothingtoDelete, content: messages.newRecordResetInstead, hasSkipCheckBox: false});
                    dialog.show().then(
                        function(){Pmg.setFeedback(messages.actionCancelled);},/* user pressed OK    : no action */
                        function(){Pmg.setFeedback(messages.actionCancelled);});/* user pressed Cancel: no action */
                }else{
                    var dialog = new DialogConfirm({title: messages.askingtoDelete, content: messages.sureWantToDelete, hasSkipCheckBox: false});
                    dialog.show().then(
                        function(){
                            var postValues = {'id': idValue};
                            var updatedWidget = registry.byId(self.form.id + 'updated');
                            if (updatedWidget){
                                postValues['updated'] = updatedWidget.get('value');
                            }
                            self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Delete')}, postValues, self.form.get('dataElts'), messages.actionDone); 
                        },
                        function(){Pmg.setFeedback(messages.actionCancelled);});/* user pressed Cancel: no action */
                }
            });                    
        }
    });
});
