define (["dojo/_base/declare", "dijit/form/CheckBox"], 
    function(declare, CheckBox){
    return declare([CheckBox], {
		_setValueAttr: function(/*String|Boolean*/ newValue, /*Boolean*/ priorityChange){
			this._set('value', newValue);
			if(this._created){
				this.set('checked', newValue ? true : false, priorityChange);
			}
		}
    }); 
});
