define(["dojo/_base/declare", "dojo/dom-style", "dijit/ColorPalette", "dijit/form/Button", "tukos/PageManager"], 
function(declare, dst, ColorPalette, Button, Pmg){

	return declare([ColorPalette], {
		 templateString:
			 '<div class="dijitInline dijitColorPalette" role="grid">'+
			 	'<table dojoAttachPoint="paletteTableNode" class="dijitPaletteTable" cellSpacing="0" cellPadding="0" role="presentation"><tbody data-dojo-attach-point="gridNode"</tbody></table>' +
			 	'<table><tbody><tr><td><div data-dojo-attach-point="removeNode"></div></td></tr></tbody></table>' +
			 '</div>',
		postCreate: function(){
			this.removeButton = new Button({label: Pmg.message('remove'), onClick: this.onRemove});
			this.removeButton.placeAt(this.removeNode);
		},
		_setValueAttr: function(value){
			this.inherited(arguments);
			dst.set(this.removeNode, 'display', value ? '' : 'none');
		}
    });
});
