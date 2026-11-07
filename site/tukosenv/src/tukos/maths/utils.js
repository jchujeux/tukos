"use strict";
define(["tukos/utils"],
	function(utils) {
		const sqrt13 = 1 / Math.sqrt(3), sqrt35 = Math.sqrt(3/5);
		let _order, _dimension, _pointsAndWeights;
	return {
		gaussPoints: [[0], [- sqrt13, sqrt13], [-sqrt35, 0, sqrt35], [-0.861136311594053, -0.339981043584856, 0.339981043584856, 0.861136311594053]],
		gaussWeights: [[2], [1, 1], [5/9, 8/9, 5/9], [0.347854845137454, 0.652145154862546, 0.652145154862546, 0.347854845137454]],

		gaussIntegration: function (func, order){// integrates over [-1, +1]
			const orderIndex = order - 1, points = this.gaussPoints[orderIndex], weights = this.gaussWeights[orderIndex];
			let result = 0;
			points.forEach(function(point, key){
				result += weights[key] * func(point);
			});
			return result;
		},
		gaussPointsAndWeights: function(orders){
			const dimension = orders.length, numPoints = orders.reduce((accumulator, currentValue) => accumulator * currentValue, 1), points = [], weights = [], gKeys = [];
			for (let d = 0; d < dimension; d++){
				gKeys[d] = 0;
			}
			pointsAndWeights: for (let p = 0; p < numPoints; p++){
				let point = [], weight = 1.0;
				for (let d = 0; d < dimension; d++){
					const gKey = gKeys[d];
					point.push(this.gaussPoints[orders[d] - 1][gKey]);
					weight *= this.gaussWeights[orders[d] - 1][gKey];
				}
				points.push(point);
				weights.push(weight);
				let d = 0;
				while (gKeys[d] === orders[d] - 1){
					d += 1;
					if (d === dimension){
						break pointsAndWeights;
					}else{
						for (let dd = 0; dd < d; dd++){
							gKeys[dd] = 0;
						}
						continue;
					}
				}
				gKeys[d] +=1;
			}
			return {points: points, weights: weights};
		},
		_initializeMultiDimensionalGaussIntegration: function(order, dimension){
			const order1 = order - 1, gPoints = this.gaussPoints[order1], gWeights = this.gaussWeights[order1];
			let pointsAndWeights = [], gKeys = [], numPoints = order ** dimension;
			for (let i = 0; i < dimension; i++){
				gKeys[i] = 0;
			}
			pointsAndWeights: for (let k = 0; k < numPoints; k++){
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
		},
		quadraticBezierControlPoint: function(origin, middle, end){// found here: https://stackoverflow.com/questions/6711707/draw-a-quadratic-b%C3%A9zier-curve-through-three-given-points
			return [2*middle[0] - origin[0] / 2 - end[0] / 2, 2*middle[1] - origin[1] / 2 - end[1] / 2];
		},
	};
});