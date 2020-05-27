define (["dojo/_base/declare", "dojo/on", "dojo/aspect", "dijit/registry", "dojoFixes/dojox/form/Uploader","tukos/evalutils",  "tukos/PageManager"],
    function(declare, on, aspect, registry, Uploader, eutils, Pmg){
    return declare(Uploader, {
        postCreate: function(){
            var self = this, form = this.form;
            on(this, 'complete', function(response){
                if (response.feedback){Pmg.addFeedback(response.feedback);}
            	if (response.outcome === 'success'){
                    Pmg.addExtendedIdsToCache(response.extendedIds);
                    Pmg.addMessagesToCache(response.messages, form.object);
                    Pmg.addExtrasToCache(response.extras);
                    if (this.onCompleteAction){
                        this.onCompleteFunction = this.onCompleteFunction || eutils.eval(this.onCompleteAction, 'data');
                        this.onCompleteFunction(response.data);
                    }else{
                        form.serverDialog({action: 'Reset'}, [], [], Pmg.message('actionDone')).then(function(response){
                        	if (self.grid){
                        		grid = form.getWidget(self.grid)
                            	grid.set('collection', grid.store.filter({contextpathid: grid.form.tabContextId()}));
                        	}
                            Pmg.addFeedback(Pmg.message('actionDone'));
                        });
                    }
            	}else{
            		Pmg.alert({title: Pmg.message('failedimport'), content: response.feedback[0]});
            	}
            	this.set('label', this.keepLabel);
            });            		
            aspect.after(this, 'onChange', function(){
                var valuesToSend = {};
                this.keepLabel = this.label;
                this.set('label', Pmg.loading(this.label));
                this.url = Pmg.requestUrl({object: form.object, view: form.viewMode, mode: form.paneMode, action: self.serverAction, query: {/*id: registry.byId(form.id + 'id').get('value'), */params: self.queryParams}});
            	if (self.includeWidgets){
                	self.includeWidgets.forEach(function(widget){
                		valuesToSend[widget] = form.valueOf(widget);
                	});
                }
            	self.upload(valuesToSend);
            });
        }
    }); 
});
