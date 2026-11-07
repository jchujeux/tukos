"use strict";
define(["tukos/utils", "tukos/maths/matrices"],
	function(utils, matrices) {
		const factorsIndexes = {1: [[0], [1]], 2: [[0,0], [1,0], [1,1], [0,1]], 3: [[0,0,0], [1,0,0], [1,1,0], [0,1,0], [0,0,1], [1,0,1], [0,1,1], [1,1,1]]},
		optionalFactorsAndFunctionsIndexes = {
				1: {3: {factors: [2], functions: [0,1]}}, 
				2: {5: {factors: [2,0], functions: [0, 1]}, 6: {factors: [1, 2], functions: [1,2]} , 7: {factors: [2, 1], functions: [2,3]}, 8: {factors: [0,2], functions: [3,0]}}, 
				3: {9: {factors: [2,0,0], functions: [0,1]}, 10: {factors: [1,2,0], functions: [1,2]}, 11: {factors: [2,1,0], functions: [2,3]}, 12: {factors: [0,2,0], functions: [3,0]},
				   13: {factors: [2,0,1], functions: [4,5]}, 14: {factors: [1,2,1], functions: [5,6]}, 15: {factors: [2,1,1], functions: [6,7]}, 16: {factors: [0,2,1], functions: [7,4]},
			   	   17: {factors: [0,0,2], functions: [0,4]}, 18: {factors: [1,0,2], functions: [1,5]}, 19: {factors: [1,1,2], functions: [2,6]}, 20: {factors: [0,1,2], functions: [3,7]}}
			};
	return {
		/*
		* returns array of interpolation functions at point referenceCoordinate for isoparametric elements in 1D (2 mandatory nodes, 1 optional), 2D (4 mandatory nodes, 4 optional) and 3D (8 mandatory, 12 optional)
		*/
		refH: function (rst, optionalNodes){
			/*
			* Computes required combinations of 0.5 * (1 +- rst[i]) * 0.5 * (1 +- rst[j])
			*/
			let factors = [];
			for (let coordinate of rst){
				factors.push( [0.5 * (1 + coordinate), 0.5 * (1 - coordinate), 1 - coordinate * coordinate]);
			}
			const dimension = rst.length, numMandatoryFunctions = 2**dimension;
			let functions = [], indexes = factorsIndexes[dimension];
			 for (let n = 0; n < numMandatoryFunctions;n++){
				functions[n] = 1;
				const currentIndexes = indexes[n];
				currentIndexes.forEach(function(currentIndex, j){
					functions[n] *= factors[j][currentIndex];
				});
			}
			if (optionalNodes){
				const factorsAndFunctions = optionalFactorsAndFunctionsIndexes[dimension];
				for (let node of optionalNodes){
					let  functionValue = 1.0;
					let currentFactorsAndFunctions = factorsAndFunctions[node], currentFactorsIndexes = currentFactorsAndFunctions.factors, currentFunctionsIndexes = currentFactorsAndFunctions.functions;
					currentFactorsIndexes.forEach(function(currentFactorsIndex, j){
						functionValue *= factors[j][currentFactorsIndex];
					});
					functions.push(functionValue);
					/*
					* Now that we have the optional node function, we need to propagate its effect on mandatory  functions
					*/
					let correction = - 0.5 * functionValue;
					for (let j of currentFunctionsIndexes){
						functions[j] += correction;
					}
				}
			}
			return functions;
		},
		dHRefDXRef: function(rst, optionalNodes){// returns dFunctions[i][n] = dhn/xi at point rst
			const factors = [], dFactors = [];
			for (let coordinate of rst){
				factors.push( [0.5 * (1 + coordinate), 0.5 * (1 - coordinate), 1 - coordinate * coordinate]);
				dFactors.push([0.5, -0.5, -2 * coordinate]);//dFactors are the derivative of factors wrt the current coordinate
			}
			const dimension = rst.length, numMandatoryFunctions = 2**dimension;
			let dFunctions = utils.vector(dimension, () => []), indexes = factorsIndexes[dimension];
			dFunctions.forEach(function(dFunction, i){//i = 0: r; i = 1: s
				for (let n = 0; n < numMandatoryFunctions;n++){
					dFunction[n] = 1;
					const currentIndexes = indexes[n];
					currentIndexes.forEach(function(currentIndex, j){
						dFunction[n] *= (j === i ? dFactors[j] : factors[j])[currentIndex];
					});
				}			
			});
			if (optionalNodes){
				const factorsAndFunctions = optionalFactorsAndFunctionsIndexes[dimension];
				dFunctions.forEach(function(dFunction, i){
					for (let node of optionalNodes){
						let  dFunct = 1.0;
						let currentFactorsAndFunctions = factorsAndFunctions[node], currentFactorsIndexes = currentFactorsAndFunctions.factors, currentFunctionsIndexes = currentFactorsAndFunctions.functions;
						currentFactorsIndexes.forEach(function(currentFactorsIndex, j){
							dFunct *= (j === i ? dFactors[j] : factors[j])[currentFactorsIndex];
						});
						dFunction.push(dFunct);
						/*
						* Now that we have the optional node dfunction, we need to propagate its effect on mandatory  dfunctions
						*/
						let correction = - 0.5 * dFunct;
						for (let j of currentFunctionsIndexes){
							dFunction[j] += correction;
						}
					}
				});
			}
			return dFunctions;
		},
		rstReferenceInformation: function(rst, optionalNodes){
			return {rst: rst, refH: this.refH(rst, optionalNodes), dHRefDXRef: this.dHRefDXRef(rst, optionalNodes)};
		},
		rstElementInformation(rstReferenceInformation, element){
			return {reference: rstReferenceInformation, element: element};
		},
		jacobian: function(rstElementInformation){
			return rstElementInformation.jacobian || (rstElementInformation.jacobian = rstElementInformation.element.nodalCoordinates().map((a) => rstElementInformation.reference.dHRefDXRef.map((b) => matrices.dotProduct(a,b))));
		},
		_jacobian: function(dHRefDXRef, nodalCoordinates){
			return nodalCoordinates.map((a) => dHRefDXRef.map((b) => matrices.dotProduct(a,b)));
		},
		jacobianDeterminant: function(rstElementInformation){
			return rstElementInformation.jacobianDeterminant || (rstElementInformation.jacobianDeterminant = matrices.sqrDeterminant(this.jacobian(rstElementInformation)));
		},
		jacobianInverse: function(rstElementInformation){
			return matrices.sqrInverse(this.jacobian(rstElementInformation), this.jacobianDeterminant(rstElementInformation));
		},
		_dHDX: function(rst, optionalNodes, nodalCoordinates){//used in diagramUtils for solutionGradient, as nodal coordinates are in the svg space in that case, and not global coordinates.
			const dHRefDXRef = this.dHRefDXRef(rst, optionalNodes), jacobian = this._jacobian(dHRefDXRef, nodalCoordinates), jacobianInverse = matrices.sqrInverse(jacobian, matrices.sqrDeterminant(jacobian));
			return  matrices.asyMultiply(jacobianInverse, dHRefDXRef);
		},
		dHDX: function(rstElementInformation){
			const {reference, element} = rstElementInformation, dHDX = matrices.asyMultiply(this.jacobianInverse(rstElementInformation), reference.dHRefDXRef);
			if (element.group.elementType === 'axisymmetric'){
				const radius = this.refHValue(reference.refH, element.nodes, element.group.global.nodes.coordinates[0]);
				dHDX.concat(reference.refH.map((h) => h / radius));
			}
			return dHDX;
		},
		refHValue: function(refH, globalNodesIndexes, globalNodalValue){
			return refH.reduce((value, h, index) => value + h * globalNodalValue[globalNodesIndexes[index]], 0.0);
		},
		refHValues: function(refH, globalNodesIndexes, globalNodalValues){
			return globalNodalValues.map((globalNodalValue) => this.refHValue(refH, globalNodesIndexes, globalNodalValue))
		},
		rstValue: function(rst, globalNodesIndexes, optionalNodes, globalNodalValue){
			const refH = this.refH(rst, optionalNodes);
			return this.refHValue(refH, globalNodesIndexes, globalNodalValue);
		},
		rstValues: function(rst, globalNodesIndexes, optionalNodes, globalNodalValues){
			return this.refHValues(this.refH(rst, optionalNodes), globalNodesIndexes, globalNodalValues);
		},
		numberOfMandatoryNodes: function(dimension){
			return 2**dimension;
		},
		numberOfOptionalNodes: function(dimension){
			return dimension * 2 ** (dimension-1);
		},
		firstOptionalNode: function(dimension){
			return this.numberOfMandatoryNodes(dimension) + 1;
		},
		maxNumberOfNodes: function(dimension){
			return this.numberOfMandatoryNodes(dimension) + this.numberOfOptionalNodes(dimension);
		},
		edge: function(optionalNode, dimension){
			return optionalNode - this.numberOfMandatoryNodes(dimension);
		},
		edgeIndex: function(nodeIndex1, nodeIndex2, dimension){
			const factorsAndFunctionIndexes = optionalFactorsAndFunctionsIndexes[dimension];
			for (let optionalNode in factorsAndFunctionIndexes){
				let edgeNodesIndexes = factorsAndFunctionIndexes[optionalNode].functions;
				if ((nodeIndex1 === edgeNodesIndexes[0] && nodeIndex2 === edgeNodesIndexes[1]) || (nodeIndex1 === edgeNodesIndexes[1] && nodeIndex2 === edgeNodesIndexes[0])){
					return optionalNode - this.firstOptionalNode(dimension);
				}
			}
		},
		nodesIndex: function(edgeIndex, dimension){
			const factorsAndFunctionIndexes = optionalFactorsAndFunctionsIndexes[dimension], optionalNode = edgeIndex + this.firstOptionalNode(dimension);
			return factorsAndFunctionIndexes[optionalNode].functions;
		},
		hasOptionalNode: function(edge, optionalNodes, dimension){
			return optionalNodes.includes(edge + this.numberOfMandatoryNodes(dimension));
			
		},
		optionalNodeIndex: function(edge, element, dimension){//returns the index in element.nodes of the edge optional node if present, else returns false
			const offset = this.numberOfMandatoryNodes(dimension), optionalIndex = element.optionalNodes.indexOf(edge + offset);
			return  optionalIndex !== -1 ? optionalIndex + offset : false;
		},
		edgeElement: function(edge, element, dimension){
			const nodes = element.nodes, edgeElement = {nodes: [nodes[edge-1], nodes[edge % 4]], optionalNodes: []}, optionalNodeIndex = this.optionalNodeIndex(edge, element, dimension);
			if (optionalNodeIndex){
				edgeElement.nodes.push(nodes[optionalNodeIndex]);
				edgeElement.optionalNodes.push(3);
			}
			return edgeElement;
		},
		optionalGlobalNodeIndex: function(edge, element, dimension){
			const offset = this.numberOfMandatoryNodes(dimension), optionalIndex = element.optionalNodes.indexOf(edge + offset);
			return  optionalIndex !== -1 ? element.nodes[optionalIndex + offset] : false;
		},
		valueIntersections: function(value, element, globalNodalValue, dimension){//only valid for dimension 2
			const self = this, nodalValue = element.nodes.map((globalNodeIndex) => globalNodalValue[globalNodeIndex]), intersections = [];
			const getIntersection = function(value, edge, firstNodeIndex, lastNodeIndex, rOffset, rFactor){// assumes linear, so could be improved
				if (nodalValue[firstNodeIndex] !== nodalValue[lastNodeIndex]){
					const [lowValueIndex, highValueIndex] = utils.sortIndexesByValues([firstNodeIndex, lastNodeIndex], [nodalValue[firstNodeIndex], nodalValue[lastNodeIndex]]);
					if (value >= nodalValue[lowValueIndex] && value < nodalValue[highValueIndex]){
						const rRatio = (value - nodalValue[firstNodeIndex]) / (nodalValue[lastNodeIndex] - nodalValue[firstNodeIndex]);
						let rIntersection = rOffset + rFactor * rRatio;
						const edgeElement = self.edgeElement(edge, element, dimension);
						let intersectionValue = self.refHValue(self.refH([rIntersection], edgeElement.optionalNodes), edgeElement.nodes, globalNodalValue);
						if (edgeElement.optionalNodes.length){
							rIntersection += (value - intersectionValue) / (nodalValue[firstNodeIndex] - nodalValue[lastNodeIndex]); // correction for nonlinearity
						}
						intersections.push({id: JSON.stringify([element.nodes[lowValueIndex], element.nodes[highValueIndex]]), element: element, edge: edge, rIntersection: rIntersection});
					}				
				}
			}
			let minNodalValue, maxNodalValue;
			if (value >= (minNodalValue = Math.min(...nodalValue)) && value <= (maxNodalValue = Math.max(...nodalValue))){
				const edges = this.numberOfMandatoryNodes(dimension);
				for (let edge = 1; edge <= edges; edge++){
					let firstNodeIndex = edge - 1, lastNodeIndex;
					if (lastNodeIndex = this.optionalNodeIndex(edge, element, dimension)){// an optional node is present, search in the two sub-edges
						getIntersection(value, edge, firstNodeIndex, lastNodeIndex, 1.0, -1.0);
						firstNodeIndex = lastNodeIndex;
						lastNodeIndex = edge % edges;
						getIntersection(value, edge, firstNodeIndex, lastNodeIndex, 0.0, -1.0);
					}else{
						lastNodeIndex = edge % edges;
						getIntersection(value, edge, firstNodeIndex, lastNodeIndex, 1.0, -2.0);
					}
				}
				return intersections;
			}else{
				return false;
			}
		},
		intersectionCoordinates: function(intersection, globalCoordinates){
			const {element, edge, rIntersection: r} = intersection, dimension = globalCoordinates.length, edgeElement = this.edgeElement(edge, element, dimension);
			return this.rstValues([r], edgeElement.nodes, edgeElement.optionalNodes, globalCoordinates);
		},
		intersectionRst: function(intersection){
			const {edge, rIntersection} = intersection;
			switch (edge){
				case 1: return [rIntersection, 1];
				case 2: return [-1, rIntersection];
				case 3: return [- rIntersection, -1]
				case 4: return [1, - rIntersection];
			}
		}
	}
});