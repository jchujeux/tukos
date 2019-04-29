define (["dojo/_base/declare", "dijit/form/Button", "dijit/popup", "dijit/focus", "tukos/menuUtils", "tukos/DialogConfirm", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"], 
    function(declare, Button, popup, focusUtil, mutils, DialogConfirm, Pmg, messages){
    return declare("tukos.ObjectNew", Button, {
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = self.form;
            this.on("click", function(evt){
                evt.stopPropagation();
                evt.preventDefault();

                var setNewValues = function(){
                    var dropDown = self.dropDown,
                        newAction = function(dupid){
                        	form.resetChangedWidgets();
                    		form.serverDialog({action: 'Edit', query: dupid ? {dupid: dupid} : {}}, [], form.get('dataElts'), messages.actionDone, true); 
                        	popup.close(self.dropDown);
                    	},
                    	onNewAction = function(evt){
                    		newAction();
                    	}, 
                    	onTemplateAction = function(newValue){
                    		newAction(newValue);
                    	};
                    if(dropDown){
                    	popup.open({popup: dropDown, around: self.domNode});
            			focusUtil.focus(dropDown.domNode);
                    }else{
                        when(mutils.buildMenu(mutils.newObjectMenuDescription(self.form.object, onNewAction, onTemplateAction)), function(dropDown){
                        	self.dropDown = dropDown;
                            dropDown.onBlur = function(){console.log('onBlur');popup.close(dropDown);};
                        	popup.open({popup: dropDown, around: self.domNode});
                			focusUtil.focus(dropDown.domNode);
                        });
                    	
                    }
                };
                if(!self.form.userHasChanged()){
                    setNewValues();
                }else{
                    Pmg.setFeedback('');
                    var dialog = new DialogConfirm({title: messages.fieldsHaveBeenModified, content: messages.sureWantToForget, hasSkipCheckBox: false});
                    dialog.show().then(function(){setTimeout(function(){setNewValues();}, 400)}, function(){Pmg.setFeedback(messages.newCancelled);});
                }
            });
        }
    });
});
