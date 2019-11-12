define(["dojo/_base/declare", "dijit/form/TimeTextBox", "tukos/utils"], 
    function(declare, TimeTextBox, utils){
    return declare([TimeTextBox], {

        openDropDown: function(callback){
        	this.inOpenDropDown = true;
        	this.inherited(arguments);
        	this.inOpenDropDown = false;
        },
        _getValueAttr: function(){
        	var result = this.inherited(arguments);
        	return (result && !this.inOpenDropDown) ? dojo.date.stamp.toISOString(result, {selector: 'time'}) : result;
        }
    });
});
