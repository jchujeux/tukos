define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom", "dojo/on", "dojo/promise/all",  "dijit/form/Button", "dijit/registry", "tukos/PageManager", "tukos/DialogConfirm", "tukos/utils",
         "dojo/i18n!tukos/nls/messages", "dojo/domReady!"], 
    function(declare, lang, dom, on, all, Button, registry, Pmg, DialogConfirm, utils, messages){
    return declare([Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = self.form;
            on(this, "click", function(evt){
                var idValue  = form.valueOf('id');
                if (idValue == ''){

                    var dialog = new DialogConfirm({title: messages.newNoDuplicate, content: messages.mustSaveFirst, hasSkipCheckBox: false});
                    dialog.show().then(
                        function(){form.setFeeedback(messages.actionCancelled);},/* user pressed OK    : no action */
                        function(){form.setFeeedback(messages.actionCancelled);});/* user pressed Cancel: no action */
                }else{
                    evt.stopPropagation();
                    evt.preventDefault();

                    var setDuplicate = function(){
                        var changedValues = form.changedValues();
                        form.resetChangedWidgets();
                        form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Edit'), query: {dupid: idValue}}, [], form.get('dataElts'), messages.actionDone, true).then(
                            function(response){
                                form.setValueOf('id', '');
                                if (response.itemCustomization){
                                	form.customization = response.itemCustomization;
                                	utils.forEach(response.itemCustomization.widgetsDescription || {}, function(widgetCustomization, widgetName){
                                		var widget = form.getWidget(widgetName);
                                		if (widget.itemCustomization){
                                			lang.setObject('itemCustomization.widgetsDescription.' + widgetName, utils.mergeRecursive(((form.itemCustomization || {}).widgetsDescription || {})[widgetName], widgetCustomization), form);
                                		}
                                	});
                                }
                                all(changedValues).then(function(changedValues){
                                    for (widgetName in changedValues){
                                        if (widgetName !== 'id' && widgetName != 'updated'){
                                            form.setValueOf(widgetName, changedValues[widgetName]);
                                        }
                                    }
                                });
                            }
                        );
                    };
                    if(!form.hasChanged()){
                        setDuplicate();
                    }else{
                        Pmg.setFeedback('');
                        var dialog = new DialogConfirm({title: messages.fieldsHaveBeenModified, content: messages.wantToSaveBefore, hasSkipCheckBox: false});
                        dialog.show().then(function(){setDuplicate();}, function(){Pmg.setFeedback(messages.actionCancelled);});
                    }
                }
            });
        }
    });
});
