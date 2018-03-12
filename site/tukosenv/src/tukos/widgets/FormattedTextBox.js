define(["dojo/_base/declare", "dijit/form/TextBox", "tukos/utils", "tukos/_WidgetsMixin"], 
    function(declare, TextBox, utils, _WidgetsMixin){
    return declare([TextBox, _WidgetsMixin], {

        _setValueAttr: function(value){
            value = utils.transform(value, this.formatType, this.formatOptions);
            this.inherited(arguments);
        },
        _getValueAttr: function(value){
            return utils.unTransform(this.inherited(arguments), this.formatType);
        }
    });
});
