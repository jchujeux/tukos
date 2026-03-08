define(["dojo/_base/declare", "dojo/_base/lang", "tukos/maths/tests/utils", "tukos/maths/matrices"], 
function(declare, lang, testUtils, matrices){
	return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(testUtilsMatrix){
			let startTime = new Date;
			let runs = 0;
			const a = testUtils[testUtilsMatrix] 
			let inverse1, determinant1, inverse2, determinant2;
			do{
				let lup;
				determinant1 = void(0);
				inverse1 = matrices.LUPInverse(matrices.sqrDecompose(lang.clone(a)));
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const firstPerSecond = (runs * 1000) / totalTime;

			startTime = new Date;
			runs = 0;
			do {
				determinant2 = void(0);
				inverse2 = matrices.sqrInverse(a, determinant2);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const secondPerSecond = (runs * 1000) / totalTime;

			
			return  '<b>matrices.inversion & determinant rate</b>: ' + firstPerSecond + '<b> matrices.inverse3by3</b>: ' + secondPerSecond + '<b> ratio first / second</b>:' +  firstPerSecond / secondPerSecond;
		}
	});
});
