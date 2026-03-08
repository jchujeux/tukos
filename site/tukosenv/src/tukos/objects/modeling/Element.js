"use strict";
define(["dojo/_base/declare", "dojo/json5", "tukos/objects/BaseClass", "tukos/maths/matrices", "tukos/objects/modeling/isoparametricElementUtils", "tukos/objects/modeling/IntegrationPoint", "tukos/utils"], 
function(declare, JSON5, BaseClass, matrices, isoUtils, IntegrationPoint, utils){
	return declare([BaseClass], {
		/*nodes, // the global nodes indexes of the elements
		optionalNodes, // for nodes beyond the mandatory number of nodes, 
		properties, //optional
		refElt,
		group,*/
		
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
		setSolutionValues: function(globalSolution, equationIndexes){
			if (globalSolution){
				const self = this, group = this.group, solutionValues = [];
				self.nodes.forEach(function(nGlobal, nLocal){
					group.globalDofs.forEach(function(dGlobal, dLocal){
						const index = dLocal+nLocal*group.ndof, equationIndex = equationIndexes[index];
						solutionValues[index] = equationIndex >= 0 ? (globalSolution && globalSolution[equationIndex] || 0.0) : (self.imposedValues ? (self.imposedValues[index] || 0.0) : 0.0);
					});
				});
				return solutionValues;
			}else{
				return undefined;
			}
		},
		contributions: function(mode, equationIndexes){
			this.solutionNodalValues = mode.rhs === 'unbalanced' ? this.setSolutionValues(this.group.global.solution, equationIndexes) : undefined;
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
			if (this.imposedValues && !this.solutionNodalValues){// linear analysis
				const imposedValuesRhs = matrices.skyMultiplyVector(contributions.matrix, utils.vector(contributions.matrix.diagIndexes.length, (x, i) => {return -this.imposedValues[i] ||  0.0}));
				contributions.rhs = imposedValuesRhs.map((x,i) => x + ((contributions.rhs && contributions.rhs[i]) || 0.0));
			}
			return contributions;
		},
		iPointValue: function(iPoint, globalNodalValue){
			return isoUtils.refHValue(this.refElt[iPoint].refH, this.nodes, globalNodalValue);
		},
		iPointCoordinates: function(iPoint){
			return isoUtils.refHValues(this.refElt[iPoint].refH, this.nodes, this.group.global.nodal.coordinates);	
		},
		iPointSolutionValue(iPoint, elementNodalSolution){
			const self = this, refH = self.refElt[iPoint].refH;
			if (this.group.ndof === 1){
				return matrices.dotProduct(elementNodalSolution, refH);
			}else{
				let iPointSolutionArray = [];
				for (let d = 0; d < this.group.ndof; d++){
					iPointSolution = 0.0;
					for (let n = 0; n < this.nodes.length; n++){
						iPointSolution += elementNodalSolution[d+n*this.group.ndof] * this.refElt[iPoint].refH[index];
					}
					iPointSolutionArray[d] = iPointSolution;
				}
				return iPointSolutionArray;
			}
		}
	});
});
