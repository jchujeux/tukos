<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Objects\Sports\Strava as ST;
use Strava\API\Exception;

class TestTukosStravaAPI {

    function __construct($parameters){ 
        try{
            $client = ST::getAthleteClient(41377);
            
            $athlete = $client->getAthlete();
            print_r($athlete);
            
            $activities = $client->getAthleteActivities();
            print_r($activities);
            
            $activityStreams = $client->getActivityStreams(6206069445, 'time,distance,altitude,heartrate,cadence,watts,grade_smooth');
            echo 'done';
        }catch(Exception $e){
            //Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
            print $e->getMessage();
        }
    }
}
?>