<?php
/**
 *
 *
 */
namespace TukosLib\Objects\Wine\Regions;

use TukosLib\Objects\Wine\AbstractModel;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = ['country'       => 'VARCHAR(80)  DEFAULT NULL'];
        parent::__construct($objectName, $translator, 'wineregions', [], [], $colsDefinition);
    }
}
?>
