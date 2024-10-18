<?php
namespace TukosLib\Objects\Sports\Equipments;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'stravagearid' => 'VARCHAR(30) DEFAULT NULL',
            'extraweight'=> 'FLOAT DEFAULT NULL',
            'frictioncoef'=> 'FLOAT DEFAULT NULL',
            'dragcoef'=> 'FLOAT DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'sptequipments',  ['parentid' => ['sptathletes']], [], $colsDefinition);
    }
}
?>

