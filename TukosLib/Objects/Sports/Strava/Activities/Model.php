<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\AbstractModel;
use TukosLib\Strava\API\Client;
use TukosLib\Strava\API\Service\REST;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use Strava\API\OAuth;
use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Objects\Sports\Strava\Activities\Kpis;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as TFK;

class Model extends AbstractModel {

    use Kpis;
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
        ];
        $this->metrics = [
            'duration' => ['stName' => 'elapsed_time'],
            'distance' => ['stName' => 'distance', 'format' => ['type' => 'divide', 'args' => [1000]]],
            'elevationgain' => ['stName' => 'total_elevation_gain'],
            'avghr' => ['stName' => 'average_heartrate', 'format' => ['type' => 'round', 'args' => [0]]],
            'avgpw' => ['stName' => 'average_watts', 'format' => ['type' => 'round', 'args' => [0]]],
            'timemoving' => ['stName' => 'moving_time'],
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
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
        if (!$jsonFilter && (!empty($kpisCacheCols = array_diff(array_keys($values), $this->allCols)))){
            $values['kpiscache'] = json_encode(Utl::extractItems($kpisCacheCols, $values));
        }
        return parent::insert($values, $init, $jsonFilter, $reference);
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
                $oauth = new OAuth(array_merge(Tfk::$registry->getOption('strava'), ['redirectUri' => '']));
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
    public function addStreamsAndMetrics($activity, $athleteId, $needsStreams, $streamCols, $client){
        if ($needsStreams){
            $activity = array_merge($activity, $this->stravaStreamsToTukosStreams($client->getActivityStreams($activity['stravaid'], implode(',', array_map(function($tukosName){return substr($tukosName, 0, -6);}, $streamCols)))));
        }
        $kpisToCompute = ['heartrate_avgload', 'heartrate_load', 'heartrate_timeabove_threshold_90', 'heartrate_timeabove_threshold', 'heartrate_timeabove_threshold_110', 'power_avgload', 'power_load'];
        if ($activity['sport'] === 'running'){
            $kpisToCompute[] = 'mechanical_load';
        }
        $activity = array_merge(Utl::getItem($activity['id'], $this->computeKpis($athleteId, [$activity['id'] => $kpisToCompute], [$activity['id'] => $activity]), [], []), $activity);
        foreach($streamCols as $col){
            if (!empty($activity[$col])){
                $activity[$col] = json_encode($activity[$col]);
            }
        }
        return $activity;
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
        foreach ($stravaActivitiesToSync as $activity){
            $tukosActivity = $this->activityToTukos($activity);
            $tukosActivity['parentid'] = $athleteId;
            $existingTukosActivity = $this->getOne(['where' => ['stravaid' => $activity['id']], 'cols' => ['id', 'timestream']]);
            if (empty($existingTukosActivity)){
                $this->updateOne($tukosActivity = $this->addStreamsAndMetrics($this->insert($tukosActivity), $athleteId, $synchroStreams, $this->streamCols, $client));
            }else{
                $tukosActivity['id'] = $existingTukosActivity['id'];
                if ($this->updateOne($tukosActivity) || ($synchroStreams && empty($existingTukosActivity['timestream']))){
                    $this->updateOne($tukosActivity = $this->addStreamsAndMetrics($tukosActivity, $athleteId, $synchroStreams, $this->streamCols, $client));
                }
            }
        }
    }
}
?>

