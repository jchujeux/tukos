define(["dojo/_base/declare", "dijit/form/DateTextBox", "tukos/_WidgetsMixin"], 
    function(declare, DateTextBox, _WidgetsMixin){
    return declare([DateTextBox, _WidgetsMixin], {

        _getValueAttr: function(){
            var date = this.inherited(arguments);
            //return (date ? dojo.date.stamp.toISOString(date, {selector: "date"}) : '0000-00-00');
            return (date ? dojo.date.stamp.toISOString(date, {selector: "date"}) : '');
        },

        _setValueAttr: function(value){
            //if (value === null){
                //value = '';
            if (value === ''){
                value = null;
            }
            this.inherited(arguments);
        }

    });
});
