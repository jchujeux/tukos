<?php
namespace TukosLib\Objects\Modeling;

class Modeling {

    public static $boundaryOptions = ['essential', 'natural'],
                  $symmetryOptions = ['isotropic', 'orthotropic', 'anisotropic'],
                  $linearityOptions = ['linear', 'nonlinear'],
                  $problemOptions = ['heattransfer', 'hydraulicshead', 'hydraulicspressure', 'solidmechanics', 'fluidmechanics'],
                  $timeDependencyOptions = ['steadyState', 'transport', 'diffusion', 'waves'],
                  $rheologyOptions = ['general', 'linearisotropic', 'linearorthotropic', 'thresholdcorrection'],
                  $elementTypeOptions = ['truss', 'planestrain', 'planestress', 'axisymmetric', 'bidimensionsal', 'tridimensional'];
    
}
?>