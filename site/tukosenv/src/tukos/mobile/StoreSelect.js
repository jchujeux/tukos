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
        },
        _setValueAttr: function(value, priorityChange, displayedValue, item){
        	console.log('mobile StoreSeelct setValueAttr - vaalue: ' + value);
        	if (typeof item === "undefined"){
        		item = this.store.get(value);
        		value = item.name;
        		this.inherited(arguments);
        	}else{
            	this.inherited(arguments);
        	}
        },
        _getValueAttr: function(){
        	var item = this.get('item');
        	return item ? item.id : this.inherited(arguments);
        }
    }); 
});
