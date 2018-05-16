define(["dojo/_base/declare", "dijit/form/NumberTextBox"], 
    function(declare, NumberTextBox){
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
        }
    });
});
