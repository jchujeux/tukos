<?php
namespace TukosLib\Objects\Sports\Workouts;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\KPISFormulaes as KF;
use TukosLib\Google\Calendar;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\Objects\Sports\Strava\Activities\Kpis;
use TukosLib\TukosFramework as TFK;

class Model extends AbstractModel {

    use Kpis;
    
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'startdate'  => 'VARCHAR(30)  DEFAULT NULL',
            'starttime'  => 'VARCHAR(30)  DEFAULT NULL',
            'duration'   => 'VARCHAR(30)  DEFAULT NULL',
            'intensity'  =>  'TINYINT DEFAULT NULL',
            'stress'     =>  'TINYINT DEFAULT NULL',
            'sport'      =>  "ENUM ('" . implode("','", Sports::$sportOptions) . "')",
            'warmup'     =>  'longtext',
            'mainactivity' =>  'longtext',
            'warmdown'     =>  'longtext',
        	'sportsman' => 'INT(11) DEFAULT NULL', 
        	'warmupdetails' =>  'longtext DEFAULT NULL',
            'mainactivitydetails' =>  'longtext DEFAULT NULL',
            'warmdowndetails'     =>  'longtext DEFAULT NULL',
        	'googleid' => 'VARCHAR(255) DEFAULT NULL',
            'mode' => 'VARCHAR(10) DEFAULT NULL',
            'distance' => 'VARCHAR(10) DEFAULT NULL',
            'elevationgain' => 'VARCHAR(10) DEFAULT NULL',
            'sensations' => 'INT DEFAULT NULL',
            'perceivedeffort' => 'INT DEFAULT NULL',
            'perceivedmechload' => 'INT DEFAULT NULL',
            'mood' => 'INT DEFAULT NULL',
            'athletecomments' => 'longtext DEFAULT NULL',
            'coachcomments' => 'longtext DEFAULT NULL',
            'timemoving' => 'VARCHAR(30)  DEFAULT NULL',
            'avghr' => 'MEDIUMINT DEFAULT NULL',
            'avgpw' => 'MEDIUMINT DEFAULT NULL',
            'heartrate_load' => 'MEDIUMINT DEFAULT NULL',
            'heartrate_avgload' => 'MEDIUMINT DEFAULT NULL',
            'power_load' => 'MEDIUMINT DEFAULT NULL',
            'power_avgload' => 'MEDIUMINT DEFAULT NULL',
            'avgcadence' => 'MEDIUMINT DEFAULT NULL', 
            'mechload' =>'MEDIUMINT DEFAULT NULL',
            'heartrate_timeabove_threshold_90' => 'VARCHAR(10) DEFAULT NULL',
            'heartrate_timeabove_threshold' => 'VARCHAR(10) DEFAULT NULL',
            'heartrate_timeabove_threshold_110' => 'VARCHAR(10) DEFAULT NULL',
            'sts' => 'FLOAT DEFAULT NULL',
            'hracwr' => 'FLOAT DEFAULT NULL',//acute chronic work ratio
            'lts' => 'FLOAT DEFAULT NULL',
            'tsb' => 'FLOAT DEFAULT NULL',
            'stravaid' => 'VARCHAR(30) DEFAULT NULL',
            'kpiscache' => 'longtext'
        ];
        $this->performedCols = ['timemoving', 'sensations', 'perceivedeffort', 'perceivedmechload', 'mood', 'athletecomments', 'coachcomments','sts', 'lts' ,  'tsb', 'avghr', 'avgpw', 'avgcadence', 'heartrate_load', 'power_load',
            'heartrate_avgload', 'power_avgload', 'mechload', 'heartrate_timeabove_threshold_90', 'heartrate_timeabove_threshold', 'heartrate_timeabove_threshold_110', 'stravaid'];
        $this->plannedCols = ['intensity',  'stress', 'warmup', 'mainactivity', 'warmdown', 'warmupdetails', 'mainactivitydetails'];
        
        $this->streamCols = ['timestream', 'distancestream', 'altitudestream', 'heartratestream', 'cadencestream', 'wattsstream', 'grade_smoothstream', 'velocity_smoothstream'];
        parent::__construct($objectName, $translator, 'sptworkouts',  ['parentid' => ['sptplans', 'sptworkouts'], 'sportsman' => ['people']], ['kpiscache'], $colsDefinition);
        $this->removeExtraColsOnSave = false;
        $this->programCache = [];
    }   
    function initialize($init=[]){
        return parent::initialize(array_merge(['warmup' => '', 'mainactivity' => '', 'warmdown' => ''], $init));
    }
    public function hasStreams($id){
        return !empty($this->getOne(['where' => ['id' => $id, ['col' => 'timestream', 'opr' => 'IS NOT NULL', 'values' => null]], 'cols' => ['id']]));
    }
    public function updateHeartrate_AvgLoad($query, $atts){
        $value = [];
        $this->setHeartrate_AvgLoad($value, DUtl::timeToSeconds($query['timemoving']), $query['avghr'], $query['sportsman']);
        return ['data' => empty($value) ? [] : ['value' => $value]];
    }
    public function updatePower_AvgLoad($query, $atts){
        $value = [];
        $this->setPower_AvgLoad($value, DUtl::timeToSeconds($query['timemoving']), $query['avgpw'], $query['sportsman']);
        return ['data' => empty($value) ? [] : ['value' => $value]];
    }
    public function setHeartrate_AvgLoad(&$item, $timemoving, $avgHr, $athleteId){
        if (!empty($athleteId)){
            list('hrmin' => $hrMin, 'hrthreshold' => $hrThreshold, 'sex' => $sex) = Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'sex']]);
            if (($hrMin != $hrThreshold) && !empty($hrThreshold) && !empty($sex)){
                $item['heartrate_avgload'] = intval(KF::avgload($avgHr, $hrThreshold, $timemoving, $sex, $hrMin));
            }
        }
    }
    public function setPower_AvgLoad(&$item, $timemoving, $avgPw, $athleteId){
        if (!empty($athleteId)){
            list('ftp' => $ftp, 'sex' => $sex) = Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['ftp', 'sex']]);
            if (!empty($ftp) && !empty($sex)){
                $item['power_avgload'] = intval(KF::avgload($avgPw, $ftp, $timemoving, $sex));
                    
            }
        }
    }
    public function getAllExtended($atts){
        /*
         * With client strava synchronization approach, the server synchronization for restricted users is not functional anymore
         * if ($this->user->isRestrictedUser() && $programId = Utl::getItem('parentid', $atts['where'])){
            $mostRecentPerformed = $this->getOne(['where' => ['parentid' => $programId, 'mode' => 'performed'], 'cols' => ['startdate'], 'orderBy' => ['startdate' => 'DESC']]);
            $programModel = Tfk::$registry->get('objectsStore')->objectModel('sptplans');
            $programInfo = $programModel->getOne(['where' => ['id' => $programId], 'cols' => ['id', 'parentid', 'fromdate', 'googlecalid']]);
            if (isset($programInfo['fromdate'])){
                $programModel->stravaProgramSynchronize(array_merge($programInfo, ['ignoreworkoutflag' => false, 'synchrostart' => empty($mostRecentPerformed) ? $programInfo['fromdate'] : $mostRecentPerformed['startdate'], 'synchroend' => date('Y-m-d'),
                    'synchrostreams' => true]));
            }
        }*/
        //$atts['cols'][] = 'kpiscache';
        $results = parent::getAllExtended($atts);
        foreach ($results as &$workout){
            if (!empty($kpisCache = Utl::extractItem('kpiscache', $workout))){
                $workout = array_merge($workout, json_decode($kpisCache, true));
            }
        }
        return $results;
    }
    public function updateOne($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (!empty($kpisCacheCols = array_diff(array_keys($newValues), $this->allCols))){
            $newValues['kpiscache'] = Utl::extractItems($kpisCacheCols, $newValues);
        }
        return parent::updateOne($newValues, $atts, $insertIfNoOld, true, $init);

    }
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
        if (!empty($kpisCacheCols = array_diff(array_keys($values), $this->allCols))){
            $values['kpiscache'] = Utl::extractItems($kpisCacheCols, $values);
        }
        return parent::insert($values, $init, true, $reference);
    }
    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        $updated =  parent::updateOneExtended($newValues, $atts, $insertIfNoOld, $jsonFilter, $init);
        if ($updated){
            $this->workoutGoogleSynchronize($updated);
        }
        return $updated;
    }
    public function insertExtended($values, $init=false, $jsonFilter = false){
        $workout = parent::insertExtended($values, $init, $jsonFilter);
        if (Utl::getItem('grade', $workout) !== 'TEMPLATE'){
            $this->workoutGoogleSynchronize($workout);
        }
        return $workout;
    }
    public function delete ($where){
        try{
            if ($workoutId = Utl::getItem('id', $where)){
                list('parentid' => $programId, 'googleid' => $googleId) = $this->getOne(['where' => ['id' => $workoutId], 'cols' => ['parentid', 'googleid']]);
                if ($googleId && ($calId = Utl::getItem('googlecalid', $this->getProgram($programId)))){
                    Calendar::deleteEvent($calId, $googleId);
                }
            }
        }catch(\Exception $e){
            Feedback::add('calendareventtodeletenotfound');
        }
        return parent::delete($where);
    }
    public function workoutGoogleSynchronize($workout){
        if (!($parentProgram = Utl::getItem('parentid', $workout))){
            if (!empty($dbWorkout = $this->getOne(['where' => ['id' => $workout['id']], 'cols' => ['parentid']]))){
                $parentProgram = Utl::getItem('parentid', $dbWorkout);
                if (!$parentProgram){
                    Feedback::add($this->tr('programnotfound') . ': ' . $workout['id']);
                    return;
                }
            }else{
                Feedback::add($this->tr('itemNotFound') . ': ' . $workout['id']);
                return;
            }
        }
        if (($programId = Utl::getItem('id', $this->getProgram($parentProgram))) && ($calId = Utl::getItem('googlecalid', $this->programCache))){
            $this->programsModel->googleSynchronizeOne($programId, $calId, $workout['id'], true, true, '', 'V2');
        }
    }
    public function getProgram($programId){
        if (!($programId === Utl::getItem('id', $this->programCache))){
            $this->programsModel = Tfk::$registry->get('objectsStore')->objectModel('sptplans');
            $this->programCache = $this->programsModel->getOne(['where' => ['id' => $programId], 'cols' => ['id', 'parentid', 'googlecalid', 'coachorganization']]);
        }
        return $this->programCache;
    }
    public function getCoachOrganization($programId){
        if (!$organization = Utl::getItem('organization', $this->getProgram($programId))){
            if ($organizationId = Utl::getItem('coachorganization', $this->programCache)){
                $organizationsModel = Tfk::$registry->get('objectsStore')->objectModel('organizations');
                $organization = $organizationsModel->getOne(['where' => ['id' => $organizationId], 'cols' => ['id', 'logo']]);
                $this->programCache['organization'] = empty($organization) ? [] : $organization;
            }else{
                Feedback::add('Nocoachorganization');
                $this->programCache['organization'] = [];
            }
        }
        return $this->programCache['organization'];
    }
    public function getLogoImage($programId){
        return Utl::getItem('logo', $this->getCoachOrganization($programId), Tfk::$registry->logo, Tfk::$registry->logo);
    }
    public function getLogoUrl($programId){
        return HUtl::imageUrl($this->getLogoImage($programId));
    }
    public function getKpis($query, $kpisToGet){// associated to process action
        return ['data' => ['kpis' => $this->computeKpis($query['athleteid'], $kpisToGet, 'stravaid')]];
    }
}
?>

