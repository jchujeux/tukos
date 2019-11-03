define (["dojo/_base/declare", "dojo/when", "dijit/form/Button", "dijit/popup", "dijit/focus", "tukos/menuUtils", "tukos/PageManager"], 
    function(declare, when, Button, popup, focusUtil, mutils, Pmg){
    return declare(Button, {
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
                    		form.serverDialog({action: 'Edit', query: dupid ? {dupid: dupid} : {}}, [], form.get('dataElts'), Pmg.message('actionDone'), true); 
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
                            dropDown.on('blur', function(){popup.close(dropDown);});
                        	popup.open({popup: dropDown, around: self.domNode});
                			focusUtil.focus(dropDown.domNode);
                        });
                    	
                    }
                };
                if(!self.form.userHasChanged()){
                    setNewValues();
                }else{
                    Pmg.setFeedback('');
                    Pmg.confirmForgetChanges().then(
                    		function(){setTimeout(function(){setNewValues();}, 400)}, 
                    		function(){Pmg.setFeedback(Pmg.message('actionCancelled'));}
                    );
                }
            });
        }
    });
});
