define (["dojo/_base/declare", "dojo/_base/lang", "dojo/on", "dojox/mobile/ToolBarButton", "dijit/registry", "tukos/PageManager", "tukos/utils"], 
function(declare, lang, on, Button, registry, Pmg, utils){
	return declare([Button],{
        postCreate: function(){
            this.inherited(arguments);
            on(this, "click", lang.hitch(function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                setTimeout(this.confirmAtts ? lang.hitch(this, this.actionDialog) : lang.hitch(this, this.action), 100);
            }));
        },
        actionDialog: function(){
        	if (this.form.hasChanged()){
        		var self = this;
        		return Pmg.confirm(this.confirmAtts).then(
        			function(){
        				return lang.hitch(self, self.action)();
        			},
        			function(){return false;}
        		);
        	}else{
        		lang.hitch(this, this.action)();
        	}
        },
        action: function(){
            var theId = this.isNew ? '' : this.form.valueOf('id'), label = this.get('label'), data = {}, self = this, sendToServer = this.sendToServer;
            if (sendToServer){
            	sendToServer.forEach(lang.hitch(this, function(toSend){
            		this[toSend](data);
            	}));
            	if (utils.empty(data)){
            		Pmg.alert({title: Pmg.message('noChangeToSubmit'), content: Pmg.message('actionCancelled')});
            		return;
            	}
            }
            this.set('label', Pmg.loading(label));
            return this.form.serverDialog({action: this.serverAction, query: theId == '' ? {} : {id: theId}}, data, this.form.get('dataElts')).then(function(response){
            	self.set('label', label);
            });         	
        },
        changedValues: function(data){
        	lang.mixin(data, this.form.changedValues());
        },
        itemCustomization: function(data){
        	var form = this.form;
        	if (form.itemCustomization){
            	lang.setObject('custom.' + form.viewMode + '.' + form.paneMode, form.itemCustomization, data);
            	delete form.customization;
        	}
        }
    });
});
