define(["dojo/_base/declare", "dojox/mobile/View", "dojox/gesture/swipe", "tukos/PageManager"], function(declare, View, swipe, Pmg){
return declare ([View], {
		buildRendering: function(){
			this.inherited(arguments);
		  	swipe.end(this.domNode, function(e){
				  console.log('this is end of swipe');
				  e.dx > 20 ? Pmg.mobileViews.selectPreviousPane() : (e.dx < -20 ? Pmg.mobileViews.selectNextPane() : null);
			  });
		}
	});
});
