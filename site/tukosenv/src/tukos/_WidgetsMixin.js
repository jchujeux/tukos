/*
 *  tukos   mixin for dynamic widget information handling and cell rendering
 */
define (["dojo/_base/array", "dojo/_base/declare", "dojo/_base/lang", "tukos/utils", "tukos/widgetUtils"], 
    function(arrayUtil, declare, lang, utils, wutils){
    return declare(null, {

        getAttr: function(attrName){
            return this[attrName] || this.getParent()[attrName];// widgets on a grid miss some attributes (form & formId in paticular) => if not found we look for it into its parent (e.g. a grid)
        },
        valueOf: function(name){
            return lang.hitch(this, wutils.valueOf)(name);
        },

        setValueOf:  function(name, value){
            return lang.hitch(this, wutils.setValueOf)(name, value);
        }
    });
});
