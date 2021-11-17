define(["dojo/_base/declare", "dijit/form/RadioButton", "tukos/evalutils"], 
    function(declare, Button, eutils){
    return declare(Button, {
        postCreate: function(){
            this.inherited(arguments);
            this.on('click', function(){
                if (this.onClickAction){
                    if(!this.onCLickFunction){
                        this.onCLickFunction = eutils.eval(this.onClickAction, 'newValue');
                    }
                }
                if (this.onCLickFunction){
                    this.onCLickFunction();
                }
            });
        }
    });
});
