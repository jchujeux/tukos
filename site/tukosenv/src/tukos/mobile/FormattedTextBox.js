define(["dojo/_base/declare", "dojox/mobile/TextBox", "tukos/utils"], 
    function(declare, TextBox, utils){
    return declare([TextBox], {

        _setValueAttr: function(value){
            value = utils.transform(value, this.formatType, this.formatOptions);
            this.inherited(arguments);
        },
        _getValueAttr: function(value){
            return utils.unTransform(this.inherited(arguments), this.formatType);
        }
    });
});
