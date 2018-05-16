define (["dojo/_base/declare", "dojo/_base/lang", "dijit/form/MappedTextBox", "dijit/_HasDropDown", "dojo/text!dijit/form/templates/DropDownBox.html"], 
    function(declare, lang, TextBox, _HasDropDown, template){
    return declare([TextBox, _HasDropDown], {
        templateString: template,
        baseClass: "dijitTextBox dijitComboBox",
        cssStateNodes: {
                "_buttonNode": "dijitDownArrowButton"
        },
        validate: function(){
                // to fix error in dijit/form/ValidationTextBox.validate when called as an editor in dgrid
                if (this.textbox != undefined){
                    return this.inherited(arguments);
                }else{
                    return true;
                }
        },
        
        openDropDown: function(){
            if (!this.dropDown){
            	require ([this.dropDownWidget["type"]], lang.hitch(this, function(DropDown){
            		if (!this.dropDownWidget["atts"].onChange){
            			this.dropDownWidget['atts'].onChange = lang.hitch(this, this.onDropDownChange);
            		}
            		this.dropDown = new DropDown(this.dropDownWidget["atts"]);
                    this.dropDown.set('value', this.get('value'));
                    this.inherited(arguments);
            	}));
            }else{
            	this.dropDown.set('value', this.get('value'));
            	this.inherited(arguments);
            }
        },
        onDropDownChange: function(newValue){
        	console.log('the new value is: ' + newValue);
        	this.set('value', newValue);
        }

    }); 
});
