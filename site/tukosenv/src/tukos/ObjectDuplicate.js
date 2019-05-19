define (["dojo/_base/declare", "dojo/_base/lang", "dojo/dom", "dojo/promise/all",  "dijit/form/Button", "dijit/registry", "tukos/PageManager", "tukos/utils"], 
    function(declare, lang, dom, all, Button, registry, Pmg, utils){
    return declare([Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = self.form;
            this.on("click", function(evt){
                var idValue  = form.valueOf('id');
                if (idValue == ''){
                    Pmg.alert({title: Pmg.message('newNoDuplicate'), content: Pmg.message('mustSaveFirst')});
                }else{
                    evt.stopPropagation();
                    evt.preventDefault();
                    var setDuplicate = function(){
                        var changedValues = form.changedValues();
                        form.resetChangedWidgets();
                        form.serverDialog({action: (self.urlArgs && self.urlArgs.action ? self.urlArgs.action : 'Edit'), query: {dupid: idValue}}, [], form.get('dataElts'), Pmg.message('actionDone'), true).then(
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
                        Pmg.confirm({title: Pmg.message('fieldsHaveBeenModified'), content: Pmg.message('wantToSaveBefore')}).then(
                        		function(){setDuplicate();}, 
                        		function(){Pmg.setFeedback(Pmg.message('actionCancelled'));}
                        );
                    }
                }
            });
        }
    });
});
