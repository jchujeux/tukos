<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\AbstractModel;
use TukosLib\Strava\API\Client;
use TukosLib\Strava\API\Service\REST;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use Strava\API\OAuth;
use TukosLib\Objects\Sports\TrainingFormulaes as TF;
use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as TFK;

class Model extends AbstractModel {

    //use Kpis;
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'stravaid' => 'VARCHAR(30) DEFAULT NULL',
            'startdate' => 'VARCHAR(15)  DEFAULT NULL',
            'starttime' => 'VARCHAR(15)  DEFAULT NULL',
            'duration' => 'VARCHAR(30)  DEFAULT NULL',
            'sport' =>  'VARCHAR(30) DEFAULT NULL',
            'distance' => 'VARCHAR(10) DEFAULT NULL',
            'elevationgain' => 'VARCHAR(10) DEFAULT NULL',
            'timemoving' => 'VARCHAR(30)  DEFAULT NULL',
            'avghr' => 'MEDIUMINT DEFAULT NULL',
            'avgpw' => 'MEDIUMINT DEFAULT NULL',
            'avgcadence' => 'MEDIUMINT DEFAULT NULL',
            'timestream' => 'longtext',
            'distancestream' => 'longtext',
            'altitudestream' => 'longtext',
            'heartratestream' => 'longtext',
            'cadencestream' => 'longtext',
            'wattsstream' => 'longtext',
            'grade_smoothstream' => 'longtext',
            'velocity_smoothstream' => 'longtext',
            'kpiscache' => 'longtext'
            
            /*'hr95' => 'MEDIUMINT DEFAULT NULL',
            'trimphr' => 'MEDIUMINT DEFAULT NULL',
            'trimpavghr' => 'MEDIUMINT DEFAULT NULL',
            'trimppw' => 'MEDIUMINT DEFAULT NULL',
            'trimpavgpw' => 'MEDIUMINT DEFAULT NULL',
            'mechload' =>'MEDIUMINT DEFAULT NULL',
            'h4time' => 'VARCHAR(10) DEFAULT NULL',
            'h5time' => 'VARCHAR(10) DEFAULT NULL',
            'sts' => 'FLOAT DEFAULT NULL',
            'lts' => 'FLOAT DEFAULT NULL',
            'tsb' => 'FLOAT DEFAULT NULL',*/
        ];
        $this->metrics = [
            'duration' => ['stName' => 'elapsed_time', 'format' => ['type' => 'divide', 'args' => [60]]],
            'distance' => ['stName' => 'distance', 'format' => ['type' => 'divide', 'args' => [1000]]],
            'elevationgain' => ['stName' => 'total_elevation_gain'],
            'avghr' => ['stName' => 'average_heartrate', 'format' => ['type' => 'round', 'args' => [0]]],
            'avgpw' => ['stName' => 'average_watts', 'format' => ['type' => 'round', 'args' => [0]]],
            'timemoving' => ['stName' => 'moving_time', 'format' => ['type' => 'divide', 'args' => [60]]],
            'avgcadence' => ['stName' => 'average_cadence', 'format' => ['type' => 'round', 'args' => [0]]],
            'startdate' => ['stName' => 'start_date'],
            'sport' => ['stName' => 'type', 'format' => ['type' => 'map', 'args' => ['Ride' => 'bicycle', 'VirtualRide' => 'bicycle', 'Run' => 'running', 'Swim' => 'swimming', 'Crossfit' => 'bodybuilding']]],
            'name' => ['stName' => 'name'],
            'stravaid' => ['stName' => 'id']
            //'notes' => [],
        ];
        $this->metricsCols = array_keys($this->metrics);
        $this->streamCols = ['timestream', 'distancestream', 'altitudestream', 'heartratestream', 'cadencestream', 'wattsstream', 'grade_smoothstream', 'velocity_smoothstream'];
        parent::__construct(
            $objectName, $translator, 'stravaactivities',  ['parentid' => ['sptathletes']], ['kpiscache'], $colsDefinition);
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
                //$item['trimpavghr'] = intval(TF::avgHrTrainingload($avgHr, $hrMin, $hrThreshold, $timemoving, $sex));
                $item['trimpavghr'] = intval(KF::avgload($avgHr, $hrThreshold, $timemoving, $sex, $hrMin));
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
        foreach ($results as &$activity){
            if (!empty($kpisCache = Utl::extractItem('kpiscache', $activity))){
                $activity = array_merge($activity, json_decode($kpisCache, true));
            }
        }
        return $results;
    }
    public function updateOne($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (!$jsonFilter && (!empty($kpisCacheCols = array_diff(array_keys($newValues), $this->allCols)))){
            $newValues['kpiscache'] = json_encode(Utl::extractItems($kpisCacheCols, $newValues));
        }
        return parent::updateOne($newValues, $atts, $insertIfNoOld, true, $init);
    }
    public function activityToTukos($activity){
        $tukosActivity = [];
        foreach($this->metricsCols as $col){
            if ($value = Utl::getItem($this->metrics[$col]['stName'], $activity)){
                $tukosActivity[$col] = ($format = Utl::getItem('format', $this->metrics[$col])) ? $this->format($value, $format) : $value;
            }
        }
        list($tukosActivity['startdate'], $tukosActivity['starttime']) = explode('T', $tukosActivity['startdate']);
        return $tukosActivity;
    }
    public function format($value, $format){
        switch($format['type']){
            case 'round':
                return round($value, $format['args'][0]);
            case 'divide':
                return $value / $format['args'][0];
            case 'map':
                return Utl::getItem($value, $format['args'], 'other');
        }
    }
    public function getAthleteClient($athleteId){
        if (!property_exists($this, 'adapter')){
            $this->adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
        }
        $athletesModel = Tfk::$registry->get('objectsStore')->objectModel('sptathletes');
        $options = json_decode($athletesModel->getOne(['where' => ['id' => $athleteId], 'cols' => ['stravainfo']])['stravainfo'], true);
        if (is_array($options)){
            $token = new AccessToken($options);
            if ($token->hasExpired()){
                $oauth = new OAuth(array_merge(Tfk::$registry->get('tukosModel')->getOption('strava'), ['redirectUri' => '']));
                $token = $oauth->getAccessToken('refresh_token', ['refresh_token' => $token->getRefreshToken()]);
                $athletesModel->updateItems(['stravainfo' => json_encode(['access_token' => $token->getToken(), 'refresh_token' => $token->getRefreshToken(), 'expires' => $token->getExpires()])], ['table' => 'people', 'where' => ['id' => $athleteId]]);
            }
            return new Client(new REST($token, $this->adapter));
        }else{
            return false;
        }
    }
    public function stravaStreamsToTukosStreams($stravaStreams){
        $tukosStreams = [];
        foreach ($stravaStreams as $name => $stream){
            $tukosStreams[$name. 'stream'] = $stream['data'];
        }
        return $tukosStreams;
    }
    public function stravaStreamToElapsedValue($times, $stravaStreamData, $delta = 1){// transforms in [elapsedTime, value], eliminating consecutive pairs with same value
        $tukosStreamData = []; $inactiveDuration = 0; $initialIntervalValue = 0;
        foreach ($times as $activeTime => $elapsedTime){
            if ($activeTime + $inactiveDuration < $elapsedTime){
                $tukosStreamData[] = [$activeTime + $inactiveDuration, 0];
                $tukosStreamData[] = [$elapsedTime - 1, 0];
                $inactiveDuration = $elapsedTime - $activeTime;
                $initialIntervalValue = 0;
            }
            if (abs($stravaStreamData[$activeTime] - $initialIntervalValue) >= $delta){
                $tukosStreamData[] = [$elapsedTime, ($initialIntervalValue = $stravaStreamData[$activeTime])];
            }
        }
        return $tukosStreamData;
    }
    public function addStreamsAndMetrics($session, $athleteParams, $needsStreams, $streamCols, $client){
        list('hrmin' => $hrMin, 'hrthreshold' => $hrThreshold, 'h4timethreshold' => $h4timeThreshold, 'h5timethreshold' => $h5timeThreshold, 'ftp' => $ftp, 'speedthreshold' => $speedThreshold, 'sex' => $sex) = $athleteParams;
        if ($needsStreams){
            $session = array_merge($session, $this->stravaStreamsToTukosStreams($client->getActivityStreams($session['stravaid'], implode(',', array_map(function($tukosName){return substr($tukosName, 0, -6);}, $streamCols)))));
        }
        if (($hrMin != $hrThreshold) && !empty($hrThreshold) && !empty($sex)){
            if (!empty($session['avghr']) && !empty($session['timemoving'])){
                $session['trimpavghr'] = intval(TF::avgHrTrainingload($session['avghr'], $hrMin, $hrThreshold, $session['timemoving'], $sex));
                if (!empty($session['heartratestream'])){
                    $session['trimphr'] = TF::hrTrainingLoad($session['heartratestream'], $hrMin, $hrThreshold, $sex);
                    list($lower, $session['h4time'], $session['h5time']) = TF::timeInZones($session['heartratestream'], [$h4timeThreshold, $h5timeThreshold], 3);
                }
            }
        }
        if (!empty($ftp) && !empty($sex)){
            if ( !empty($session['avgpw'])){
                $session['trimpavgpw'] = intval(TF::avgPwTrainingload($session['avgpw'], $ftp, $session['timemoving'], $sex));
            }
            if (!empty($session['wattsstream'])){
                $session['trimppw'] = TF::pwTrainingLoad($session['wattsstream'], $ftp, $sex, 30);
            }
        }
        if ($session['sport'] === 'running' && !empty($session['cadencestream']) && !empty($session['distancestream']) && !empty($speedThreshold)){
            $session['mechload'] = TF::runningMechanicalLoad($session['distancestream'], $session['cadencestream'], $speedThreshold / 0.36);
        }
        foreach($streamCols as $col){
            if (!empty($session[$col])){
                $session[$col] = json_encode($session[$col]);
            }
        }
        return $session;
    }
    public function activitiesToTukos($athleteId, $synchroStart, $synchroEnd, $synchroStreams, $stravaColsToGet){
        $athleteModel = Tfk::$registry->get('objectsStore')->objectModel('people');
        try{
            $client = $this->getAthleteClient($athleteId);
            $stravaActivitiesToSync = $client->getAthleteActivities(strtotime(DUtl::dayAfter($synchroEnd)), strtotime($synchroStart));
        } catch(\Exception $e){
            $message = $e->getMessage();
            if (strpos($message, "Authorization Error") > 0){
                Feedback::add($this->tr('Stravaauthorizationnomorevalid'));
                $athleteModel->updateOne(['id' => $athleteId, 'stravainfo' => null]);
            }else{
                Feedback::add($this->tr('Couldnotretrievefromstrava') . ': ' . $message);
            }
            return [];
        }
        if(empty($stravaActivitiesToSync)){
            Feedback::add($this->tr('Noactivitytosyncforathlete') . '  ' . $athleteId);
            return [];
        }
        $athleteParams = $athleteModel->getOne(['where' => ['id' => $athleteId], 'cols' => ['hrmin', 'hrthreshold', 'h4timethreshold', 'h5timethreshold', 'ftp', 'speedthreshold', 'sex']]);
        $insertedStravaCols = []; $updatedStravaCols = [];
        foreach ($stravaActivitiesToSync as $activity){
            $tukosActivity = $this->activityToTukos($activity);
            $tukosActivity['parentid'] = $athleteId;
            $existingTukosActivity = $this->getOne(['where' => ['stravaid' => $activity['id']], 'cols' => ['id', 'timestream']]);
            if (empty($existingTukosActivity)){
                $this->insert($tukosActivity = $this->addStreamsAndMetrics($tukosActivity, $athleteParams, $synchroStreams, $this->streamCols, $client));
                $insertedStravaCols[$tukosActivity['stravaid']] = Utl::getItems($stravaColsToGet, $tukosActivity);
            }else{
                if ($updated = $this->updateOne($tukosActivity, ['where' => ['id' => $existingTukosActivity['id']]]) || ($synchroStreams && empty($existingTukosActivity['timestream']))){
                    $this->updateOne($tukosActivity = $this->addStreamsAndMetrics($tukosActivity, $athleteParams, $synchroStreams, $this->streamCols, $client));
                    $updatedStravaCols[$tukosActivity['stravaid']] = Utl::getItems($stravaColsToGet, $tukosActivity);
                }
            }
        }
        return ['inserted' => $insertedStravaCols, 'updated' => $updatedStravaCols];
    }
}
?>

