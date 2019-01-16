<?php
namespace TukosLib\Objects\Sports;

use TukosLib\Objects\AbstractModel as BaseModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Sports\Sports;
use TukosLib\TukosFramework as Tfk;

abstract class AbstractModel extends BaseModel {

    function options($property){
        $name = $property . 'Options';
        return (isset($this->$name) ? $this->$name : Sports::$$name);
    }
}
?>
