<?php
namespace TukosLib\Objects\Wine;

use TukosLib\Objects\Wine\Wine;
use TukosLib\Objects\AbstractModel as BaseModel;

abstract class AbstractModel extends BaseModel {

    function options($property){
        $name = $property . 'Options';
        return (isset($this->$name) ? $this->$name : Wine::$$name);
    }
}
?>
