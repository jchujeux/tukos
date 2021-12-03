define([
	"dojo/_base/declare",
	"dojo/dom-construct",
	"dijit/_WidgetBase"
], function(declare, dct, _WidgetBase){

	return declare(_WidgetBase, {
		baseClass: "mblSpinWheelSlot",

		buildRendering: function(){
			dct.create("div", {className: "mblSpinWheelSlot", id: "pt"}, this.widget.containerNode);
			dct.create("div", {className: "mblSpinWheelSlot", id: "txt", innerHTML: "."}, this.widget.containerNode);
		},
		setInitialValue: function(){
		}
	});
});
