<?php
namespace TukosLib\Objects\Itm;

use TukosLib\Objects\AbstractModel as BaseModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Itm\Itm;
use TukosLib\TukosFramework as Tfk;

abstract class AbstractModel extends BaseModel {

    function options($property){
        $name = $property . 'Options';
        return (isset($this->$name) ? $this->$name : Itm::$$name);
    }
}
?>
