<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    
    public static function translationSets(){
        return ['sports'];
    }
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'records' => 'longtext',
            //'patient' => 'MEDIUMINT DEFAULT NULL'
        ];
        parent::__construct($objectName, $translator, 'physiogametracks', ['parentid' => ['physiogameplans'], 'patient' => ['physiopatients']], ['records'], $colsDefinition, [], [], ['custom'], ['name']);
        $this->recordtypeOptions = ['1' => 'running', '2' => 'otheractivity', '3' => 'noteorcomment'];
    }
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $item = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
        if (!empty($item['parentid'])){
            $gamePlanModel = Tfk::$registry->get('objectsStore')->objectModel('physiogameplans');
            $gamePlan = $gamePlanModel->getOneExtended(['where' => ['id' => $item['parentid']], 'cols' => ['parentid', 'woundstartdate', 'treatmentstartdate', 'dateupdated', 'diagnostic', 'pathologyof', 'training', 'pain', 'exercises', 'biomechanics', 'comments', 'indicatorscache']]);
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
        if (!empty($item['records'])){
            foreach($item['records'] as &$record){
                $indicatorsCache = Utl::getItem('indicatorscache', $record);
                $record = array_merge($record, json_decode($indicatorsCache, true));
            }
        }
        return $item;
    }
    /*public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (!$jsonFilter && !empty($newValues['records'])){
            $parentId = !empty($newValues['parentid']) ? $newValues['parentid'] : $this->getOne(['where' =>['id' => $newValues['id']], 'cols' => ['parentid']])['parentid'];
        }
        if (!empty($parentId)){
            $gamePlanModel = Tfk::$registry->get('objectsStore')->objectModel('physiogameplans');
            foreach($newValues['records'] as &$record){
                if (!empty($indicatorsCacheCols = array_diff(array_keys($record), $gamePlanModel->allCols))){
                    Utl::extractItems($indicatorsCacheCols, $record);
                }
            }
        }
        return parent::updateOneExtended($newValues, $atts, $insertIfNoOld, $jsonFilter, $init);
    }
    public function insertExtended($values, $init=false, $jsonFilter = false){
        if (!$jsonFilter && !empty($values['records']) && !empty($values['parentid'])){
            $gamePlanModel = Tfk::$registry->get('objectsStore')->objectModel('physiogameplans');
            foreach($values['records'] as &$record){
                if (!empty($indicatorsCacheCols = array_diff(array_keys($record), $gamePlanModel->allCols))){
                    Utl::extractItems($indicatorsCacheCols, $record);
                }
            }
        }
    }*/
}
?>
