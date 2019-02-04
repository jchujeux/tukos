<?php
namespace TukosLib\Utils;

use Aura\Di\Container;

class DiContainer extends Container{
    public function isInstantiated($key){
        return isset($this->services[$key]);
    }
}
?>