"use strict";
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/maths/matrices", "tukos/utils"], 
function(declare, BaseClass, matrices, utils){
	return declare([BaseClass], {
		/*iPoint, // the index of the integration point in this.element.group.integrationPoints & this.element.refElt
		element,*/

		jacobian: function(){
			const dHRefDXRef= this.element.refElt[this.iPoint].dHRefDXRef, dimension = this.element.group.refEltsDimension, nodesLength = this.element.nodes.length, jacobian = [];
			for (let i = 0; i < dimension; i++){
				jacobian[i] = [];
				for (let j = 0; j < dimension; j++){
					jacobian[i][j] = 0.0;
					for (let n = 0; n < nodesLength; n++){
						jacobian[i][j] += this.element.group.global.nodal.coordinates[i][this.element.nodes[n]] * dHRefDXRef[j][n];
					}
				}
			}
			return jacobian;
		},
		jacobianDeterminant: function(jacobian){
			return this.jacDet || (this.jacDet = matrices.sqrDeterminant(jacobian));
		},
		jacobianInverse: function(jacobian){
			const jacDet = this.jacDet || (this.jacDet = this.jacobianDeterminant(jacobian));
			return matrices.sqrInverse(jacobian, jacDet);
		},
		dHDX: function(jacobianInverse){
			const iPointRefElt = this.element.refElt[this.iPoint], dHDX = matrices.asyMultiply(jacobianInverse, iPointRefElt.dHRefDXRef);
			if (this.element.group.elementType === 'axisymmetric'){
				const radius = this.element.iPointValue(this.iPoint, this.element.group.global.nodes.coordinates[0]);
				dHDX.concat(iPointRefElt.refH.map((h) => h / radius));
			}
			return dHDX;
		},
		dHDXToB: function(dHDX){//applies  the transformation to apply to columns of DHDX to be consistent with C for B(T) . C . B 
			const group = this.element.group, elementType = group.elementType, dimension = group.refEltsDimension;
			switch(group.problemType){
				case 'hydraulicshead':
				case 'hydraulicspressure':
				case 'heatTransfer':
					return dHDX;
				case 'solidmechanics':
				let numShear = 3, numAxial = dimension;	
				switch (elementType){
						case 'truss':
							for (let n = 0; n < dHDX[0].length; n++){
								for (let iDof = 0; iDof < group.ndof; iDof++){
									b.concat(this.truss.projections.map((projection, d) => projection * dHDX[d][n])); 	
								}
							}	
							return b;
						case 'axisymmetric':
							numAxial = 3;
						case 'planestress':
						case 'planestrain':
							numShear = 1;
						default:
							const b = utils.vector(numAxial + numShear, () => []);
							for (let n = 0; n < dHDX[0].length; n++){	
								for (let d = 0; d < numAxial; d++){
									const nodeRow = utils.vector(numAxial, () => 0.0);
									nodeRow[d] = dHDX[d][n];
									b[d].push(...nodeRow);
								}
								if (numShear === 1){
									const nodeRow = [dHDX[1][n], dHDX[0][n]];
									if (numAxial === 3) nodeRow.push(0.0);
									b[numAxial].push(...nodeRow);
								}else{
									for (let i = 0; i < numShear; i++)// in fact numShear = dimension = 3 in this case
										for (let j = 0; j < dimension; j++)
											b[numAxial+i].push(i === j ? 0.0 : dHDX[dimension - i - j][n]);
								}
							}
							return b;
					}
			}
		},
		rhsToEltRhs: function(rhs, dHDx){
			const element = this.element, group = element.group
			let result;
			if (rhs.h){
				result = this.hRhsToEltRhs(rhs.h);
			}
			if (rhs.dH){
				const dHResult = this.dHRhsToEltRhs(dHDx, (element.solutionNodalValues ?  group.material.dHRhs(group, this) : rhs.dH));// 
				result = result ? dHResult.map((x,i) => x + result[i]) : dHResult;
			}
			if (rhs.dHDH){
				const dHDHResult = this.dHRhsToEltRhs(dHDx, this.element.group.material.dHDHRhs(this, rhs.dHDH));
				result = result ? dHDHResult.map((x,i) => -x + result[i]) : dHDHResult.map((x) => -x);
			}
			return result;
		},
		hRhsToEltRhs: function(hRhs){
			const iPointRefElt = this.element.refElt[this.iPoint], h = iPointRefElt.refH, rhs = [];
			for (let n = 0; n < h.length; n++){
				for (let d = 0; d <this.element.group.ndof; d++){
					rhs.push(h[n] * hRhs[d]);
				}
			}
			return rhs;
		},
		dHRhsToEltRhs: function(dHDx, dHRhs){
			const rhs = [];
			for (let n = 0; n < this.element.nodes.length; n++){
				let sum = 0.0;
				for (let d = 0; d < dHDx.length; d++){
					sum += dHDx[d][n] * dHRhs[d];
				}
				rhs.push(sum);
			}
			return rhs;
		},
		contributions: function(mode){
			const element = this.element, group = element.group, jacobian = this.jacobian(), iWeight = group.integrationPoints.weights[this.iPoint], combinedWeight = Math.abs(this.jacobianDeterminant(jacobian)) * iWeight, c = group.cSky, groupRhs = group.rhs, 
				  weightedC = {diagIndexes: c.diagIndexes, upper: c.upper.map((x) => x * combinedWeight)};
			this.solutionValue = undefined;
			const dHDx = this.dHDX(this.jacobianInverse(jacobian)), bMatrix = this.dHDXToB(dHDx);
			let matrix, rhs;
			if (mode.matrix){
				matrix = matrices.aTSkyA(bMatrix, weightedC);
			}
			switch(mode.rhs){
				case true: 
					rhs = (groupRhs.h || groupRhs.dH) ? this.rhsToEltRhs(groupRhs, dHDx).map((x) => x * combinedWeight) : [];
					break;
				case 'unbalanced':	
					rhs = this.rhsToEltRhs({h: groupRhs.h, dH: groupRhs.dH, dHDH: element.solutionNodalValues ? matrices.asyMultiplyVector(bMatrix, element.solutionNodalValues): undefined}, dHDx);
					if (rhs){
						rhs = rhs.map((x) => x * combinedWeight);
					}
			}
			return {matrix: matrix, rhs: rhs};
		}
	});
});
