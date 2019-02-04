<?php
namespace TukosLib\Objects\Sports\Exercises;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Sports\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

	
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'level1' => 'VARCHAR(30)  DEFAULT NULL',
            'level2' => 'VARCHAR(30)  DEFAULT NULL',
            'level3' =>  'VARCHAR(30)  DEFAULT NULL',
            'visual' =>  'longtext DEFAULT NULL',
            'protocol' =>  'longtext DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'sptexercises',  ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], [], $colsDefinition, [], [], ['custom']
        );
    }   
}
?>
