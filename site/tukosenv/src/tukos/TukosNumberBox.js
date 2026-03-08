define(["dojo/_base/declare", "dijit/form/NumberTextBox", "dojox/string/sprintf"], 
    function(declare, NumberTextBox, sprintf){
    return declare([NumberTextBox], {

        _setValueAttr: function(value){
           if (typeof value === 'string' && value !== ''){
                value = Number(value);
            }else if (isNaN(value)){
                value = '';
            }
           this.inherited(arguments);
        },
        _getValueAttr: function(){
            var value = this.inherited(arguments);
            return isNaN(value) ? '' : value;
        },
		format: function(value){
			return  this.constraints.type === 'scientific' ? value.toExponential() : this.inherited(arguments);
		},
		parse: function(value, constraints){
			return constraints.type === 'scientific' ? parseFloat(value) : this.inherited(arguments);
		},
		isValid: function(){
			return true;
		},
		filter: function(/*Number*/ value){
			return value;
		}
    });
});
