<?php
namespace TukosLib\Objects\Sports\Programs;

trait SessionsFeedbackUtils{

    public function instantiateVersion($version){
        $versionClass = 'TukosLib\\Objects\\Sports\\Programs\\SessionsFeedback' . $version;
        $this->version = new $versionClass();
    }
}
?>
