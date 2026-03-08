define(["dojo/_base/declare", "dojo/_base/lang"], 
function(declare, lang){
	    return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(){
			let startTime = new Date;
			let runs = 0;
			do{
				/* here put your first scenario*/
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const firstPerSecond = (runs * 1000) / totalTime;

			startTime = new Date;
			do {
				/* here put your second scenario*/
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const secondPerSecond = (runs * 1000) / totalTime;

			
			return  '<b>First per seconds</b>: ' + firstPerSecond + '<b> Second per second</b>: ' + secondPerSecond + '<b> ratio first / second</b>:' +  firstPerSecond / secondPerSecond;
		}
	});
});
