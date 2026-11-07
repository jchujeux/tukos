"use strict";
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/objects/modeling/modeling", "tukos/utils", "tukos/maths/matrices"], 
function(declare, BaseClass, modeling, utils, matrices){
	const g = modeling.constants.g;
	return declare([BaseClass], {
		
		cSky: function(dimension){
			const diagIndexes = [], upper = [],  e = this.linearIsotropic.properties.young, nu = this.linearIsotropic.properties.nu;
			let numShear = 3, numAxial = dimension;
			switch (this.group.elementType){
				case 'truss':
					return {diagIndexes: [1], upper: [e]};
				case 'planestress':
					const f1 = e / (1 - nu * nu), f2 = nu * f1, f3 = e / (2 * (1 + nu)); 	
					return {diagIndexes: [1,3,4], upper: [f1, f1, f2, f3]};
				case 'axisymmetric':
					numAxial = 3;
				case 'planestrain':
					numShear = 1;
				default:
					const mu2 = e / (1 + nu), lambda = mu2 * nu / (1 - 2*nu), lambda2Mu = lambda + mu2, mu = mu2 / 2;
					upper.push(lambda2Mu);
					diagIndexes.push(upper.length);
					for (let i = 1; i < numAxial; i++){
						upper.push(lambda2Mu);
						if (lambda > 0){
							for (let j = i-1; j >=0; j--){
								upper.push(lambda);
							}
						}
						diagIndexes.push(upper.length);
					}
					for (let i = 0; i < numShear; i++){
						upper.push(mu);
						diagIndexes.push(upper.length);
					}
					return {diagIndexes: diagIndexes, upper: upper};
			}
		},
		dHDXToB: function(dHDX){
			const group = this.group, elementType = group.elementType, dimension = group.refEltsDimension;
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
							if (numAxial === 3) nodeRow.push(0.0);//bug in 3D if only works for axisymetric ?
							b[numAxial].push(...nodeRow);
						}else{
							for (let i = 0; i < numShear; i++)// in fact numShear = dimension = 3 in this case
								for (let j = 0; j < dimension; j++)
									b[numAxial+i].push(i === j ? 0.0 : dHDX[dimension - i - j][n]);
						}
					}
					return b;
				}
		},
		rhs: function(group){//problemType, rhsDescription){
			return {h: this.hRhs(group), dH: this.dHRhs(group)};
		},
		hRhs: function(group){
			if (group.properties && group.properties.rhs){
				const hRhsFunction = '_hRhs' + utils.capitalize(group.problemType);
				return this[hRhsFunction](group);
			}else{
				return undefined;
			}
		},
		_hRhsSolidmechanics: function(group){
			let hRhs;
			if (group.properties.rhs === 'gravity'){
				const gDensity = g * this.general.properties.density || 0.0, dofsContribution = group.gravityDirection;
				hRhs = [];
				dofsContribution && dofsContribution.forEach(function(contribution, d){
					hRhs[d] = contribution * gDensity;
				});
			}
			return hRhs;
		},
		dHRhs: function(group, integrationPoint){
			const dHRhsFunction = '_dHRhs' + utils.capitalize(group.problemType);
			return this[dHRhsFunction] ? this[dHRhsFunction](group, integrationPoint) : undefined;
		},
		dHDHRhs: function(integrationPoint, dHDH){
			const dHDHRhsFunction = '_dHDHRhs' + utils.capitalize(integrationPoint.element.group.problemType);
			return this[dHDHRhsFunction] ? this[dHDHRhsFunction](integrationPoint, dHDH) : undefined;
		}/*,
		imposedValue: function(element, globalNode, imposedValueDescription){
			const imposedValueFunction = '_imposed' + utils.capitalize(element.group.problemType);
			return this[imposedValueFunction](element, globalNode, imposedValueDescription);
		}*/
	});
});
