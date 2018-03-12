define(["dojo/_base/declare", "dijit/form/NumberTextBox", "tukos/_WidgetsMixin", "dojo/json"], 
    function(declare, NumberTextBox, _WidgetsMixin, JSON){
    return declare([NumberTextBox, _WidgetsMixin], {

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
/*
        parse: function(expression, options){
            var result = this.inherited(arguments);
            console.log('parse expression:  ' + expression + ' result: ' + result + 'options: ' + JSON.stringify(options));
            return result;
        },
        format: function(number, options){
            var result = this.inherited(arguments);
            console.log('format - number: ' + number + ' result: ' +  result + 'options: ' + JSON.stringify(options));
            return result;
        },
*/
    });
});
