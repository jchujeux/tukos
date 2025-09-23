"use strict";
define([],
	function() {
		const sqrt13 = 1 / Math.sqrt(3), sqrt35 = Math.sqrt(3/5);
		let _order, _dimension, _pointsAndWeights;
	return {
		gaussPoints: [[0], [- sqrt13, sqrt13], [-sqrt35, 0, sqrt35]],
		gaussWeights: [[2], [1, 1], [5/9, 8/9, 5/9]],

		gaussIntegration: function (func, order){
			const order1 = order - 1, points = this.gaussPoints[order1], weights = this.gaussWeights[order1];
			let result = 0;
			points.forEach(function(point, key){
				result += weights[key] * func(point);
			});
			return result;
		},
		_initializeMultiDimensionalGaussIntegration: function(order, dimension){
			const order1 = order - 1, gPoints = this.gaussPoints[order1], gWeights = this.gaussWeights[order1];
			let pointsAndWeights = [], gKeys = [], numPoints = order ** dimension;
			for (let i = 0; i < dimension; i++){
				gKeys[i] = 0;
			}
			pointsAndWeights: {
				for (let k = 0; k < numPoints; k++){
					let point = [], weight = 1.0;
					for (let j = 0; j < dimension; j++){
						const gKey = gKeys[j];
						point.push(gPoints[gKey]);
						weight = weight * gWeights[gKey];
					}
					pointsAndWeights.push([point, weight]);
					let i = 0;
					while (gKeys[i] === order - 1){
						i += 1;
						if (i === dimension){
							break pointsAndWeights;
						}else{
							//gKeys[i] += 1;
							for (let j = 0; j < i; j++){
								gKeys[j] = 0;
							}
							continue ;
						}
					}
					gKeys[i] += 1;
				}
			}
			return pointsAndWeights;
		},
		multiDimensionalGaussIntegation: function(func, order, dimension){
			let result = 0.0;
			if (order !== _order && dimension !== _dimension){
				_pointsAndWeights = this._initializeMultiDimensionalGaussIntegration(order, dimension)
			}	
			_pointsAndWeights.forEach(function(pointAndWeight){
				result += pointAndWeight[1] * func(pointAndWeight[0]);
			});
			return result;
		}
	};
});