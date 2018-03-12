define (["dojo/_base/declare", "dojo/dom", "dojo/on", "dojo/cookie", "dijit/form/Button", "dojo/request", "dojo/request/iframe", "dijit/registry", "tukos/Download", "dojo/i18n!tukos/nls/messages", "dojo/json", "dojo/domReady!"], 
    function(declare, dom, on, cookie, Button, request, iframe, registry, download, messages, JSON){
    return declare([Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            on(this, "click", function(evt){
                var idValue  = self.form.valueOf('id');
                if (idValue == ''){
                    var dialog = new DialogConfirm({title: messages.noDownload, content: messages.downloadPrerequisites, hasSkipCheckBox: false});
                    dialog.show().then(
                        function(){self.setFeeedback(messages.resetCancelled);},
                        function(){self.setFeeedback(messages.resetCancelled);}
                    );
                }else{
                    //download.download({object: 'documents', view: 'noview', action: 'download'}, idValue);
                    download.download({object: 'documents', view: 'noview', action: 'download', query: {id: idValue}});
                }
            });
        }
    });
});
