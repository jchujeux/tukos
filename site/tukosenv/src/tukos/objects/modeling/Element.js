"use strict";
define(["dojo/_base/declare", "dojo/json5", "tukos/objects/BaseClass", "tukos/maths/matrices", "tukos/objects/modeling/isoparametricElementUtils", "tukos/objects/modeling/IntegrationPoint", "tukos/utils"], 
function(declare, JSON5, BaseClass, matrices, isoUtils, IntegrationPoint, utils){
	return declare([BaseClass], {
		/*nodes, // the global nodes indexes of the elements
		optionalNodes, // for nodes beyond the mandatory number of nodes, 
		properties, //optional
		refElt,
		group,*/
		
		dofNodesSolutionCache: {}, nodalCoordinatesCache: {},

		constructor: function constructor(args){
			this.inherited(constructor, arguments);
			const self = this, group = this.group,  iPoints = group.integrationPoints;
			this.points = [];
			for (let p = 0; p < iPoints.points.length; p++){
				this.points[p] = new IntegrationPoint({iPoint: p, element: this});
			}
			this.setImposedValues();
			if (group.elementType === 'truss'){//ignore optional node, and compute the normalized projection of the truss on each global axis
				this.truss = truss = {length: 0.0, area: this.properties && this.properties.area || group.properties.area};
				const projectedLengths = [];
				for (let d = 0; d < group.dimension; d++){
					projectedLengths[i] = group.global.nodes.coordinates[d][this.nodes[1]] - group.global.nodes.coordinates[d][this.nodes[0]];
					truss.length += projectedLengths[i] * projectedLengths[i];
				}
				truss.length = Math.sqrt(truss.length);
				truss.projections = projectedLengths.map((x) => x / Length);
			}
		},
		pressure: function(rstElementInformation){
			const element = rstElementInformation.element, nodesPressure = this.dofNodesPressureCache.element === element 
				? this.dofNodesPressureCache.dofNodesPressure 
				: (this.dofNodesPressureCache = {element: element, dofNodesPressure: element.nodalDofSolution(element.group.global.solution)}).dofNodesPressure;
			return rstElementInformation.reference.refH.map((x, n) => x * nodesPressure[n]);
		},
		equationsIndexes: function(){
			const self = this, equationsIndexes = [];
			this.nodes.forEach(function(node){
				self.group.globalDofs.forEach(function(globalDof){
					equationsIndexes.push(self.group.global.nodal.equationsIndexes[globalDof][node]);
				});
			});
			return equationsIndexes;
		},
		setImposedValues: function(){
			if (this.group.global.imposedValues){
				const self = this, group = this.group, imposedValues = this.group.global.imposedValues;
				this.imposedValues = [];
				group.globalDofs.forEach(function(dGlobal, dLocal){
					self.nodes.forEach(function(nGlobal, nLocal){
						let imposedValue = (imposedValues[dGlobal] || [])[nGlobal];
						if (imposedValue){
							if (typeof imposedValue === 'string'){
								imposedValues[dGlobal][nGlobal] = imposedValue = Number(imposedValue) || self.group.material.imposedValue(self, nGlobal, JSON5.parse(imposedValue));
							}
							self.imposedValues[dLocal+nLocal*group.ndof] = imposedValue;
						}
					});
				});
				if (this.imposedValues.length === 0){
					this.imposedValues = false;
				}
			}
		},
		nodalCoordinates: function(){
			const dimension = this.group.refEltsDimension, nodesLength = this.nodes.length, cache = this.nodalCoordinatesCache;
			if (cache.element === this){
				return cache.nodalCoordinates;
			}else{
				const nodalCoordinates = [];
				for (let d = 0; d < dimension; d++){
					nodalCoordinates.push([]);
					for (let n = 0; n < nodesLength; n++){
						 nodalCoordinates[d][n] = this.group.global.nodal.coordinates[d][this.nodes[n]];
					}
				}
				this.dofNodesSolutionCache = {element: this, nodalCoordinates: nodalCoordinates};
				return nodalCoordinates;
			}
		},
		nodalDofSolution: function(){
			const globalSolution = this.group.global.solution;
			if (globalSolution){
				const cache = this.dofNodesSolutionCache;
				if (cache.element === this && cache.globalSolution === globalSolution){
					return cache.dofNodesSolution;
				}else{
					const self = this, group = this.group, solutionValues = [], equationsIndexes = this.equationsIndexes();
					self.nodes.forEach(function(nGlobal, nLocal){
						group.globalDofs.forEach(function(dGlobal, dLocal){
							const index = dLocal+nLocal*group.ndof, equationIndex = equationsIndexes[index];
							solutionValues[index] = equationIndex >= 0 ? (globalSolution && globalSolution[equationIndex] || 0.0) : (self.imposedValues ? (self.imposedValues[index] || 0.0) : 0.0);
						});
					});
					this.dofNodesSolutionCache = {element: this, globalSolution: globalSolution, dofNodesSolution: solutionValues};
					return solutionValues;
				}
			}else{
				return undefined;
			}
		},
		contributions: function(mode){
			let contributions = this.points[0].contributions(mode);
			for (let p = 1; p < this.points.length; p++){
				const newContributions = this.points[p].contributions(mode);
				if (mode.matrix)
					contributions.matrix = matrices.skyAdd(contributions.matrix, newContributions.matrix);
				if (mode.rhs && newContributions.rhs){
					newContributions.rhs.forEach(function(contribution, i){
						contributions.rhs[i] += contribution;
					});
				}
			}
			if (this.imposedValues && mode.rhs !== 'unbalanced'){// linear analysis
				const imposedValuesRhs = matrices.skyMultiplyVector(contributions.matrix, utils.vector(contributions.matrix.diagIndexes.length, (x, i) => {return -this.imposedValues[i] ||  0.0}));
				contributions.rhs = imposedValuesRhs.map((x,i) => x + ((contributions.rhs && contributions.rhs[i]) || 0.0));
			}
			return contributions;
		},
		refHValueFromGlobal: function(refH, globalNodalValue){
			return isoUtils.refHValue(refH, this.nodes, globalNodalValue);
		},
		refHValuesFromGlobal: function(refH, globalNodalValues){
			return isoUtils.refHValues(refH, this.nodes, globalNodalValue);
		},
		iPointValueFromGlobal: function(iPoint, globalNodalValue){
			return isoUtils.refHValue(this.refElt[iPoint].refH, this.nodes, globalNodalValue);
		},
		iPointValuesFromGlobal: function(iPoint, globalNodalValues){
			return isoUtils.refHValues(this.refElt[iPoint].refH, this.nodes, globalNodalValues);
		},
		iPointCoordinates: function(iPoint){
			return this.iPointValuesFromGlobal(iPoint, this.group.global.nodal.coordinates);
		},
		refHValuesFromElement: function(refH, dofNodesValues){
			if (this.group.ndof === 1){
				return matrices.dotProduct(dofNodesValues, refH);
			}else{
				let refHValuesArray = [];
				for (let d = 0; d < this.group.ndof; d++){
					let refHValue = 0.0;
					for (let n = 0; n < this.nodes.length; n++){
						refHValue += elementNodalSolution[d+n*this.group.ndof] * refH[n];
					}
					refHValuesArray[d] = iPointSolution;
				}
				return refHValuesArray;
			}
		},
		iPointValuesFromElement: function(iPoint, dofNodesValues){
			return this.refHValuesFromElement(this.refElt[iPoint].refH, dofNodesValues);
		}
	});
});
