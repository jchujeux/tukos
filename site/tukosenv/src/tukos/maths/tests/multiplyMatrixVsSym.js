define(["dojo/_base/declare", "dojo/_base/lang", "tukos/maths/tests/utils", "tukos/maths/matrices"], 
function(declare, lang, testUtils, matrices){
	return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		test: function(testUtilsMatrices){
			const {dimension, matrix, sym} = JSON.parse(testUtilsMatrices);
			let a, b;
			if (dimension){
				a = testUtils.getMatrixSymetric(dimension, 10), b = matrices.sqrToSym(a);
			}else{
				a = testUtils[matrix], b = testUtils[sym];
			}
			let startTime = new Date;
			let runs = 0;
			let resultMatrix, resultSym
			do{
				resultMatrix = matrices.asyMultiply(a, a);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const firstPerSecond = (runs * 1000) / totalTime;

			startTime = new Date;
			runs = 0;
			do {
				resultSym = matrices.symMultiply(b, b);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const secondPerSecond = (runs * 1000) / totalTime;

			
			return  '<b>Cmultiply rate</b>: ' + firstPerSecond + '<b> symMultiply rate</b>: ' + secondPerSecond + '<b> ratio first / second</b>:' +  firstPerSecond / secondPerSecond;
		}
	});
});
