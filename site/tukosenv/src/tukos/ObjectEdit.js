define (["dojo/_base/declare", "dojo/_base/lang", "tukos/ObjectSelect", "dijit/registry", "dijit/focus", "tukos/DialogConfirm", "tukos/PageManager", "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, ObjectSelect, registry, focusUtil, DialogConfirm, Pmg, messages){
    return declare(ObjectSelect, {
        postCreate: function(args){
            this.inherited(arguments);
            var form = registry.byId(this.form.id);
            this.on("change", function(newValue){
                var self = this;
                var item = this.item

                if (! (item.id == '')){// this test is needed due to fact that this.reset() fires the 'change' event again :-(.
                    var setEditValues = function(){
                        form.serverDialog(lang.mixin(self.urlArgs || {action: 'edit'}, {query: {id: item.id}}), [], self.form.get('dataElts'), messages.actionDone); 
                    }
                    if(!form.hasChanged()){
                        setEditValues();
                    }else{
                        Pmg.setFeedback(' ');
                        var dialog = new DialogConfirm({title: messages.fieldsHaveBeenModified, content: messages.sureWantToForget, hasSkipCheckBox: false});
                        dialog.show().then(function(){setEditValues();}, function(){Pmg.setFeedback(messages.actionCancelled);});
                    }
                    this.reset();
                    //focusUtil.curNode && focusUtil.curNode.blur();
                }
            });
        }
    });
});
