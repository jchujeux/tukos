"use strict";
define([],
		function() {
		const factorsIndexes = {1: [[0], [1]], 2: [[0,0], [1,0], [1,1], [0,1]], 3: [[0,0,0], [1,0,0], [1,1,0], [0,1,0], [0,0,1], [1,0,1], [0,1,1], [1,1,1]]},
			  optionalFactorsAndFunctionsIndexes = {
				1: [3: {factors: [2], functions: [0,1]}], 
				2: {5: {factors: [2,0], functions: [0, 1]}, 6: {factors: [1, 2], functions: [1,2]} , 7: {factors: [2, 1], functions: [2,3]}, 8: {factors: [0,2], functions: [3,4]}}, 
				3: {9: {factors: [2,0,0], functions: [0,1]}, 10: {factors: [1,2,0], functions: [1,2]}, 11: {factors: [2,1,0], functions: [2,3]}, 12: {factors: [0,2,0], functions: [3,0]},
				   13: {factors: [2,0,1], functions: [4,5]}, 14: {factors: [1,2,1], functions: [5,6]}, 15: {factors: [2,1,1], functions: [6,7]}, 16: {factors: [0,2,1], functions: [7,4]},
			   	   17: {factors: [0,0,2], functions: [0,4]}, 18: {factors: [1,0,2], functions: [1,5]}, 19: {factors: [1,1,2], functions: [2,6]}, 20: {factors: [0,1,2], functions: [3,7]}}
			}
	return {
		/*
		* returns array of interpolation functions at point referenceCoordinate for isoparametric elements in 1D (2 mandatory nodes, 1 optional), 2D (4 mandatory nodes, 4 optional) and 3D (8 mandatory, 12 optional)
		*/
		interpolationFunctions: function (referenceCoordinates, optionalNodes){
			/*
			* Computes required combinations of 0.5 * (1 +- referenceCoordinates[i]) * 0.5 * (1 +- referenceCoordinates[j])
			*/
			let factors = [];
			for (let coordinate of referenceCoordinates){
				factors.push( [0.5 * (1 + coordinate), 0.5 * (1 - coordinate), 1 - coordinate * coordinate]);
			}
			const dimensions = referenceCoordinates.length, numFunctions = 2**dimensions;
			let functions = [], indexes = factorsIndexes[dimensions];
			 for (let i = 0; i < numFunctions;i++){
				functions[i] = 1;
				let currentIndexes = indexes[i];
				for (let j in currentIndexes){
					functions[i] = functions[i] * factors[j][currentIndexes[j]];
				}
			}
			if (optionalNodes){
				const factorsAndFunctions = optionalFactorsAndFunctionsIndexes[dimensions];
				let  functionIndex = functions.length;
				for (let node of optionalNodes){
					functions[nodeIndex] = 1;
					let currentFactorsAndFunctions = factorsAndFunctions[node], currentFactorsIndexes = currentFactorsAndFunctions.factors, currentFunctionsIndexes = currentFactorsAndFunctions.functions;
					for (let j in currentFactorsIndexes){
						functions[nodeIndex] = functions[nodeIndex] * factors[j][currentFactorsIndexes[j]];
					}
					/*
					* Now that we have the optional node function, we need to propagate its effect on mandatory  functions
					*/
					let correction = - 0.5 * functions[nodeIndex];
					for (let j in currentFunctionsIndexes){
						functions[j] += correction;
					}
					functionIndex +=1;
				}
			}
			return functions;
		}
	}
});