<?php
namespace TukosLib\Objects\Sports;

use TukosLib\Strava\API\Client;
use TukosLib\Strava\API\Service\REST;
use League\OAuth2\Client\Token\AccessToken as AccessToken;
use Strava\API\OAuth;
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
            'startdate' => ['stName' => 'start_date'],
            'sport' => ['stName' => 'type', 'format' => ['type' => 'map', 'args' => ['Ride' => 'bicycle', 'VirtualRide' => 'bicycle', 'Run' => 'running', 'Swim' => 'swimming']]],
            'name' => ['stName' => 'name'],
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
                return $format['args'][$value];
        }
    }
    public static function getAthleteClient($athleteId){
        if (is_null(self::$adapter)){
            self::$adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
        }
        $athletesModel = Tfk::$registry->get('objectsStore')->objectModel('sptathletes');
        $options = json_decode($athletesModel->getOne(['where' => ['id' => $athleteId], 'cols' => ['stravainfo']])['stravainfo'], true);
        $token = new AccessToken($options);
        if ($token->hasExpired()){
            $oauth = new OAuth(array_merge(Tfk::$registry->get('tukosModel')->getOption('strava'), ['redirectUri' => '']));
            $token = $oauth->getAccessToken('refresh_token', ['refresh_token' => $token->getRefreshToken()]);
            $athletesModel->updateItems(['stravainfo' => json_encode(['access_token' => $token->getToken(), 'refresh_token' => $token->getRefreshToken(), 'expires' => $token->getExpires()])], ['table' => 'people', 'where' => ['id' => $athleteId]]);
        }
        return new Client(new REST($token, self::$adapter));
    }
}
Strava::init();
?>