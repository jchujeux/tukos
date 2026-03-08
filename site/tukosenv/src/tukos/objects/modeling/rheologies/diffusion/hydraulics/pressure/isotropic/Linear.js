"use strict";
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/objects/modeling/modeling", "tukos/utils", "tukos/maths/matrices"], 
function(declare, BaseClass, modeling, utils, matrices){
	const g = modeling.constants.g;
	return declare([BaseClass], {
		//the constitutive models used in element groups  (intrinsic behavior of the material)
		//properties, //depends on symetry, linearity, problem: Young, nu, etc.
		
		cSky: function(dimension){
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
		rhs: function(group){//problemType, rhsDescription){
			return {h: this.hRhs(group), dH: this.dHRhs(group)};
		},
		hRhs: function(group){
			return undefined;
		},
		dHRhs: function(group){
			const properties = this.linearIsotropic.properties, hydraulicConductivity = properties["hydraulic conductivity"]/*, dHRhs = []*/, gravityDirection = group.gravityDirection;
			return gravityDirection.map((g) => g * hydraulicConductivity);
		},
		dHDHRhs: function(integrationPoint, dHDH){
			const element = integrationPoint.element, group = element.group;
			return matrices.skyMultiplyVector(group.cSky, dHDH);
		},
		imposedValue: function(element, globalNode, imposedValueDescription){
			const group = element.group, gravityDirection = group.gravityDirection, fluidSurfaceCoordinate = imposedValueDescription.fluidSurface;
			const direction = gravityDirection[0] && !gravityDirection[1] ? 0 : 1, nodeCoordinate = group.global.nodal.coordinates[direction][globalNode], fluidHeight = (nodeCoordinate - fluidSurfaceCoordinate) * gravityDirection[direction];
			return fluidHeight <= 0 ? 0.0 :  g * this.linearIsotropic.properties['fluid density'] * fluidHeight;
		}
	});
});
