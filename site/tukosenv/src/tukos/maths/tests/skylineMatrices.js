define(["dojo/_base/declare", "dojo/_base/lang", "tukos/maths/matrices"], 
function(declare, lang, matrices){
	return declare(null, {
		constructor: function(args){
			lang.mixin(this, args);
		},
		testATSkyA: function(){
			const symmetric = [
				[2.0, 1.5, 1.5, 0.0, 0.0, 0.0],
				[1.5, 2.0, 1.5, 0.0, 0.0, 0.0],
				[1.5, 1.5, 2.0, 0.0, 0.0, 0.0],
				[0.0, 0.0, 0.0, 0.8, 0.0, 0.0],
				[0.0, 0.0, 0.0, 0.0, 0.8, 0.0],
				[0.0, 0.0, 0.0, 0.0, 0.0, 0.8]
			],
			a = [
				[2.0, 1.5, 1.5, 1.2, 1.1, 0.0, 1.2, 1.1, 0.0],
				[1.0, 1.5, 1.5, 0.0, 0.0, 1.2, 1.2, 1.2, 1.0],
				[2.0, 1.5, 1.5, 0.0, 0.0, 0.6, 1.2, 1.1, 0.0],
				[3.0, 1.5, 1.5, 0.0, 0.0, 0.7, 1.2, 1.1, 0.0],
				[4.0, 1.5, 1.5, 0.0, 0.0, 0.8, 1.3, 1.1, 0.0],
				[1.0, 1.5, 1.5, 0.0, 0.5, 0.2, 1.2, 1.1, 2.0],				
			];
			const symSky = matrices.symToSky(symmetric);

			let startTime = new Date, skyResult;
			let runs = 0;
			do{
				skyResult = matrices.aTSkyA(a, symSky);
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const skysPerSecond = (runs * 1000) / totalTime;

			startTime = new Date;
			runs = 0;
			let results;
			do {
				results = [];
				const aCols = a[0].length, symDimension = symmetric.length;
				for (let i = 0; i < aCols; i++){
					const row = [];
					for (let j = 0; j < aCols; j++){
						let ijResult = 0.0;
						for (let k = 0; k < symDimension; k++){
							let kiResult = 0.0;
							for (let l = 0; l < symDimension; l++){
								kiResult += symmetric[k][l] * a[l][j];
							}
							ijResult += a[k][i] * kiResult;
						}
						row.push(ijResult);
					}
					results.push(row);
				}
				runs++;
				totalTime = new Date - startTime;
			} while (totalTime < 1000);

			const standardsPerSecond = (runs * 1000) / totalTime;

			
			return  '<b>skys per seconds</b>: ' + skysPerSecond + '<b> standards per second</b>: ' + standardsPerSecond + '<b> ratio skys / standard</b>:' +  skysPerSecond / standardsPerSecond +
					'<br><b>skyline result (in skyline form)</b>:<BR>' + JSON.stringify(skyResult) +
					'<br><b>skyline result (in sym form)</b>:<BR>' + JSON.stringify(matrices.skyToSym(skyResult)) +		
					'<br><b>standard result (in standard form)</b>:<BR>'+ JSON.stringify(results);
		}
	});
});
