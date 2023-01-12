<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\TrainingFormulaes as TF;
use TukosLib\Google\Calendar;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
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
        	'sessionid' => 'VARCHAR(10) DEFAULT NULL',
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
            'hr95' => 'MEDIUMINT DEFAULT NULL',
            'trimphr' => 'MEDIUMINT DEFAULT NULL',
            'trimpavghr' => 'MEDIUMINT DEFAULT NULL',
            'trimppw' => 'MEDIUMINT DEFAULT NULL',
            'trimpavgpw' => 'MEDIUMINT DEFAULT NULL',
            'avgcadence' => 'MEDIUMINT DEFAULT NULL', 
            'mechload' =>'MEDIUMINT DEFAULT NULL',
            'h4time' => 'VARCHAR(10) DEFAULT NULL',
            'h5time' => 'VARCHAR(10) DEFAULT NULL',
            'sts' => 'FLOAT DEFAULT NULL',
            'lts' => 'FLOAT DEFAULT NULL',
            'tsb' => 'FLOAT DEFAULT NULL',
            'stravaid' => 'VARCHAR(30) DEFAULT NULL',
            'timestream' => 'longtext',
            'distancestream' => 'longtext',
            'altitudestream' => 'longtext',
            'heartratestream' => 'longtext',
            'cadencestream' => 'longtext',
            'wattsstream' => 'longtext',
            'grade_smoothstream' => 'longtext',
            'velocity_smoothstream' => 'longtext',
            'kpiscache' => 'longtext'
        ];
        $this->performedCols = ['timemoving', 'sensations', 'perceivedeffort', 'mood', 'athletecomments', 'coachcomments','sts', 'lts' ,  'tsb', 'avghr', 'avgpw', 'hr95', 'trimphr', 'trimppw', 'trimpavghr', 'trimpavgpw', 'mechload', 'h4time', 'h5time', 'stravaid'];
        $this->plannedCols = ['intensity',  'stress', 'warmup', 'mainactivity', 'warmdown', 'warmupdetails', 'mainactivitydetails'];
        
        $this->streamCols = ['timestream', 'distancestream', 'altitudestream', 'heartratestream', 'cadencestream', 'wattsstream', 'grade_smoothstream', 'velocity_smoothstream'];
        parent::__construct($objectName, $translator, 'sptsessions',  ['parentid' => ['sptprograms', 'sptsessions'], 'sportsman' => ['people']], ['kpiscache'], $colsDefinition);
        $this->programCache = [];
    }   
    function initialize($init=[]){
        return parent::initialize(array_merge(['warmup' => '', 'mainactivity' => '', 'warmdown' => '', 'sessionid' => 1], $init));
    }
    function adjustSessionId($query){
        $existingSessionIds = array_column($this->getAll ([
            'where' => ['parentid' => $query['parentid'], 'startdate' => $query['startdate'], 
                ($query['mode'] === 'performed') ? ['col' => 'mode', 'opr' => '=', 'values' => 'performed'] : [['col' => 'mode', 'opr' => '!=', 'values' => 'performed'], ['col' => 'mode', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]], 
            'cols' => ['sessionid']]), 'sessionid');
        $sessionId = empty($existingSessionIds) ? 1 : (max($existingSessionIds) + 1);
        return ['data' => ['value' => ['sessionid' => $sessionId]]];
    }
    public function hasStreams($id){
        return !empty($this->getOne(['where' => ['id' => $id, ['col' => 'timestream', 'opr' => 'IS NOT NULL', 'values' => null]], 'cols' => ['id']]));
    }
    public function updateTrimpAvgHr($query, $atts){
        $value = [];
        $this->setTrimpAvgHr($value, DUtl::timeToMinutes($query['timemoving']), $query['avghr'], $query['sportsman']);
        return ['data' => empty($value) ? [] : ['value' => $value]];
    }
    public function updateTrimpAvgPw($query, $atts){
        $value = [];
        $this->setTrimpAvgPw($value, DUtl::timeToMinutes($query['timemoving']), $query['avgpw'], $query['sportsman']);
        return ['data' => empty($value) ? [] : ['value' => $value]];
    }
    public function setTrimpAvgHr(&$item, $timemoving, $avgHr, $athleteId){
        if (!empty($athleteId)){
            list('hrmin' => $hrMin, 'hrthreshold' => $hrThreshold, 'sex' => $sex) = Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'sex']]);
            if (($hrMin != $hrThreshold) && !empty($hrThreshold) && !empty($sex)){
                $item['trimpavghr'] = intval(TF::avgHrTrainingload($avgHr, $hrMin, $hrThreshold, $timemoving, $sex));
            }
        }
    }
    public function setTrimpAvgPw(&$item, $timemoving, $avgPw, $athleteId){
        if (!empty($athleteId)){
            list('ftp' => $ftp, 'sex' => $sex) = Tfk::$registry->get('objectsStore')->objectModel('sptathletes')->getOne(['where' => ['id' => $athleteId], 'cols' => ['ftp', 'sex']]);
            if (!empty($ftp) && !empty($sex)){
                $item['trimpavgpw'] = intval(TF::avgPwTrainingload($avgPw, $ftp, $timemoving, $sex));
            }
        }
    }
    public function getAll ($atts, $jsonColsPaths = [], $jsonNotFoundValues = null, $processLargeCols = false){
        $atts['cols'][] = 'kpiscache';
        $results = parent::getAll($atts, $jsonColsPaths, $jsonNotFoundValues, $processLargeCols);
        foreach ($results as &$session){
            if (!empty($kpisCache = Utl::extractItem('kpiscache', $session))){
                $session = array_merge($session, json_decode($kpisCache, true));
            }
        }
        return $results;
    }
    public function getAllExtended($atts){
        if ($this->user->isRestrictedUser() && $programId = Utl::getItem('parentid', $atts['where'])){
            $mostRecentPerformed = $this->getOne(['where' => ['parentid' => $programId, 'mode' => 'performed'], 'cols' => ['startdate'], 'orderBy' => ['startdate' => 'DESC']]);
            $programModel = Tfk::$registry->get('objectsStore')->objectModel('sptprograms');
            $programInfo = $programModel->getOne(['where' => ['id' => $programId], 'cols' => ['id', 'parentid', 'fromdate', 'googlecalid']]);
            $programModel->stravaProgramSynchronize(array_merge($programInfo, ['ignoresessionflag' => false, 'synchrostart' => empty($mostRecentPerformed) ? $programInfo['fromdate'] : DUtl::dayAfter($mostRecentPerformed['startdate']), 'synchroend' => date('Y-m-d'),
                'synchrostreams' => true]));
        }
        return parent::getAllExtended($atts);
    }
    public function updateOne($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (!$jsonFilter && (!empty($kpisCacheCols = array_diff(array_keys($newValues), $this->allCols)))){
            $newValues['kpiscache'] = json_encode(Utl::extractItems($kpisCacheCols, $newValues));
        }
        return parent::updateOne($newValues, $atts, $insertIfNoOld, true, $init);

    }
    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        $updated =  parent::updateOneExtended($newValues, $atts, $insertIfNoOld, $jsonFilter, $init);
        if ($updated){
            $this->sessionGoogleSynchronize($updated);
        }
        return $updated;
    }
    public function insertExtended($values, $init=false, $jsonFilter = false){
        $session = parent::insertExtended($values, $init, $jsonFilter);
        if (Utl::getItem('grade', $session) !== 'TEMPLATE'){
            $this->sessionGoogleSynchronize($session);
        }
        return $session;
    }
    public function delete ($where){
        if ($sessionId = Utl::getItem('id', $where)){
            list('parentid' => $programId, 'googleid' => $googleId) = $this->getOne(['where' => ['id' => $sessionId], 'cols' => ['parentid', 'googleid']]);
            if ($googleId && ($calId = Utl::getItem('googlecalid', $this->getProgram($programId)))){
                Calendar::deleteEvent($calId, $googleId);
            }
        }
        return parent::delete($where);
    }
    public function sessionGoogleSynchronize($session){
        if (!($programId = Utl::getItem('parentid', $session))){
            $programId = $this->getOne(['where' => ['id' => $session['id']], 'cols' => ['parentid']])['parentid'];
        }
        $programId = Utl::getItem('id', $this->getProgram($programId));
        if (($programId = Utl::getItem('id', $this->getProgram($programId))) && ($calId = Utl::getItem('googlecalid', $this->programCache))){
            $this->programsModel->googleSynchronizeOne($programId, $calId, $session['id'], true, true, '', 'V2');
        }
    }
    public function getProgram($programId){
        if (!($programId === Utl::getItem('id', $this->programCache))){
            $this->programsModel = Tfk::$registry->get('objectsStore')->objectModel('sptprograms');
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
}
?>

