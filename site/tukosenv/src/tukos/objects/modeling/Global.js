"use strict"
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/objects/modeling/Group", "tukos/objects/modeling/rheologies/rheologiesLoader", "tukos/maths/matrices", "tukos/utils"], 
function(declare, BaseClass, Group, rheologiesLoader, matrices, utils){
	return declare([BaseClass], {
		/*dimension,// the dimension of the geometrical space
		ndof, // the number of unknowns (of degrees of freedom) per node
		nodal, /
			Coordinates: coordinates[d][n] : row d is the direction index (x,y or z), coordinates[i][n] is the value of the coordinate for node n in the direction i.
			equations: equations[i][n]: row i is the direction index. equation[i][n] = 0 if the node is blocked in the direction i, else it is the equation index associated to node n in the direction i
		neq, //numberOfEquations
		materials, // array of materials
		groups, // array of groups
		matrices, // an object {name1, sky1, name2: sky2, ...}*/

		constructor: function constructor(args){
			this.inherited(constructor, arguments);
			const self = this/*, materials = []*/;
			/*utils.forEach(this.materials, function(material){
				materials.push(material.Material = new Material(material));
			});*/
			const groups = [];
			utils.forEach(this.groups, function(group){
				//group.material = self.materials[group.materialId].Material;
				const rheologyClass = rheologiesLoader.module(group.problemType, group.rheologyType);
				group.material = new rheologyClass(group.materialProperties);
				group.global = self;
				groups.push(group.Group = new Group(group));
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
			return {cols: utils.vector(neq, () => [0.0]), sky: {diagIndexes: utils.vector(neq, () => []), upper: []}};
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
					col = null;
					diagIndexes[c] = (diagIndex = diagIndex + cols[c].length);
				}
				cols = [];
			}
			return matrix.sky;
		}
	});
});
