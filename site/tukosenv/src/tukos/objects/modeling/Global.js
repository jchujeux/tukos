"use strict"
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/objects/modeling/Group", "tukos/utils"], 
function(declare, BaseClass, Group, utils){
	return declare([BaseClass], {

		constructor: function constructor(args){
			this.inherited(constructor, arguments);
			const self = this;
			const groups = [];
			utils.forEach(this.groups, function(group){
				group.global = self;
				groups.push(new Group(group));
			});
			this.groups = groups;
		},
		assemble: function(mode, globalRhs){
			const self = this;
			let globalMatrix;
			if (mode.matrix){
				globalMatrix = this.createMatrix(this.neq);
			}
			this.groups.forEach(function(group){
				group.elements.forEach(function(element){
					const equationsIndexes = element.equationsIndexes(), contribution = element.contributions(mode, equationsIndexes);
					if (mode.matrix){
						self.addToMatrix(globalMatrix, contribution.matrix, equationsIndexes);
					}
					if (mode.rhs && contribution.rhs){
						self.addToRhs(globalRhs, contribution.rhs, equationsIndexes);
					}
				});
			});
			return {globalMatrix: mode.matrix ? this.colsToSky(globalMatrix) : globalMatrix, globalRhs: globalRhs};
		},
		createMatrix: function(neq){
			return {cols: utils.vector(neq, () => [0.0]), sky: {diagIndexes: [], upper: []}};
		},
		// the (global) equation associated to element dof i and node n is global.nodal.equationsIndexes[group.globalDofs[i]], [element.nodes[n]]
		addToMatrix: function(matrix, skyContribution, skyEquationsIndexes){
			const cols = matrix.cols, {diagIndexes, upper} = skyContribution
			let diagIndex = 0;
			for (let c = 0; c < diagIndexes.length; c++){
				const nextDiagIndex = diagIndexes[c], cEquationIndex = skyEquationsIndexes[c];
				if (cEquationIndex >= 0){
					for (let r = c; r >= 0; r--){
						const rEquationIndex = skyEquationsIndexes[r];
						if (rEquationIndex >=0){
							if (cEquationIndex >= rEquationIndex){
								cols[cEquationIndex][cEquationIndex - rEquationIndex] = (cols[cEquationIndex][cEquationIndex - rEquationIndex] || 0.0) + upper[diagIndex + c - r];
							}else{
								cols[rEquationIndex][rEquationIndex - cEquationIndex] = (cols[rEquationIndex][rEquationIndex - cEquationIndex] || 0.0) + upper[diagIndex + c - r];
							}
						}
					}
				}
				diagIndex = nextDiagIndex;
			}
		},
		addToRhs: function(rhs, elementContribution, elementEquationsIndexes){
			for (let r = 0; r < elementContribution.length; r++){
				const equationIndex = elementEquationsIndexes[r];
				if (equationIndex >= 0){
					rhs[equationIndex] += elementContribution[r];
				}
			}
		},
		colsToSky: function(matrix){
			let {cols, sky} = matrix, {diagIndexes, upper} = sky;
			if (cols.length){
				let diagIndex = 0;
				for (let c = 0; c < cols.length; c++){
					let col = cols[c];
					for (let r = 0; r < col.length; r++){
						upper.push(col[r] === undefined ? 0.0 : col[r]);
					}
					diagIndexes[c] = (diagIndex = diagIndex + cols[c].length);
					cols[c] = null;
				}
				delete matrix.cols;
			}
			return matrix.sky;
		},
		nodalSolution: function(equationsSolution){
			let nodalSolution;
			const nodal = this.nodal, solution = equationsSolution || this.solution, imposedValues = this.imposedValues;
			if (solution){
				nodalSolution = [];
				for (let i = 0; i < nodal.equationsIndexes.length; i++){
					const dofSolution = [];
					nodal.equationsIndexes[i].forEach(function(equationIndex, n){
						dofSolution.push(equationIndex === -1 ? 0 : solution[equationIndex]);
					});
					nodalSolution.push(dofSolution);
				}
				if (imposedValues){
					imposedValues.forEach(function(row, d){
						row.forEach(function(imposedValue, n){
							nodalSolution[d][n] = imposedValue;
						});
					});
				}
			}
			return nodalSolution;
		},
		equationsSolution: function(nodalSolution){
			const equationsIndexes = this.nodal.equationsIndexes, equationsSolution = [];
			nodalSolution.forEach(function(dofSolution, dof){
				dofSolution.forEach(function(solution, node){
					const equationIndex = equationsIndexes[dof][node];
					if (equationIndex != -1){
						equationsSolution[equationIndex] = nodalSolution[dof][node];
					}
				});
			});
			return equationsSolution;
		}
	});
});
