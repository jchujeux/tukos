define (["dojo/_base/declare", "dijit/form/MultiSelect"], 
    function(declare, MultiSelect){
    return declare([MultiSelect], {
        postCreate: function(){
            var valueToRestore = this.value;
        	this.inherited(arguments);
            for (var  i in this.options){
                var opt = dojo.doc.createElement('option');
                opt.innerHTML = this.options[i];
                opt.value = i;
                this.domNode.appendChild(opt);
            }
            this.set('value', valueToRestore);
        }
    }); 
});
