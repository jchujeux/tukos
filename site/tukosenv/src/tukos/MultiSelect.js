/*
 *  Provides a Select field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "dijit/form/MultiSelect", "dojo/json", "tukos/_WidgetsMixin"/*, "dojo/domReady!"*/], 
    function(declare, MultiSelect, JSON, _WidgetsMixin){
    return declare([MultiSelect, _WidgetsMixin], {
        postCreate: function(){
            this.inherited(arguments);    
            for (var  i in this.options){
                var opt = dojo.doc.createElement('option');
                opt.innerHTML = this.options[i];
                opt.value = i;
                this.domNode.appendChild(opt);
            }
        }
    }); 
});
