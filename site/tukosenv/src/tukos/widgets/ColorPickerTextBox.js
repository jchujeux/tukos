define(["dojo/_base/declare", "dojo/_base/lang", "dojo/dom-style", "tukos/widgets/DropDownTextBox", "tukos/widgets/ColorPicker"], 
function(declare, lang, domStyle, DropDownTextBox, ColorPicker){

	return declare([DropDownTextBox], {

        openDropDown: function(){
            this.dropDown = ColorPicker.colorWidget();
            this.dropDown.onChange = lang.hitch(this, this.onDropDownChange);
            this.dropDown.set('value', this.get('value'));
            dijit._HasDropDown.prototype.openDropDown.apply(this, arguments);
        },

        closeDropDown: function(focus){
        	//var node = this.dropDown.domNode;
        	this.inherited(arguments);
        	//node.parentNode.removeChild(node);
        	this.dropDown = null;
        },
        
        onDropDownChange: function(newValue){
        	console.log('the new value is: ' + newValue);
        	this.set('value', newValue);
        },

        _setValueAttr: function(value){
        	this.inherited(arguments);
        	this.set('title', value);
        	domStyle.set(this.domNode, 'backgroundColor', value);	
        },

        format: function(hex){
        	return ColorPicker.format(hex);
        },
 
        parse: function(name){
        	return ColorPicker.parse(name);
        }
    });
});
