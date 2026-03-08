define(["dojo/_base/declare", "dojo/_base/lang", "dojo/string", "tukos/utils", "tukos/mathUtils", "tukos/PageManager"], 
function(declare, lang, string, utils, mathUtils, Pmg){
	    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(){
			const pivot = utils.vector(10, (v,i) => i);
			return 'done';
		}
	});
});
