<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\AbstractModel;
use TukosLib\Strava\API\Client;
use TukosLib\Strava\API\Service\REST;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use Strava\API\OAuth;
use TukosLib\Objects\Sports\KpisFormulaes as KF;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as TFK;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'stravaid' => 'VARCHAR(30) DEFAULT NULL',
            'startdate' => 'VARCHAR(15)  DEFAULT NULL',
            'starttime' => 'VARCHAR(15)  DEFAULT NULL',
            'duration' => 'VARCHAR(30)  DEFAULT NULL',
            'sport' =>  'VARCHAR(30) DEFAULT NULL',
            'gearid' => 'VARCHAR(30) DEFAULT NULL',
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
            //'watts_calcstream' => 'longtext',
            //'grade_smoothstream' => 'longtext',
            //'velocity_smoothstream' => 'longtext',
            'latlngstream' => 'longtext',
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
            'gearid' => ['stName' => 'gear_id'],
            'stravaid' => ['stName' => 'id']
        ];
        $this->metricsCols = array_keys($this->metrics);
        $this->streamCols = ['timestream', 'distancestream', 'altitudestream', 'heartratestream', 'cadencestream', 'wattsstream'/*, 'watts_calcstream', 'grade_smoothstream', 'velocity_smoothstream*/, 'latlngstream'];
        parent::__construct(
            $objectName, $translator, 'stravaactivities',  ['parentid' => ['sptathletes']], [], $colsDefinition);
    }   
    public function hasStreams($id){
        return !empty($this->getOne(['where' => ['id' => $id, ['col' => 'timestream', 'opr' => 'IS NOT NULL', 'values' => null]], 'cols' => ['id']]));
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
    public function addStreams($activity, $streamCols, $client){
        $stravaStreamsName = array_map(function($tukosName){return substr($tukosName, 0, -6);}, $streamCols);
        $activity = array_merge($activity, $this->stravaStreamsToTukosStreams(array_intersect_key($client->getActivityStreams($activity['stravaid'], implode(',', $stravaStreamsName)), array_flip($stravaStreamsName))));
        foreach($streamCols as $col){
            if (!empty($activity[$col])){
                $activity[$col] = json_encode($activity[$col]);
            }
        }
        return $activity;
    }
    public function activitiesToTukos($athleteId, $synchroStart, $synchroEnd, $synchroStreams){
        $athleteModel = Tfk::$registry->get('objectsStore')->objectModel('people'); $itemsValues = [];
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
            $existingTukosActivity = $this->getOne(['where' => ['stravaid' => $activity['id']], 'cols' => ['id', 'timestream'/*, 'watts_calcstream'*/]]);
            if (empty($existingTukosActivity) && $synchroStreams){
                $this->updateOne($tukosActivity = $this->addStreams($this->insert($tukosActivity), $this->streamCols, $client));
            }else{
                $tukosActivity['id'] = $existingTukosActivity['id'];
                $this->updateOne($tukosActivity);
                if ($synchroStreams){
                    if (empty($existingTukosActivity['timestream'])){
                        $this->updateOne($this->addStreams($tukosActivity, $this->streamCols, $client));
                    }else if (empty($existingTukosActivity['latlngstream'])){//temporary: until 10/2024 watt_calcstream was not in streamCols
                        $this->updateOne($this->addStreams($tukosActivity, ['latlngstream'], $client));
                    }
                }
            }
            unset($tukosActivity['parentid']);
            if ($gearid = Utl::extractItem('gearid', $tukosActivity)){
                $gearItem = Tfk::$registry->get('objectsStore')->objectModel('sptequipments')->getOne(['where' => ['stravagearid' => $gearid], 'cols' => ['id', 'extraweight', 'frictioncoef', 'dragcoef']]);
                if (!empty($gearItem)){
                    $gearItem['equipmentid'] = Utl::extractItem('id', $gearItem);
                    $tukosActivity = array_merge($tukosActivity, $gearItem);
                }
            }
            $itemsValues[] = $tukosActivity;
        }
        return $itemsValues;
    }
}
?>

