<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameRecords;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'recordtype' => 'TINYINT DEFAULT NULL',
            'recorddate' => 'VARCHAR(30)  DEFAULT NULL',
            'globalsensation' => 'VARCHAR(512) DEFAULT NULL',
            'environment' => 'VARCHAR(512) DEFAULT NULL',
            'recovery' => 'VARCHAR(512) DEFAULT NULL',
            'previoussensation' => 'VARCHAR(512) DEFAULT NULL',
            'duration'   => 'VARCHAR(30)  DEFAULT NULL',
            'distance' => 'VARCHAR(10) DEFAULT NULL',
            'elevationgain' => 'VARCHAR(10) DEFAULT NULL',
            'elevationloss' => 'VARCHAR(10) DEFAULT NULL',
            'perceivedintensity' => 'TINYINT DEFAULT NULL',
            'intensitydetails' => 'longtext',
            'perceivedstress' => 'TINYINT DEFAULT NULL',
            'stressdetails' => 'longtext',
            'sessionrpe' => 'TINYINT DEFAULT NULL',
            'sessionrpedetails' => 'longtext',
            'mentaldifficulty' => 'TINYINT DEFAULT NULL',
            'mentaldifficultydetails' => 'longtext',
            'indicatorscache' => 'longtext'
        ];
        parent::__construct($objectName, $translator, 'physiogamesessions', ['parentid' => ['physiogameplans']], ['indicatorscache'], $colsDefinition, [], [], ['custom'], ['name', 'parentid']);
        $this->recordtypeOptions = ['1' => 'running', '2' => 'otheractivity', '3' => 'noteorcomment'];
    }
    public function getOne ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null, $absentColsFlag = 'forbid'){
        $item = parent::getOne($atts, $jsonColsPaths, $jsonNotFoundValue, $absentColsFlag);
        if (!empty($indicatorsCache = Utl::extractItem('indicatorscache', $item))){
            $item = array_merge($item, json_decode($indicatorsCache, true));
        }
        return $item;
    }
    public function getAll ($atts, $jsonColsPaths = [], $jsonNotFoundValues = null, $processLargeCols = false){
        $atts['cols'][] = 'indicatorscache';
        $results = parent::getAll($atts, $jsonColsPaths, $jsonNotFoundValues, $processLargeCols);
        foreach ($results as &$session){
            if (!empty($indicatorsCache = Utl::extractItem('indicatorscache', $session))){
                $session = array_merge($session, json_decode($indicatorsCache, true));
            }
        }
        return $results;
    }
    public function updateOne($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (!$jsonFilter && (!empty($indicatorsCacheCols = array_diff(array_keys($newValues), $this->allCols)))){
            $newValues['indicatorscache'] = Utl::extractItems($indicatorsCacheCols, $newValues);
        }
        return parent::updateOne($newValues, $atts, $insertIfNoOld, true, $init);
    }
}
?>
