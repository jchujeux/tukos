<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\GoldenCheetah as GC;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
                'startdate'  => 'VARCHAR(30)  DEFAULT NULL',
                'duration'   => 'VARCHAR(30)  DEFAULT NULL',
                'intensity'  =>  'TINYINT DEFAULT NULL',
                'stress'     =>  'TINYINT DEFAULT NULL',
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
                'sensations' => 'INT DEFAULT NULL',
                'perceivedeffort' => 'INT DEFAULT NULL',
                'mood' => 'INT DEFAULT NULL',
                'athletecomments' => 'longtext DEFAULT NULL',
                'coachcomments' => 'longtext DEFAULT NULL',
                'timemoving' => 'VARCHAR(30)  DEFAULT NULL',
                'avghr' => 'MEDIUMINT DEFAULT NULL',
                'avgpw' => 'MEDIUMINT DEFAULT NULL',
                'hr95' => 'MEDIUMINT DEFAULT NULL',
                'trimphr' => 'MEDIUMINT DEFAULT NULL',
                'trimppw' => 'MEDIUMINT DEFAULT NULL',
                'avgcadence' => 'MEDIUMINT DEFAULT NULL', 
                'mechload' =>'MEDIUMINT DEFAULT NULL',
                'h4time' => 'VARCHAR(10) DEFAULT NULL',
                'h5time' => 'VARCHAR(10) DEFAULT NULL',
                'sts' => 'FLOAT DEFAULT NULL',
                'lts' => 'FLOAT DEFAULT NULL',
                'tsb' => 'FLOAT DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'sptsessions',  ['parentid' => ['sptprograms', 'sptsessions'], 'sportsman' => ['people']], [], $colsDefinition);
    }   
    function initialize($init=[]){
        return parent::initialize(array_merge(['warmup' => '', 'mainactivity' => '', 'warmdown' => '', 'sessionid' => 1], $init));
    }
    function adjustSessionId($query, $values){
        $existingSessionIds = array_column($this->getAll ([
            'where' => ['parentid' => $query['parentid'], 'startdate' => $query['startdate'], 
                ($query['mode'] === 'performed') ? ['col' => 'mode', 'opr' => '=', 'values' => 'performed'] : [['col' => 'mode', 'opr' => '!=', 'values' => 'performed'], ['col' => 'mode', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]], 
            'cols' => ['sessionid']]), 'sessionid');
        $sessionId = empty($existingSessionIds) ? 1 : (max($existingSessionIds) + 1);
        return ['data' => ['sessionid' => $sessionId]];
    }
}
?>

