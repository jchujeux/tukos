define (["dojo/_base/declare", "dojo/ready", "dojo/dom-construct", "dijit/popup", "dijit/TooltipDialog", "dijit/form/Button", "tukos/PageManager", "tukos/widgets/WidgetsLoader",
         "dojo/i18n!tukos/nls/messages"], 
    function( declare, ready, dct, popup, TooltipDialog, Button, Pmg, widgetsLoader, messages){
    return declare(null, {
        onChangeContextCallback: function(newValue){
            console.log('context has changed to value: ' + newValue);
        },
        contextMenuCallback: function(item, evt){
            evt.preventDefault();
            evt.stopPropagation();
            this.openCustomContextDialog(item);
        },
        openCustomContextDialog: function(item){
            var customContextDialog = new TooltipDialog({});                                                              
            customContextDialog.set('content', this.customContextDialogContent(item));
            item.customDialog = customContextDialog;
            ready(function(){
                popup.open({parent: item, popup: customContextDialog, around: item.domNode});
            });
        },
        
        customContextDialogContent: function(item){
            var self = this;
            var contentTable = dct.create('table', null);
            var customValuesTr = dct.create('tr', null, contentTable);
                var customContextTd = dct.create('td', {style: 'vertical-align:top;text-align:center'} , customValuesTr);
                    var contextTitle = dct.create('div', {innerHTML: '<b> default context for ' + item.label + ': </b>'}, customContextTd);
                    dojo.when(
                    	widgetsLoader.instantiate(
                    		'ObjectSelectDropDown', 
                    		{label: 'Context', style: {width: '12em'}, table: 'contexts', dropDownWidget: {type: 'StoreTree', atts: Pmg.cache.contextTreeAtts}, onChange: this.onChangeContextCallback}),
                    	function(contextSelect){
                            if (item.context){
                                contextSelect.set('value', item.context);
                            }
                            item.contextSelect = contextSelect;
                            customContextTd.appendChild(contextSelect.domNode);
                    		
                    });

            var actionTr = dct.create('tr', {style: 'vertical-align:top;text-align: center;'}, contentTable);
            var actionTd = dct.create('td', null, actionTr);
            var saveButton = new Button({label: messages.save, onClick: function(evt){
                    self.saveCustomContext(item);
                    popup.close(self.customDialog);
                }
            });
            actionTd.appendChild(saveButton.domNode);

            var cancelButton = new Button({label: messages.cancel, onClick: function(evt){
                    self.cancelCustomContext(item);
                    popup.close(self.customDialog);
                }
            });
            actionTd.appendChild(cancelButton.domNode);
            var resetButton = new Button({label: messages.reset, onClick: function(evt){
                    self.resetCustomContext(item);
                    popup.close(self.customDialog);
                }
            });
            actionTd.appendChild(resetButton.domNode);
            return contentTable;
        },
        saveCustomContext: function(item){
            var self = this;
            Pmg.serverDialog({object: 'users', view: 'NoView', mode: 'Tab', action: 'ModuleContextSave'}, {data: {module: item.moduleName, contextid: item.contextSelect.get('value')}}, messages.actionDone).then(
                function(response){
                    item.context = response.contextid;
                }
            );
        },
        cancelCustomContext: function(item){
            Pmg.setFeedback(messages.actionCancelled);
        },
        resetCustomContext: function(item){
            item.contextSelect.set('value', '');
            //this.saveCustomContext(item);
        }
    });
});
