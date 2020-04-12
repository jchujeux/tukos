<?php
namespace TukosLib\Objects\Sports\Exercises;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

	
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'level1' => 'MEDIUMINT  DEFAULT NULL',
            'level2' => 'MEDIUMINT  DEFAULT NULL',
            'level3' =>  'MEDIUMINT  DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'sptexercises',  ['parentid' => ['organizations']], [], $colsDefinition, [], [], ['custom']
        );
    }   
}
?>
