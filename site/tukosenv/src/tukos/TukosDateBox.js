define(["dojo/_base/declare", "dijit/form/DateTextBox"], 
    function(declare, DateTextBox){
    return declare([DateTextBox], {

        _getValueAttr: function(){
            var date = this.inherited(arguments);
            return (date ? dojo.date.stamp.toISOString(date, {selector: "date"}) : '');
        },

        _setValueAttr: function(value){
            if (value === ''){
                value = null;
            }
            this.inherited(arguments);
        }

    });
});
