/*
 *  tukos   mixin for dynamic widget information handling and cell rendering
 */
define (["dojo/_base/lang", "tukos/widgetUtils"], 
    function(lang, wutils){
    return {
        getAttr: function(attrName){
            return this[attrName] || this.getParent()[attrName];// widgets on a grid miss some attributes (form & formId in paticular) => if not found we look for it into its parent (e.g. a grid)
        },
        valueOf: function(name){
            return lang.hitch(this, wutils.valueOf)(name);
        },
        setValueOf:  function(name, value){
            return lang.hitch(this, wutils.setValueOf)(name, value);
        },
        setValuesOf:  function(data){
            return lang.hitch(this, wutils.setValuesOf)(data);
        },
        _setReadOnlyAttr: function(newValue){
        	this._set('readOnly', newValue);
        	if (newValue){
        		this.defaultBackgroundColor = this.defaultBackgroundColor || this.get('style').backgroundColor;
        		this.set('style', {backgroundColor: 'WhiteSmoke'});
        	}else{
        		if (this.defaultBackgroundColor){
        			this.set('style', {backgroundColor: this.defaultBackgroundColor});
        		}
        	}
        },
        getRootForm: function(){
        	return this.form.form || this.form;
        }
    }
});
