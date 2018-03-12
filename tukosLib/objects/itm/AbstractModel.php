<?php
namespace TukosLib\Objects\ITM;

use TukosLib\Objects\AbstractModel as BaseModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ITM\ITM;
use TukosLib\TukosFramework as Tfk;

abstract class AbstractModel extends BaseModel {

    function options($property){
        $name = $property . 'Options';
        return (isset($this->$name) ? $this->$name : ITM::$$name);
    }
}
?>
