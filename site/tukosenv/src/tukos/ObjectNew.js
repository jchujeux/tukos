define (["dojo/_base/declare", "dojo/when", "dijit/form/Button", "dijit/popup", "dijit/focus", "tukos/menuUtils", "tukos/PageManager"], 
    function(declare, when, Button, popup, focusUtil, mutils, Pmg){
    return declare(Button, {
        postCreate: function(){
            this.inherited(arguments);
            var self = this, form = self.form, handle;
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
                    	setTimeout(function(){
                        	popup.open({popup: dropDown, around: self.domNode});
                			focusUtil.focus(dropDown.domNode);
                    	}, 300);
                    }else{
                        when(mutils.buildMenu(mutils.newObjectMenuDescription(self.form.object, onNewAction, onTemplateAction)), function(dropDown){
                        	self.dropDown = dropDown;
                            handle = dropDown.on('blur', function(){popup.close(dropDown);});
                        	setTimeout(function(){
                                popup.open({popup: dropDown, around: self.domNode});
                    			focusUtil.focus(dropDown.domNode);                        		
                        	}, 300);
                        });
                    	
                    }
                };
                form.checkChangesDialog(setNewValues);
/*
                if (form.userHasChanged()){
                    Pmg.confirmForgetChanges(changes).then(
                        function(){setNewValues(true);},
                        function(){Pmg.setFeedback(Pmg.message('actionCancelled'));}
                    );
                }else{
                	setNewValues();
                }
*/
            });
        }
    });
});
