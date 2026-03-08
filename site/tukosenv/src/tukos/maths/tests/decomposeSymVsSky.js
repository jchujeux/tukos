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
			let resultMatrix, resultSky, resultLDLSky, sky;
			do{
				sky = matrices.symToSky(sym);// for the comparaison to be "fair"
				resultMatrix = matrices.symDecompose(sym);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const firstPerSecond = (runs * 1000) / totalTime;

			startTime = new Date;
			runs = 0;
			do {
				sky =  matrices.symToSky(sym);
				resultSky = matrices.skyDecompose(sky);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const secondPerSecond = (runs * 1000) / totalTime;

			sky =  matrices.symToSky(sym);
			resultLDLSky = matrices.skyLDLDecompose(sky);
			sky = matrices.symToSky(sym);
			const resultColsolSky = matrices.skyColsolDecompose(sky);
			const skyD = {diagIndexes: [], upper: []};
			const skyDColsol = {diagIndexes: [], upper: []};
			let diagIndex = 0;
			for (r = 1; r <= resultLDLSky.diagIndexes.length; r++){
				skyD.upper.push(Math.sqrt(resultLDLSky.upper[diagIndex]));
				resultLDLSky.upper[diagIndex] = 1.0;
				skyD.diagIndexes[r-1] = r;
				diagIndex = resultLDLSky.diagIndexes[r-1];
			}
			diagIndex = 0;
			for (r = 1; r <= resultColsolSky.diagIndexes.length; r++){
				skyDColsol.upper.push(Math.sqrt(resultColsolSky.upper[diagIndex]));
				resultColsolSky.upper[diagIndex] = 1.0;
				skyDColsol.diagIndexes[r-1] = r;
				diagIndex = resultColsolSky.diagIndexes[r-1];
			}
			const LD = matrices.skyMultiply(skyD, resultLDLSky, true);
			const LDColsol = matrices.skyMultiply(skyDColsol, resultColsolSky, true);
			
			return  '<b>symDecompose rate</b>: ' + firstPerSecond + '<b> skyDecompose rate</b>: ' + secondPerSecond + '<b> first / second</b>:' +  firstPerSecond / secondPerSecond;
		}
	});
});
