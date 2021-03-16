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
            'level3' => 'MEDIUMINT  DEFAULT NULL',
            'stress' => 'TINYINT DEFAULT NULL',
            'series' => 'TINYINT DEFAULT NULL',
            'repeats'=> 'VARCHAR(30)  DEFAULT NULL',
            'extra' => 'VARCHAR(30)  DEFAULT NULL',
            'extra1' => 'VARCHAR(30)  DEFAULT NULL',
            'progression' => 'longtext',
        ];
        parent::__construct(
            $objectName, $translator, 'sptexercises',  ['parentid' => ['organizations']], [], $colsDefinition, [], [], ['custom']
        );
    }   
}
?>
