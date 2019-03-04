<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Sports\AbstractModel;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'startdate'  => 'VARCHAR(30)  DEFAULT NULL',
            'duration'   => 'VARCHAR(30)  DEFAULT NULL',
            'intensity'  =>  "ENUM ('" . implode("','", Sports::$intensityOptions) . "')",
            'stress'     =>  "ENUM ('" . implode("','", Sports::$stressOptions) . "')",
            'sport'      =>  "ENUM ('" . implode("','", Sports::$sportOptions) . "')",
            'warmup'     =>  'longtext',
            'mainactivity' =>  'longtext',
            'warmdown'     =>  'longtext',
        	'sessionid' => 'VARCHAR(10) DEFAULT NULL',
        	'sportsman' => 'INT(11) DEFAULT NULL', 
        	'difficulty' => 'VARCHAR(10) DEFAULT NULL',
        	'warmupdetails' =>  'longtext',
            'mainactivitydetails' =>  'longtext',
            'warmdowndetails'     =>  'longtext',
        		'googleid' => 'VARCHAR(255) DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'sptsessions',  ['parentid' => ['sptprograms', 'sptsessions'], 'sportsman' => ['people']], [], $colsDefinition, [], ['intensity', 'stress', 'sport', 'difficulty'], ['worksheet', 'custom']
        );
    }   
    function initialize($init=[]){
        return parent::initialize(array_merge(['duration' => json_encode([60, 'minute']), 'warmup' => '', 'mainactivity' => '', 'warmdown' => ''], $init));
    }
}
?>
