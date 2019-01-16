/*
 *  Provides a Reset button field which issues a request  (via post) to get form field values from the server and reloads them on every involved field
 *   - usage: 
 */
 
define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dojo/dom-form", "dijit/form/Button", "dijit/registry", "tukos/DialogConfirm", "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, dom, on, domForm, Button, registry, DialogConfirm, Pmg, messages){
    return declare("tukos.ObjectReset", [Button],{
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.resetDialogue = function(){
                var setResetValues = function(){
                    var theId = self.form.valueOf('id');
                    Pmg.setFeedback(messages.actionDoing);
                    return self.form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Reset'), query: theId == '' ? {} : {id: theId}}, [], self.form.get('dataElts')/*, messages.actionDone*/); 
                }
                if (!self.form.hasChanged()){
                    return setResetValues();
                }else{
                    var dialog = new DialogConfirm({title: messages.fieldsHaveBeenModified, content: messages.sureWantToForget, hasSkipCheckBox: false});
                    return dialog.show().then(
                            function(){return setResetValues()},
                            function(){Pmg.setFeedback(messages.actionCancelled);
                    });
                }
            }
            on(this, "click", function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setTimeout(self.resetDialogue, 100);
            });
        }
    });
});
