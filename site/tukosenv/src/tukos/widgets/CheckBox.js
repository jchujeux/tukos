define (["dojo/_base/declare", "dijit/form/CheckBox"], 
    function(declare, CheckBox){
    return declare([CheckBox], {
    	postCreate: function(){
			var checkedValue = this.value;
			this.watch('checked', function(attr, oldValue, newValue){
				this._set('value', newValue ? checkedValue : '');
			});
		},
    	_setValueAttr: function(/*String|Boolean*/ newValue, /*Boolean*/ priorityChange){
			this._set('value', newValue);
			if(this._created){
				this.set('checked', newValue ? true : false, priorityChange);
			}
		}
    }); 
});
