"use strict";
define(["dojo/_base/declare", "tukos/objects/modeling/materials/LinearIsotropic", "tukos/objects/modeling/modeling", "tukos/utils", "tukos/maths/matrices"], 
function(declare, LinearIsotropic, modeling, utils, matrices){
	const g = modeling.constants.g;
	return declare([LinearIsotropic], {
		/*
		* Use for hydraulicspressure problem type. If pressure < pressureThreshold, conductivityCorrection is applied.
		*/
		
		dHRhs: function(group, integrationPoint){
			const linearProperties = this.linearIsotropic.properties, hydraulicConductivity = linearProperties["hydraulic conductivity"], gravityDirection = group.gravityDirection, thresholdProperties = this.PressureThresholdCorrection.properties;
			let pressure;
			if (integrationPoint){
				const element = integrationPoint.element;
				this.solutionValue = pressure = element.solutionNodalValues ? element.iPointSolutionValue(integrationPoint.iPoint, element.solutionNodalValues) : undefined;
			}
			const correction = (pressure < thresholdProperties.threshold ? thresholdProperties.correction : 1.0);
			return gravityDirection.map((g) => correction * g * hydraulicConductivity);
		},
		dHDHRhs: function(integrationPoint, dHDH){
			const element = integrationPoint.element, group = element.group, thresholdProperties = this.PressureThresholdCorrection.properties;
			const pressure = this.solutionValue || element.solutionNodalValues ? element.iPointSolutionValue(integrationPoint.iPoint, element.solutionNodalValues) : undefined;
			return matrices.skyMultiplyVector(group.cSky, pressure < thresholdProperties.threshold ? dHDH.map(() => thresholdProperties.correction) : dHDH);
		}
	});
});
