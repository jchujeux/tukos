<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\Strava\Activities\StreamsCorrection;
use TukosLib\Strava\API\Client;
use TukosLib\Strava\API\Service\REST;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use Strava\API\OAuth;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\ConversionUtilities as CUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as TFK;

class Model extends AbstractModel {

    use StreamsCorrection;
    
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
            'latitudestream' => 'longtext',
            'longitudestream' => 'longtext',
            'timemovingc' => 'VARCHAR(30)  DEFAULT NULL',
            'timestreamc' => 'longtext',
            'distancestreamc' => 'longtext',
            'altitudestreamc' => 'longtext',
            'heartratestreamc' => 'longtext',
            'cadencestreamc' => 'longtext',
            'wattsstreamc' => 'longtext',
            'latitudestreamc' => 'longtext',
            'longitudestreamc' => 'longtext',
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
    public function originalStreamCols(){
        return isset($this->originalStreamCols) ? $this->originalStreamCols : $this->originalStreamCols = array_merge(array_filter($this->streamCols, function($col){return $col !== 'latlngstream';}), ['latitudestream', 'longitudestream']);
    }
    public function correctedStreamCols(){
        return isset($this->correctedStreamCols) ? $this->correctedStreamCols : $this->correctedStreamCols = array_map(function($col){return $col . 'c';}, $this->originalStreamCols());
    }
    public function tukosStreamCols(){
        return isset($this->tukosStreamCols) ? $this->tukosStreamCols : $this->tukosStreamCols = array_merge($this->originalStreamCols(), $this->correctedStreamCols());
    }
    public function getOneCorrected ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null, $absentColsFlag = 'forbid'){
        $isCorrected = false;
        if (!empty($this->getOne(['where' => array_merge($atts['where'], [['col' => 'timestreamc', 'opr' => 'IS NOT NULL', 'values' => null]]), 'cols' => ['id']]))){
            $isCorrected = true;
            foreach($atts['cols'] as &$col){
                if (substr($col, -6) === 'stream' || $col === 'timemoving'){
                    $col = $col . 'c';
                }
            }
        }
        $result = $this->getOne($atts, $jsonColsPaths, $jsonNotFoundValue, $absentColsFlag);
        if ($isCorrected){
            foreach($result as $col => $value){
                if (substr($col, -7) === 'streamc' || $col === 'timemovingc'){
                    unset($result[$col]);
                    $result[substr($col, 0, -1)] = $value;
                }
            }
        }
        return $result;
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
    public function addStreams($activity, $streamCols, $client){
        $stravaStreamsName = array_map(function($tukosName){return substr($tukosName, 0, -6);}, $streamCols);
        $activity = array_merge($activity, $this->stravaStreamsToTukosStreams(array_intersect_key($client->getActivityStreams($activity['stravaid'], implode(',', $stravaStreamsName)), array_flip($stravaStreamsName))));
        $this->addCorrectedStreams($activity);
        foreach($activity as $col => $value){
            if (!empty($activity[$col]) && (substr($col, -6) === 'stream' || substr($col, -7) === 'streamc')){
                $activity[$col] = json_encode($value);
            }
        }
        return $activity;
    }
    public function activitiesToTukos($athleteId, $synchroStart, $synchroEnd, $geoSource, $synchroStreams){
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
                    }else if (empty($existingTukosActivity['latlngstream'])){//temporary: added stream to save in tukos
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
            if ($geoSource){
                $tukosActivity['geosource'] = $geoSource;
                $this->addGeoDataToItem($tukosActivity, $this->latitude, $this->longitude);
            }
            if (!empty($tukosActivity['timestreamc'])){
                foreach($tukosActivity as $col => $value){
                    if (substr($col, -7) === 'streamc'){
                        unset($tukosActivity[$col]);
                        $tukosActivity[substr($col, 0, -1)] = $value;
                    }
                }
                unset($tukosActivity['comments']);
            }
            $itemsValues[] = $tukosActivity;
        }
        return $itemsValues;
    }
    public function addGeoDataToItem(&$itemValue, $latitude, $longitude){
        if (($libGeo = $itemValue['geosource']) && ($libGeo !== 'auto' || !empty($latitude))){
            $geoUrl = 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?' .
                'select=date%2C%20coordonnees%2C%20libgeo%2C%20ff%2C%20dd&where=date%20%3E%3D%20date%27${date}T00%3A00%3A00%27%20and%20date%20%3C%3D%20date%27${date}T23%3A59%3A59%27%20and%20${libgeowhere}&order_by=date%20ASC';
            if ($libGeo === 'auto'){
                $targetDistance = 50; $windData = [];
                while (empty($windData) || $targetDistance <= 200){
                    $libGeoWhere = Utl::substitute('within_distance(coordonnees%2C%20geom\'POINT(${longitude}%20${latitude})\'%2C%20${targetdistance}km)',
                        ['latitude' => CUtl::radiansToDegrees($latitude), 'longitude' => CUtl::radiansToDegrees($longitude), 'targetdistance' => $targetDistance]);
                    $windData = json_decode(file_get_contents(Utl::substitute($geoUrl, ['date' => $itemValue['startdate'], 'libgeowhere' => $libGeoWhere])), true);
                    $targetDistance = $targetDistance * 2;
                }
                if (!empty($windData['results'])){
                    $data = Utl::toAssociative($windData['results'], 'libgeo');
                    if (count($data) === 1){
                        $data = $windData['results'];
                    }else{
                        $nearestDistance = INF;
                        foreach($data as $libGeo => $values){
                            $distance = CUtl::latlngRadiansToMeters($latitude, $longitude, CUtl::degreesToRadians($values['coordonnees']['lat']), CUtl::degreesToRadians($values['coordonnees']['lon']));
                            if ($distance < $nearestDistance){
                                $nearestLibGeo = $libGeo;
                                $nearestDistance = $distance;
                            }
                        }
                        $data = array_values(array_filter($windData['results'], function($item) use ($nearestLibGeo) {return $item['libgeo'] === $nearestLibGeo;}));
                    }
                }
                
            }else{
                $libGeoWhere = Utl::substitute('libgeo%20%3D%20%22${libgeo}%22', ['libgeo' => $libGeo]);
                $windData = json_decode(file_get_contents(Utl::substitute($geoUrl, ['date' => $itemValue['startdate'], 'libgeowhere' => $libGeoWhere])), true);
                $data = $windData['results'];
            }
            if (!empty($data)){
                $dataCount = count($data);
                $midTimeSeconds = DUtl::timeToSeconds(substr($itemValue['starttime'], 0, 8)) + intval($itemValue['duration'] / 2);
                $midTime = substr(DUtl::secondsToTime($midTimeSeconds), 1);
                $beforeData = $data[0];
                $i = 1; $iLast = $dataCount -1;
                while($i <= $iLast && substr($data[$i]['date'], 11, 8) < $midTime){
                    $beforeData = $data[$i];
                    $i +=1;
                }
                $afterData = $i <= $iLast ? $data[$i] : $beforeData;
                if ($beforeData !== $afterData){
                    $secondsBefore = Dutl::timeToSeconds(substr($beforeData['date'], 11, 8));
                    $secondsAfter = Dutl::timeToSeconds(substr($afterData['date'], 11, 8));
                    $totalSeconds = $secondsAfter - $secondsBefore;
                    $beforeWeight = ($secondsAfter - $midTimeSeconds) / $totalSeconds;
                    $afterWeight = ($midTimeSeconds - $secondsBefore) / $totalSeconds;
                    $itemValue['windvelocity'] = ($beforeData['ff'] * $beforeWeight + $afterData ['ff'] * $afterWeight) / 2.0;
                    $itemValue['winddirection'] = intval(round(($beforeData['dd'] * $beforeWeight + $afterData ['dd']* $afterWeight) * 16 / 360)) % 16;
                }else{
                    $itemValue['windvelocity'] = $beforeData['ff'];
                    $itemValue['winddirection'] = intval(round(($beforeData['dd']) * 16 / 360)) % 16;
                }
            }
        }else{
            $itemValue['windvelocity'] = null;
            $itemValue['winddirection'] = null;
        }
        
    }
}
?>

