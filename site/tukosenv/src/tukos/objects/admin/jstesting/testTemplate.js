define(["dojo/_base/declare", "dojo/_base/lang"], 
function(declare, lang){
	    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(){
			/* implement here your testing procedure*/
			return 'done';
		}
	});
});
