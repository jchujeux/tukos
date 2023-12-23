<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\Sports\Strava\AuthorizeAndSynchronize;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    
    use AuthorizeAndSynchronize;
    
    public static function translationSets(){
        return ['sports'];
    }
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'records' => 'longtext',
            //'patient' => 'MEDIUMINT DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'physiogametracks', ['parentid' => ['physiogameplans']], ['records'], $colsDefinition, [], [], ['custom'], ['name']);
        $this->recordtypeOptions = ['1' => 'running', '2' => 'bicycle', '3' => 'otheractivity', '4' => 'noteorcomment'];
    }
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $item = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
        if (!empty($item['parentid'])){
            $gamePlanModel = Tfk::$registry->get('objectsStore')->objectModel('physiogameplans');
            $gamePlan = $gamePlanModel->getOneExtended(['where' => ['id' => $item['parentid']], 'cols' => ['parentid', 'physiotherapist', 'woundstartdate', 'treatmentstartdate', 'dateupdated', 'diagnostic', 'pathologyof', 'training', 'pain', 'exercises', 'biomechanics', 'comments', 'indicatorscache']]);
            $gamePlan['patientid'] = Utl::extractItem('parentid', $gamePlan);
            $gamePlan['planindicatorscache'] = Utl::extractItem('indicatorscache', $gamePlan);
            $i = 1;
            while (isset($gamePlan['indicator' . $i])){
                $gamePlan['planindicator' . $i] = Utl::extractItem('indicator' . $i, $gamePlan);
                $i += 1;
            }
            $gamePlan['notes'] = Utl::extractItem('comments', $gamePlan);
            $item = array_merge($item, $gamePlan);
        }
        $mostRecentStravaDate = '';
        if (!empty($item['records'])){
            foreach($item['records'] as &$record){
                if ($indicatorsCache = Utl::getItem('indicatorscache', $record)){
                    $record = array_merge($record, json_decode($indicatorsCache, true));
                }
                if (!empty($record['stravainfo'])){
                    $mostRecentStravaDate = max($mostRecentStravaDate, $record['recorddate']);
                }
            }
        }
        //$item['stravaActivities'] = $this->stravaSynchronize(['athleteid' => $item['patientid'], 'synchrostreams' => true, 'synchrostart' => empty($mostRecentPerformed) ? $item['treatmentstartdate'] : DUtl::dayAfter($mostRecentStravaDate), 'synchroend' => date('Y-m-d')])['stravaActivities'];
        return $item;
    }
    public function stravaCols(){
        return ['stravaid' => null, 'startdate' => null, 'starttime' => null, 'sport' => null, 'duration' => ['objToStoreEdit' => ['minutesToTime' => ['class' => 'TukosLib\Utils\DateTimeUtilities']]], 'distance' => null, 'elevationgain' => null];
    }
    public function getKpis($query, $kpisToGet){// associated to process action
        $stravaActivitiesModel = Tfk::$registry->get('objectsStore')->objectModel('stravaactivities');
        $activitiesKpis = $stravaActivitiesModel->computeKpis($query['athlete'], $kpisToGet, [], 'stravaid');
        return ['data' => ['kpis' => $activitiesKpis]];
    }
}
?>
