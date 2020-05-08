define(["dojo/_base/declare", "dijit/form/Button", "tukos/evalutils"], 
    function(declare, Button, eutils){
    return declare(Button, {
        postCreate: function(){
            this.inherited(arguments);
            this.on('click', function(evt){
                evt.stopPropagation();
                evt.preventDefault();
                if (this.onClickAction){
                    if(!this.onClickFunction){
                        this.onClickFunction = this.onClickFunction || eutils.eval(this.onClickAction, 'evt');
                    }
                    this.onClickFunction(evt);
                }
            });
        }
    });
});
