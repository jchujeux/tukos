define (["dojo/_base/declare", "dojox/mobile/ComboBox", "dojo/store/Memory"], 
    function(declare, ComboBox, Memory){
    return declare([ComboBox], {
        constructor: function(args){
            args.store= new Memory(args.storeArgs);
        },
        postCreate: function(args){
        	this.inherited(arguments);
        	if (this.noMobileKeyboard){
            	this.domNode.setAttribute('readonly', 'readonly');
        	}
        }
    }); 
});
