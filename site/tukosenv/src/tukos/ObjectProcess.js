define (["dojo/_base/declare", "dojo/_base/lang", "dijit/form/Button", "dijit/registry", "tukos/evalutils", "tukos/PageManager"], 
    function(declare, lang, Button, registry, eutils, Pmg){
    return declare([Button], {
        postCreate: function(args){
            this.inherited(arguments);
            var self = this, form = this.form;
            this.on("click", function(evt){
                evt.stopPropagation();
                evt.preventDefault();

                setTimeout(function(){
                    if (lang.hitch(self, self.processCondition)()){
                        self.valuesToSend = {};
                        (self.includeWidgets || []).forEach(function(widgetName){
                        	self.valuesToSend[widgetName] = form.valueOf(widgetName);
                        });
                        if (self.allowSave){
                            self.valuesToSend = lang.mixin(self.valuesToSend, form.changedValues());
                        }
                        theId = registry.byId(self.form.id + 'id').get('value');
                        if (self.allowSave || (theId != '' && !self.needToSaveBeforeProcess())){
                            if (self.dialogDescription){
                                if (self.tooltipDialog){
                                    self.tooltipDialog.open({around: self.domNode});
                                }else{
                                    require(["tukos/TukosTooltipDialog"], function(TukosTooltipDialog){
                                        self.dialogDescription.paneDescription.attachedWidget = self;
                                        self.dialogDescription.paneDescription.form = self.form;
                                        self.dialogDescription.paneDescription.tabContextId = lang.hitch(self.form, self.form.tabContextId);
                                        self.tooltipDialog = new TukosTooltipDialog(self.dialogDescription);
                                        self.tooltipDialog.open({around: self.domNode});
                                    });
                                }
                            }else{
                                self.doProcess(theId, self.urlArgs);
                            }
                        }else{
                            Pmg.alert({title: Pmg.message('newOrFieldsModified'), content: Pmg.message('saveOrReloadFirst')});
                        }
                    }                	
                }, 100);
            });
        },
        
        doProcess: function(theId, urlArgs){
            Pmg.setFeedback(Pmg.message('actionDoing'));
            this.form.serverDialog({action:(urlArgs && urlArgs.action ? urlArgs.action : 'Process'), query:urlArgs ? lang.mixin({id: theId}, urlArgs.query) : {id: theId}}, this.valuesToSend, this.form.get('postElts'),
            	Pmg.message('actionDone')); 
        },

        needToSaveBeforeProcess: function(){
            for (widgetName in this.form.changedWidgets){
                if (!this.noSaveBeforeProcess ||! this.noSaveBeforeProcess[widgetName]){
                    return true;
                }
            }
            return false;
        },

        processCondition: function(){
            if (this.conditionDescription){
                if (!this.conditionFunction){
                    var myEval = lang.hitch(this, eutils.eval);
                    this.conditionFunction = myEval(this.conditionDescription, '');
                }
                return this.conditionFunction();
            }else{
                return true;
            }
        }
    });
});
