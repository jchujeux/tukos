<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Strava\API\Client;
use TukosLib\Strava\API\Service\REST;
use TukosLib\TukosFramework as Tfk;

class TestStravaAPI {

    function __construct($parameters){ 
        try{
            $adapter = new \GuzzleHttp\Client(['base_uri' => 'https://www.strava.com/api/v3/']);
            $service = new REST('360be8e138d77f122a652a897d2834123a222ceb', $adapter);  // Define your user token here.
            $client = new Client($service);
            
            $athlete = $client->getAthlete();
            print_r($athlete);
            
            $activities = $client->getAthleteActivities();
            print_r($activities);
            
            //$activityStreams = $client->getActivityStreams(6206069445, 'time,distance,altitude,heartrate,cadence,watts,grade_smooth');
            echo 'done';
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>