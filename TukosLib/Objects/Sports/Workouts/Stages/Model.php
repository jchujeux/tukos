<?php
namespace TukosLib\Objects\Sports\Workouts\Stages;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'stagetype'  =>"ENUM ('" . implode("','", Sports::$stagetypeOptions) . "') ",
            'duration'     => 'VARCHAR(30)  DEFAULT NULL',
            'intensity'     =>  'TINYINT DEFAULT NULL',
            'stress'         =>  'TINYINT DEFAULT NULL',
            'sport'          =>  "ENUM ('" . implode("','", Sports::$sportOptions) . "') ",
        	'summary'          =>  'longtext ',
        	'details'          =>  'longtext ',
        ];
        parent::__construct(
            $objectName, $translator, 'sptworkoutsstages',['parentid' => ['sptworkoutsstages']], [], $colsDefinition, [], ['statetype', 'intensity', 'stress', 'sport'], ['custom']
        );
    }   
}
?>
