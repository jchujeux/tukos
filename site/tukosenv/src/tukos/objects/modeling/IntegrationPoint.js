"use strict";
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/maths/matrices", "tukos/objects/modeling/isoparametricElementUtils", "tukos/utils"], 
function(declare, BaseClass, matrices, isoUtils, utils){
	return declare([BaseClass], {
		/*iPoint, // the index of the integration point in this.element.group.integrationPoints & this.element.refElt
		element,*/

		dHDXToB: function(dHDX){//applies  the transformation to apply to columns of DHDX to be consistent with C for B(T) . C . B 
			return this.element.group.material.dHDXToB(dHDX);
		},
		rhsToEltRhs: function(rhs, dHDx){
			const element = this.element, group = element.group
			let result;
			if (rhs.h){
				result = this.hRhsToEltRhs(rhs.h);
			}
			if (rhs.dH){
				const dHResult = this.dHRhsToEltRhs(dHDx, (element.nodalDofSolution() ?  group.material.dHRhs(group, this) : rhs.dH));// 
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
			const element = this.element, group = element.group, rstElementInformation = isoUtils.rstElementInformation(this.element.refElt[this.iPoint], element),
				  iWeight = group.integrationPoints.weights[this.iPoint], combinedWeight = Math.abs(isoUtils.jacobianDeterminant(rstElementInformation)) * iWeight, c = group.cSky, groupRhs = group.rhs, 
				  weightedC = {diagIndexes: c.diagIndexes, upper: c.upper.map((x) => x * combinedWeight)};
			this.solutionValue = undefined;
			const dHDx = isoUtils.dHDX(rstElementInformation), bMatrix = this.dHDXToB(dHDx);
			let matrix, rhs;
			if (mode.matrix){
				matrix = matrices.aTSkyA(bMatrix, weightedC);
			}
			switch(mode.rhs){
				case true: 
					rhs = (groupRhs.h || groupRhs.dH) ? this.rhsToEltRhs(groupRhs, dHDx).map((x) => x * combinedWeight) : [];
					break;
				case 'unbalanced':	
					const dofNodesSolution = element.nodalDofSolution();
					rhs = this.rhsToEltRhs({h: groupRhs.h, dH: groupRhs.dH, dHDH: dofNodesSolution ? matrices.asyMultiplyVector(bMatrix, dofNodesSolution): undefined}, dHDx);
					if (rhs){
						rhs = rhs.map((x) => x * combinedWeight);
					}
			}
			return {matrix: matrix, rhs: rhs};
		}
	});
});
