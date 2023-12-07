<?php
namespace TukosLib\Objects\Sports\Plans;

trait WorkoutsFeedbackUtils{

    public function instantiateVersion($version){
        $versionClass = 'TukosLib\\Objects\\Sports\\Plans\\WorkoutsFeedback' . $version;
        $this->version = new $versionClass();
    }
}
?>
