define (["dojo/_base/declare", "dojo/on", "dijit/form/Button", "tukos/Download", "tukos/PageManager"], 
    function(declare, on, Button, download, Pmg){
    return declare([Button], {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            on(this, "click", function(evt){
                var idValue  = self.form.valueOf('id');
                if (idValue == ''){
                    Pmg.alert({title: Pmg.message('notdownloaded'), content: Pmg.message('nofileassociated')});
                }else{
                    download.download({object: 'documents', view: 'NoView', action: 'Download', query: {id: idValue}});
                }
            });
        }
    });
});
