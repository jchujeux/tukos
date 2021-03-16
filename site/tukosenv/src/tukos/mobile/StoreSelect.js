define (["dojo/_base/declare", "dojox/mobile/ComboBox", "dojo/store/Memory"], 
    function(declare, ComboBox, Memory){
    return declare([ComboBox], {
        constructor: function(args){
            args.store= new Memory(args.storeArgs);
        },
        postCreate: function(args){
        	this.inherited(arguments);
        	if (!this.showMobileKeyboard){
            	this.domNode.setAttribute('readonly', true);
        	}
        	this.item = this.store.get('');
        },
         _setValueAttr: function(value,  priorityChange,  displayedValue,  item){
            if (item === undefined/* && displayedValue === undefined && (value === null || value === '')*/){
                item = this.store.get(value);
                this.inherited(arguments, [item.name, priorityChange, displayedValue, item]);
            }else{
                return this.inherited(arguments);
            }
        },
        _getServerValueAttr: function(){
        	return this.item.id;
        }
    }); 
});
