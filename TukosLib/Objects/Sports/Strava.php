<?php
namespace TukosLib\Objects\Sports;

use TukosLib\Strava\API\Client;
use TukosLib\Strava\API\Service\REST;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use Strava\API\OAuth;
use TukosLib\Objects\Sports\TrainingFormulaes as TF;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Strava {

    private static $metrics = null; private static $metricsCols = []; private static $adapter = null;
    
    public static function init(){
        self::$metrics = [
            'duration' => ['stName' => 'elapsed_time', 'format' => ['type' => 'divide', 'args' => [60]]],
            'distance' => ['stName' => 'distance', 'format' => ['type' => 'divide', 'args' => [1000]]],
            'elevationgain' => ['stName' => 'total_elevation_gain'],
            'avghr' => ['stName' => 'average_heartrate', 'format' => ['type' => 'round', 'args' => [0]]],
            'avgpw' => ['stName' => 'average_watts', 'format' => ['type' => 'round', 'args' => [0]]],
            'timemoving' => ['stName' => 'moving_time', 'format' => ['type' => 'divide', 'args' => [60]]],
            'avgcadence' => ['stName' => 'average_cadence', 'format' => ['type' => 'round', 'args' => [0]]],
            'startdate' => ['stName' => 'start_date_local'],
            'sport' => ['stName' => 'type', 'format' => ['type' => 'map', 'args' => ['Ride' => 'bicycle', 'VirtualRide' => 'bicycle', 'Run' => 'running', 'Swim' => 'swimming', 'Crossfit' => 'bodybuilding']]],
            'name' => ['stName' => 'name'],
            'stravaid' => ['stName' => 'id']
            //'notes' => [],
        ];
        self::$metricsCols = array_keys(self::$metrics);
    }
    public static function metricsOptions($tr, $cols = []){
        foreach(empty($cols) ? self::$metrics : array_intersect_key(self::$metrics, array_flip($cols)) as $name => $description){
            $metricsOptions[$name] = ['option' => $tr($name, 'none'), 'tooltip' => $tr($name . 'Tooltip', 'none')];
        }
        return $metricsOptions;
    }
    public static function allMetrics(){
        return self::$metricsCols;
    }
    public static function stName($col){
        return self::$metrics[$col]['stName'];
    }
    public static function activityToSession($activity){
        $sessionActivity = [];
        foreach(self::$metricsCols as $col){
            if ($value = Utl::getItem(self::$metrics[$col]['stName'], $activity)){
                $sessionActivity[$col] = ($format = Utl::getItem('format', self::$metrics[$col])) ? self::format($value, $format) : $value;
            }
        }
        list($sessionActivity['startdate'], $sessionActivity['starttime']) = explode('T', $sessionActivity['startdate']);
        return $sessionActivity;
    }
    public static function format($value, $format){
        switch($format['type']){
            case 'round': 
                return round($value, $format['args'][0]);
            case 'divide':
                return $value / $format['args'][0];
            case 'map':
                return Utl::getItem($value, $format['args'], 'other');
        }
    }
    public static function getAthleteClient($athleteId){
        if (is_null(self::$adapter)){
            self::$adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
        }
        $athletesModel = Tfk::$registry->get('objectsStore')->objectModel('sptathletes');
        $options = json_decode(Utl::getItem('stravainfo', $athletesModel->getOne(['where' => ['id' => $athleteId], 'cols' => ['stravainfo']])), true);
        if (is_array($options)){
            $token = new AccessToken($options);
            if ($token->hasExpired()){
                $oauth = new OAuth(array_merge(Tfk::$registry->get('tukosModel')->getOption('strava'), ['redirectUri' => '']));
                $token = $oauth->getAccessToken('refresh_token', ['refresh_token' => $token->getRefreshToken()]);
                $athletesModel->updateItems(['stravainfo' => json_encode(['access_token' => $token->getToken(), 'refresh_token' => $token->getRefreshToken(), 'expires' => $token->getExpires()])], ['table' => 'people', 'where' => ['id' => $athleteId]]);
            }
            return new Client(new REST($token, self::$adapter));
        }else{
            return false;
        }
    }
    public static function stravaStreamsToTukosStreams($stravaStreams){
        $tukosStreams = [];
        foreach ($stravaStreams as $name => $stream){
            $tukosStreams[$name. 'stream'] = $stream['data'];
        }
        return $tukosStreams;
    }
    public static function stravaStreamToElapsedValue($times, $stravaStreamData, $delta = 1){// transforms in [elapsedTime, value], eliminating consecutive pairs with same value
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
    public static function addStreamsAndMetrics($session, $athleteParams, $needsStreams, $streamCols, $client){
        list('hrmin' => $hrMin, 'hrthreshold' => $hrThreshold, 'h4timethreshold' => $h4timeThreshold, 'h5timethreshold' => $h5timeThreshold, 'ftp' => $ftp, 'speedthreshold' => $speedThreshold, 'sex' => $sex) = $athleteParams;
        if ($needsStreams){
            $session = array_merge($session, self::stravaStreamsToTukosStreams($client->getActivityStreams($session['stravaid'], implode(',', array_map(function($tukosName){return substr($tukosName, 0, -6);}, $streamCols)))));
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
}
Strava::init();
?>