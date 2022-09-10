define(["dojo/_base/declare", "dojo/on", "dijit/form/RadioButton", "tukos/evalutils"], 
    function(declare, on, RadioButton, eutils){
    return declare(RadioButton, {
        postCreate: function(){
            const self = this;
            this.inherited(arguments);
            on(this.focusNode, 'click', function(){
                if (self.onClickAction){
                    if(!self.onCLickFunction){
                        self.onCLickFunction = eutils.eval(self.onClickAction, 'newValue');
                    }
                }
                if (self.onCLickFunction){
                    self.onCLickFunction();
                }
            });
        }
    });
});
