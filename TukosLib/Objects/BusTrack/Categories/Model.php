<?php
namespace TukosLib\Objects\BusTrack\Categories;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'vatfree'      => "VARCHAR(31) DEFAULT NULL"
        ];
        parent::__construct($objectName, $translator, 'bustrackcategories', ['parentid' => ['organizations']], [], $colsDefinition, [], ['worksheet', 'custom']);
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['date' => date('Y-m-d')], $init));
    }
}
?>