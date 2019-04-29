define(["dojo/_base/declare", "dojo/dom-construct", "dojox/mobile/FormLayout", "dojox/mobile/TextBox", "dojox/mobile/Button", "tukos/widgets/WidgetsLoader"], 
    function(declare, dct, FormLayout, TextBox, Button, widgetsLoader){
    return declare(FormLayout, {
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            this.rows.forEach(function(row){
            	var rowLayout = dct.create('div', null, self.domNode), rowLabel = dct.create('label', row.label, rowLayout), rowFieldSet = dct.create('fieldset', null, rowLayout);

            	dojo.when(widgetsLoader.instantiate(row.widget.type, row.widget.atts), function(theWidget){
            		rowFieldSet.appendChild(theWidget.domNode);
            	});
            });
        }
    });
});
