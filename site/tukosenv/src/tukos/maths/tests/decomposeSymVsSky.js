define(["dojo/_base/declare", "dojo/_base/lang", "tukos/maths/tests/utils", "tukos/maths/matrices"], 
function(declare, lang, testUtils, matrices){
	return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(testUtilsMatrix){
			let startTime = new Date;
			let runs = 0;
			const sym = testUtils[testUtilsMatrix]; 
			let sky;
			do{
				sky = matrices.symToSky(sym);// for the comparaison to be "fair"
				resultMatrix = matrices.symLLDecompose(sym);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const firstPerSecond = (runs * 1000) / totalTime;

			startTime = new Date;
			runs = 0;
			do {
				sky =  matrices.symToSky(sym);
				resultSky = matrices.skyLLDecompose(sky);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const secondPerSecond = (runs * 1000) / totalTime;

			return  '<b>symLLDecompose rate</b>: ' + firstPerSecond + '<b> skyLLDecompose rate</b>: ' + secondPerSecond + '<b> first / second</b>:' +  firstPerSecond / secondPerSecond;
		}
	});
});
