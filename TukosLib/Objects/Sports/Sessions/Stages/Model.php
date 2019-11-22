<?php
namespace TukosLib\Objects\Sports\Sessions\Stages;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Sports\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'stagetype'  =>"ENUM ('" . implode("','", Sports::$stagetypeOptions) . "') ",
            'duration'     => 'VARCHAR(30)  DEFAULT NULL',
            'intensity'     =>  'TINYINT DEFAULT NULL',
            'stress'         =>  "ENUM ('" . implode("','", Sports::$stressOptions) . "') ",
            'sport'          =>  "ENUM ('" . implode("','", Sports::$sportOptions) . "') ",
        	'summary'          =>  'longtext ',
        	'details'          =>  'longtext ',
        ];
        parent::__construct(
            $objectName, $translator, 'sptsessionsstages',['parentid' => ['sptsessionsstages']], [], $colsDefinition, [], ['statetype', 'intensity', 'stress', 'sport'], ['custom']
        );
    }   
}
?>
