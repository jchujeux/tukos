/*
 *  Provides a Select field which data store is args.storeData, callable from ObjectPane.js
 *   
 */
define (["dojo/_base/declare", "dijit/form/ComboBox", "dojo/store/Memory",  "tukos/_WidgetsMixin"], 
    function(declare, ComboBox, Memory, _WidgetsMixin){
    return declare([ComboBox, _WidgetsMixin], {
        constructor: function(args){
            args.store= new Memory(args.storeArgs);
        }
    }); 
});
