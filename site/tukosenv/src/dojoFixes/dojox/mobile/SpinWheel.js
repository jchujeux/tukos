define([
	"dojo/_base/declare",
	"dojox/mobile/_PickerBase"
], function(declare, PickerBase){

	return declare(PickerBase, {
		// summary:
		//		A value picker widget that has spin wheels.
		// description:
		//		SpinWheel is a value picker component. It is a sectioned wheel
		//		that can be used to pick up some values from the wheel slots by
		//		spinning them.

		/* internal properties */	
		baseClass: "mblSpinWheel",

		startup: function(){
			if(this._started){ return; }
			this.centerPos = Math.round(this.domNode.offsetHeight / 2);
			this.inherited(arguments);
		},

		resize: function() {
			this.centerPos = Math.round(this.domNode.offsetHeight / 2);
			this.getChildren().forEach(function(child){
				child.resize && child.resize();
			});
		},

		addChild: function(/*Widget*/ widget){
			this.inherited(arguments);
			if(this._started){
				widget.setInitialValue();
			}
		}
	});
});
