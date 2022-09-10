<?php
namespace TukosLib\Objects\Physio\WoundTrack\GamePlans;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'physiotherapist' => 'MEDIUMINT DEFAULT NULL',
            'organization' => 'MEDIUMINT DEFAULT NULL',
            'dateupdated' => 'VARCHAR(30)  DEFAULT NULL',
            'diagnostic' => 'longtext', 
            'pathologyof' => 'TINYINT DEFAULT NULL',
            'woundstartdate' => 'VARCHAR(30)  DEFAULT NULL',
            'treatmentstartdate' => 'VARCHAR(30)  DEFAULT NULL',
            'training' => 'longtext',
            'pain' => 'longtext',
            'exercises' => 'longtext', 
            'biomechanics' => 'longtext',
            'indicatorscache' => 'longtext',
        ];
        parent::__construct($objectName, $translator, 'physiogameplans', ['parentid' => ['physiopatients'], 'physiotherapist' => ['people']], ['indicatorscache'], $colsDefinition, [], [], ['custom', 'history'], ['name', 'parentid']);
    }
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $item = parent::getOneExtended($atts, ['indicatorscache' => []], $jsonNotFoundValue);
        if (!empty($indicatorsCache = Utl::extractItem('indicatorscache', $item))){
            $item = array_merge($item, $indicatorsCache);
        }
        return $item;
    }
    public function getAllExtended ($atts){
        $atts['cols'][] = 'indicatorscache';
        $results = parent::getAllExtended($atts);
        foreach ($results as &$plan){
            if (!empty($indicatorsCache = Utl::extractItem('indicatorscache', $plan))){
                $plan = array_merge($plan, json_decode($indicatorsCache, true));
            }
        }
        return $results;
    }
    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        $this->processLargeCols($newValues);
        if (!$jsonFilter && (!empty($indicatorsCacheCols = array_diff(array_keys($newValues), $this->allCols)))){
            $newValues['indicatorscache'] = Utl::extractItems($indicatorsCacheCols, $newValues);
        }
        return $this->updateOne($newValues, $atts, $insertIfNoOld, true, $init);
    }
    public function insertExtended($values, $init=false, $jsonFilter = false){
        if (!$jsonFilter && (!empty($indicatorsCacheCols = array_diff(array_keys($values), $this->allCols)))){
            $values['indicatorscache'] = Utl::extractItems($indicatorsCacheCols, $values);
        }
        return parent::insertExtended($values, $init, $jsonFilter);
    }
}
?>
