/*
 *  tukos   mixin for dynamic widget information handling and cell rendering
 */
define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "tukos/utils", "tukos/widgetUtils"], 
    function(arrayUtil, declare, lang, domStyle, utils, wutils){
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
        _setReadOnlyAttr: function(newValue){
        	//this.inherited(arguments);
        	this.readOnly = newValue;
        	if (newValue){
        		//domStyle.set(this.domNode, 'backgroundColor', 'WhiteSmoke');
        		this.defaultBackgroundColor = this.defaultBackgroundColor || this.get('style').backgroundColor;
        		this.set('style', {backgroundColor: 'WhiteSmoke'});
        	}else{
        		//domStyle.set(this.domNode, 'backgroundColor', '');
        		if (this.defaultBackgroundColor){
        			this.set('style', {backgroundColor: this.defaultBackgroundColor});
        		}
        	}
        }
    }
});
