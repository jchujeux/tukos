define(["dojo/_base/declare", "dojo/_base/lang", "dojo/_base/Color", "dojo/dom-style", "tukos/widgets/ColorPalette", "dijit/form/DropDownButton", "tukos/PageManager"], 
function(declare, lang, Color, dst, ColorPalette, DropDownButton, Pmg){
	var color = new Color();
	return declare([DropDownButton], {
		constructor: function(){
			this.iconClass = "dijitEditorIcon dijitEditorIconHiliteColor";
		},
		loadDropDown: function(callback){
			var onChangeColor = lang.hitch(this, this.onChangeColor),
				myColorPalette = this.dropDown = new ColorPalette(
					{onChange: function(newColor){onChangeColor(newColor);}, onBlur: function(){dijit.popup.close(this);}, onRemove: function(){onChangeColor('');}});
			myColorPalette.startup();
			callback();
		},
		onChangeColor: function(newColor){
			this.set('value', newColor);
			this.closeDropDown(true);
		},
		openDropDown: function(){
			var currentColor = this.get('value');
			this.dropDown.set('value', currentColor ? color.setColor(currentColor).toHex() : '');				
			this.inherited(arguments);
		},
		_setValueAttr: function(value){
			this._set('value', value);
			dst.set(this.iconNode, 'backgroundColor', value);
		}
    });
});
