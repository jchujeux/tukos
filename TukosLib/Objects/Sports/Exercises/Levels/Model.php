<?php
namespace TukosLib\Objects\Sports\Exercises\Levels;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'level'      => "MEDIUMINT DEFAULT NULL"
        ];
        parent::__construct($objectName, $translator, 'sptexerciseslevels', ['parentid' => ['organizations']], [], $colsDefinition, [], ['custom']);
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['date' => date('Y-m-d')], $init));
    }
}
?>