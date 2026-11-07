"use strict";
define(["dojo/_base/declare", "tukos/objects/BaseClass", "tukos/objects/modeling/modeling", "tukos/objects/modeling/isoparametricElementUtils", "tukos/maths/matrices"], 
function(declare, BaseClass, modeling, isoUtils, matrices){
	const g = modeling.constants.g;
	return declare([BaseClass], {
		cSky: function(dimension){
			const hydraulicConductivity = this.linearIsotropic.properties["hydraulic conductivity"], fluidDensity = this.linearIsotropic.properties["fluid density"], conductivity = hydraulicConductivity / (g * fluidDensity);
			const result = {diagIndexes: [], upper: []};
			for (let i = 1; i <= dimension; i++){
				result.diagIndexes.push(i);
				result.upper.push(conductivity);
			} 
			return result; 
		},
		dHDXToB: function(dHDX){
			return dHDX;
		},
		rhs: function(group){
			return {h: this.hRhs(group), dH: this.dHRhs(group)};
		},
		hRhs: function(group){
			return undefined;
		},
		dHRhs: function(group){
			const properties = this.linearIsotropic.properties, hydraulicConductivity = properties["hydraulic conductivity"], gravityDirection = group.gravityDirection;
			return gravityDirection.map((d) => d * hydraulicConductivity);
		},
		dHDHRhs: function(integrationPoint, dHDH){
			const element = integrationPoint.element, group = element.group;
			return matrices.skyMultiplyVector(group.cSky, dHDH);
		},
		imposedValue: function(element, globalNode, imposedValueDescription){
			const group = element.group, gravityDirection = group.gravityDirection, fluidSurfaceCoordinate = imposedValueDescription.fluidSurface;
			const direction = gravityDirection[0] && !gravityDirection[1] ? 0 : 1, nodeCoordinate = group.global.nodal.coordinates[direction][globalNode], fluidHeight = (nodeCoordinate - fluidSurfaceCoordinate) * gravityDirection[direction];
			return fluidHeight <= 0 ? 0.0 :  g * this.linearIsotropic.properties['fluid density'] * fluidHeight;
		},
		filtrationVelocity: function(rstElementInformation){
			const element = rstElementInformation.element, group = element.group, properties = this.linearIsotropic.properties, hydraulicConductivity = properties["hydraulic conductivity"], gravityDirection = group.gravityDirection;
			 return matrices.skyMultiplyVector(group.cSky, matrices.asyMultiplyVector(isoUtils.dHDX(rstElementInformation), rstElementInformation.element.nodalDofSolution())).map((x, d) => -x + gravityDirection[d] * hydraulicConductivity);
		}
	});
});
