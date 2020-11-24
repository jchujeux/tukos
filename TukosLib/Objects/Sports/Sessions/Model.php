<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\GoldenCheetah as GC;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = array_merge([
                'startdate'  => 'VARCHAR(30)  DEFAULT NULL',
                'duration'   => 'VARCHAR(30)  DEFAULT NULL',
                'intensity'  =>  'TINYINT DEFAULT NULL',
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
                'mode' => 'VARCHAR(10) DEFAULT NULL',
                'distance' => 'VARCHAR(10) DEFAULT NULL',
                'elevationgain' => 'VARCHAR(10) DEFAULT NULL',
                'feeling' => 'VARCHAR(255) DEFAULT NULL',
                'sensations' => 'INT DEFAULT NULL',
                'perceivedeffort' => 'INT DEFAULT NULL',
                'mood' => 'INT DEFAULT NULL',
                'athletecomments' => 'VARCHAR(512) DEFAULT NULL',
                'athleteweeklyfeeling' => 'VARCHAR(512) DEFAULT NULL',
                'coachcomments' => 'VARCHAR(512) DEFAULT NULL',
                'coachweeklycomments' => 'VARCHAR(512) DEFAULT NULL',
                'sts' => 'FLOAT DEFAULT NULL',
                'lts' => 'FLOAT DEFAULT NULL',
                'tsb' => 'FLOAT DEFAULT NULL',
        ],
            GC::sessionsColsDefinition());
        parent::__construct(
            $objectName, $translator, 'sptsessions',  ['parentid' => ['sptprograms', 'sptsessions'], 'sportsman' => ['people']], [], $colsDefinition);
    }   
    function initialize($init=[]){
        return parent::initialize(array_merge(['duration' => '60', 'warmup' => '', 'mainactivity' => '', 'warmdown' => ''], $init));
    }
}
?>

