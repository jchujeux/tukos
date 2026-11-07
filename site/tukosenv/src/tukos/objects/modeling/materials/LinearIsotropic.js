"use strict";
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/objects/modeling/modeling", "tukos/utils", "tukos/maths/matrices"], 
function(declare, BaseClass, modeling, utils, matrices){
	const g = modeling.constants.g;
	return declare([BaseClass], {
		//the constitutive models used in element groups  (intrinsic behavior of the material)
		//properties, //depends on symetry, linearity, problem: Young, nu, etc.
		
		cSky: function(dimension, problemType, elementType){
			const cFunction = '_' + problemType + "LinearIsotropic";
			return this[cFunction](dimension, elementType);
		},
		_hydraulicsheadLinearIsotropic: function(dimension){
			return this._diffusionLinearIsotropic(dimension, this.linearIsotropic.properties["hydraulic conductivity"]);
		},
		_hydraulicspressureLinearIsotropic: function(dimension){
			const hydraulicConductivity = this.linearIsotropic.properties["hydraulic conductivity"], fluidDensity = this.linearIsotropic.properties["fluid density"], conductivity = hydraulicConductivity / (g * fluidDensity);
			return this._diffusionLinearIsotropic(dimension, conductivity);
		},
		_diffusionLinearIsotropic: function(dimension, conductivity){
			const result = {diagIndexes: [], upper: []};
			for (let i = 1; i <= dimension; i++){
				result.diagIndexes.push(i);
				result.upper.push(conductivity);
			} 
			return result; 
		},
		_hydraulicsheadLinearOrthotropic: function(dimension){
			return this._diffusionLinearOrthotropic(dimension, 'hydraulic conductivities');
		},
		_diffusionLinearOrthotropic: function(dimension, conductivitiesName){// digonal tensor (so anisotropy parallel to the geometrical axes.
			const result = {diagIndexes: [], upper: []};
			for (let i = 1; i <= dimension; i++){
				result.diagIndexes.push(i);
				result.upper.push(this.linearOrthotropic.properties[conductivitiesName][i-1]);
			} 
			return result; 
		},
		_solidmechanicsLinearIsotropic: function(dimension, elementType){
			const diagIndexes = [], upper = [],  e = this.linearIsotropic.properties.young, nu = this.linearIsotropic.properties.nu;
			let numShear = 3, numAxial = dimension;
			switch (elementType){
				case 'truss':
					return {diagIndexes: [1], upper: [e]};
				case 'planestress':
					const f1 = e / (1 - nu * nu), f2 = nu * f1, f3 = e / (2 * (1 + nu)); 	
					return {diagIndexes: [1,2,3], upper: [f1, f1, f2, f3]};
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
		_dHRhsHydraulicspressure: function(group, integrationPoint){
			const properties = this.linearIsotropic.properties, hydraulicConductivity = properties["hydraulic conductivity"]/*, dHRhs = []*/, gravityDirection = group.gravityDirection;
			return gravityDirection.map((g) => g * hydraulicConductivity);
		},
		dHDHRhs: function(integrationPoint, dHDH){
			const dHDHRhsFunction = '_dHDHRhs' + utils.capitalize(integrationPoint.element.group.problemType);
			return this[dHDHRhsFunction] ? this[dHDHRhsFunction](integrationPoint, dHDH) : undefined;
		},
		_dHDHRhsHydraulicspressure: function(integrationPoint, dHDH){
			const element = integrationPoint.element, group = element.group;
			return matrices.skyMultiplyVector(group.cSky, pressure < 0.0 ? dHDH.map((x) => 0.0) : dHDH);
		},
		_dHDHRhsHydraulicshead: function(integrationPoint, dHDH){
			const element = integrationPoint.element, group = element.group;
			if (group.rheologyType.indexOf('Linear') === 0){
				const hydraulicsHead =  element.solutionNodalValues ? element.iPointSolutionValue(integrationPoint.iPoint, element.solutionNodalValues) : undefined, iPointCoordinates = element.iPointCoordinates(integrationPoint.iPoint), 
					  headFreeSurfaceThreshold = - matrices.dotProduct(iPointCoordinates, group.gravityDirection);
				if (hydraulicsHead < headFreeSurfaceThreshold){
					console.log('hydraulic head: ' + hydraulicsHead + ' threshold: ' + headFreeSurfaceThreshold);
				}
				return matrices.skyMultiplyVector(group.cSky, hydraulicsHead <headFreeSurfaceThreshold ? dHDH.map((x) => 0.001 * x) : dHDH);
			}else{
				Pmg.addFeedback(Pmg.message('No nonlinear material defined yet'), null, null, true);
				throw Error("nonlinear rheology not implemented yet");
			}
		},
		imposedValue: function(element, globalNode, imposedValueDescription){
			const imposedValueFunction = '_imposed' + utils.capitalize(element.group.problemType);
			return this[imposedValueFunction](element, globalNode, imposedValueDescription);
		},
		_imposedHydraulicspressure: function(element, globalNode, imposedValueDescription){
			const group = element.group, gravityDirection = group.gravityDirection, fluidSurfaceCoordinate = imposedValueDescription.fluidSurface;
			const direction = gravityDirection[0] && !gravityDirection[1] ? 0 : 1, nodeCoordinate = group.global.nodal.coordinates[direction][globalNode], fluidHeight = (nodeCoordinate - fluidSurfaceCoordinate) * gravityDirection[direction];
			return fluidHeight <= 0 ? 0.0 :  g * this.linearIsotropic.properties['fluid density'] * fluidHeight;
		}
	});
});
