<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Objects\Sports\TrainingFormulaes as TF;
use TukosLib\Objects\Sports\Strava as ST;
use Strava\API\Exception;
use TukosLib\Utils\Utilities as Utl;

class TestTukosStravaAPI {

    function __construct($parameters){ 
        try{
            $this->initializeDuration();
            $client = ST::getAthleteClient(41377);
            $clientD = $this->hrDuration();
            $athlete = $client->getAthlete();
            $athleteD = $this->hrDuration();
            //print_r($athlete);
            
            $activities = $client->getAthleteActivities();
            $activitiesD = $this->hrDuration();
            //print_r($activities);
            
            if ($activityStreams = $client->getActivityStreams(6822733888, 'time,distance,altitude,heartrate,cadence,watts,grade_smooth')){
                $streamsD = $this->hrDuration();
                $hrTrainingLoad = TF::hrTrainingLoad($activityStreams['heartrate']['data'], 80, 152, 'male');
                $hrTrainingLoadD = $this->hrDuration();
                $hrTrainingLoadR =  10 * TF::hrTrainingLoad(Utl::reduce($activityStreams['heartrate']['data'], 10), 80, 152, 'male');
                $hrTrainingLoadRD = $this->hrDuration();
                $pwTrainingLoad = TF::pwTrainingLoad($activityStreams['watts']['data'], 220, 'male', 30);
                $pwTrainingLoadD = $this->hrDuration();
                $mechLoad = TF::runningMechanicalLoad($activityStreams['distance']['data'], $activityStreams['cadence']['data'], 12 / 0.36);
                $mechLoadD = $this->hrDuration();
                $hrTimeInZones = TF::timeInZones($activityStreams['heartrate']['data'], [103, 126, 142, 159], 3);
                $hrTimeInZonesD = $this->hrDuration();
                echo "HR Training Load: $hrTrainingLoad - HR Training LoadR: $hrTrainingLoadR - Power training load: $pwTrainingLoad - Mech load: $mechLoad\n";
                var_dump($hrTimeInZones);
                echo "\nclient: $clientD - athlete: $athleteD - activities: $activitiesD - streams: $streamsD - hrTrainingLoad: $hrTrainingLoadD - hrTrainingLoadRD: $hrTrainingLoadRD - pwTrainingLoad: $pwTrainingLoadD - mechload: $mechLoadD - hrTimeInZones: $hrTimeInZonesD";
                    
            }else{
                    echo "No stream found";
            }
        }catch(Exception $e){
            //Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
            print $e->getMessage();
        }
    }
    function initializeDuration(){
        $this->previous = hrtime(true);
    }
    function hrDuration(){
        $previous = $this->previous; 
        return (($this->previous = hrtime(true)) - $previous) / 1000000000;
    }
}
?>