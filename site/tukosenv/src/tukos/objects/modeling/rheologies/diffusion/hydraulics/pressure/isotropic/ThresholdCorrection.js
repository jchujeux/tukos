"use strict";
define(["dojo/_base/declare", "tukos/objects/modeling/rheologies/diffusion/hydraulics/pressure/isotropic/Linear", "tukos/objects/modeling/modeling", "tukos/objects/modeling/isoparametricElementUtils", "tukos/maths/matrices"], 
function(declare, LinearIsotropic, modeling, isoUtils, matrices){
	const g = modeling.constants.g;
	return declare([LinearIsotropic], {
		dHRhs: function dHRhs(group, integrationPoint){
			if (integrationPoint){
				const linearProperties = this.linearIsotropic.properties, hydraulicConductivity = linearProperties["hydraulic conductivity"], gravityDirection = group.gravityDirection, thresholdProperties = this.thresholdCorrection.properties.pressure;
				const element = integrationPoint.element, nodesPressure = element.nodalDofSolution(), pressure = nodesPressure ? element.iPointValuesFromElement(integrationPoint.iPoint, nodesPressure) : undefined;
				const correction = (pressure < thresholdProperties.threshold ? thresholdProperties.correction : 1.0);
				return gravityDirection.map((g) => correction * g * hydraulicConductivity);
			}else{
				return this.inherited(dHRhs, arguments);
			}
		},
		dHDHRhs: function(integrationPoint, dHDH){
			const element = integrationPoint.element, group = element.group, thresholdProperties = this.thresholdCorrection.properties.pressure, nodesPressure = element.nodalDofSolution(), 
				  pressure = nodesPressure ? element.iPointValuesFromElement(integrationPoint.iPoint, nodesPressure) : undefined;
			return matrices.skyMultiplyVector(group.cSky, pressure < thresholdProperties.threshold ? dHDH.map(() => thresholdProperties.correction) : dHDH);
		},
		filtrationVelocity: function filtrationVelocity(rstElementInformation){
			const {reference, element} = rstElementInformation, uncorrectedVelocity = this.inherited(filtrationVelocity, arguments), thresholdProperties = this.thresholdCorrection.properties.pressure, nodesPressure =  element.nodalDofSolution(), 
				  pressure = element.refHValuesFromElement(reference.refH, nodesPressure);
			const result = pressure < thresholdProperties.threshold ? uncorrectedVelocity.map((x) => thresholdProperties.correction * x) : uncorrectedVelocity;
			return result;
		}
	});
});
