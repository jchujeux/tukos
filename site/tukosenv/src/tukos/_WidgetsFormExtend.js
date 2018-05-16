define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/dom-attr", "tukos/widgetUtils"], 
    function(arrayUtil, declare, lang, domAttr, domStyle, wutils){
    return {

		_setReadOnlyAttr: function(/*Boolean*/ newValue){
			domAttr.set(this.focusNode, 'readOnly', newValue);
			this._set("readOnly", newValue);
        	if (newValue){
        		this.defaultBackgroundColor = this.defaultBackgroundColor || this.get('style').backgroundColor;
        		this.set('style', {backgroundColor: 'WhiteSmoke'});
        	}else{
        		if (this.defaultBackgroundColor){
        			this.set('style', {backgroundColor: this.defaultBackgroundColor});
        		}
        	}
		}
    }
});
