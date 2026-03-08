define([], 
function(){
	return {
		symmetryTypes: ['isotropic', 'orthotropic', 'anisotropic'],
		linearityTypes: ['linear', 'nonlinear'],
		problemTypes: ['heattransfer', 'hydraulicshead', 'hydraulcspressure', 'solidmechanics', 'fluidmechanics'],
		timeDependencyTypes: ['steadystate', 'transport', 'diffusion', 'waves'],
		rheologyTypes: ['general', 'linearisotropic', 'linearorthotropic', 'thresholdcorrection'],
		elementTypes: ['truss', 'planestrain', 'planestress', 'axisymmetric', 'tridimensional'],
		refEltsDimensions: {truss: 1, planestrain: 2, planestress: 2, axisymmetric: 2, bidimensional: 2, tridimensional: 3},
		constants: {g: 10}
	}
});
