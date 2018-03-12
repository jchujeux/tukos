define (["dojo/_base/declare", "dojo/dom-construct", "dijit/_WidgetBase", "tukos/hiutils"], 
    function(declare, dct, Widget, hiutils){
    return declare([Widget], {
        postCreate: function(){
            this.inherited(arguments);
            if (this.value){
                this.domNode.innerHTML = this.value;
            }
        },
        _setValueAttr: function(value){
                dct.empty(this.domNode);
                this.domNode.innerHTML = value;
        },
        _getValueAttr: function(){
            return this.domNode.innerHTML || '';
        }
    }); 
});
