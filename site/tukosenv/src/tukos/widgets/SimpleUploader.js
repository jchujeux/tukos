define (["dojo/_base/declare", "dojo/on", "dojo/aspect", "dijit/registry", "dojoFixes/dojox/form/Uploader", "tukos/PageManager", "dojo/i18n!tukos/nls/messages"],
    function(declare, on, aspect, registry, Uploader, Pmg, messages){
    return declare(Uploader, {
        postCreate: function(){
            var self = this, form = this.form;
            this.url = Pmg.requestUrl({object: form.object, view: form.viewMode, mode: form.paneMode, action: self.serverAction, query: {params: self.queryParams}});
            on(this, 'complete', function(evt){
                Pmg.addFeedback(evt.feedback);
            	if (evt.outcome === 'success'){
                    form.serverDialog({action: 'reset'}, [], [], messages.actionDone).then(function(response){
                    	if (self.grid){
                    		grid = form.getWidget(self.grid)
                        	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                    	}
                        Pmg.addFeedback(messages.actionDone);
                    });
            	}else{
            		Pmg.alert({title: messages.failedimport, content: evt.feedback[0]});
            	}
            });            		
            aspect.after(this, 'onChange', function(){
                self.upload({});
            });
        }
    }); 
});
