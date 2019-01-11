define (["dojo/_base/declare", "dijit/form/ComboBox", "dojo/store/Memory"], 
    function(declare, ComboBox, Memory){
    return declare([ComboBox], {
        constructor: function(args){
            args.store= new Memory(args.storeArgs);
        }
    }); 
});
