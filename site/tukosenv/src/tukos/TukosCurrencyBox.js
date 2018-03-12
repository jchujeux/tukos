define(["dojo/_base/declare", "dijit/form/CurrencyTextBox", "tukos/_WidgetsMixin", "dojo/json"], 
    function(declare, CurrencyTextBox, _WidgetsMixin, JSON){
    return declare([CurrencyTextBox, _WidgetsMixin], {

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
