<?php
namespace TukosLib\Objects\Physio\PersoTrack\DailyAssesments;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    public static function translationSets(){
        return ['sports'];
    }
    function __construct($objectName, $translator=null){
        $colsDefinition = [
                'startdate'  => 'VARCHAR(30)  DEFAULT NULL',
                'painduring' => 'TINYINT DEFAULT NULL',
                'painafter' => 'TINYINT DEFAULT NULL',
                'painnextday' => 'TINYINT DEFAULT NULL',
                //'mood' => 'TINYINT DEFAULT NULL',
                //'fatigue' => 'TINYINT DEFAULT NULL',
                'otherexceptional' => 'VARCHAR(512) DEFAULT NULL',
                'duration'   => 'VARCHAR(30)  DEFAULT NULL',
                'intensity'  =>  'TINYINT DEFAULT NULL',
                'distance' => 'VARCHAR(10) DEFAULT NULL',
                'elevationgain' => 'VARCHAR(10) DEFAULT NULL',
                'mechload' => 'MEDIUMINT DEFAULT NULL'
        ];
        parent::__construct(
            $objectName, $translator, 'physiopersodailies',  ['parentid' => ['physiopersotreatments']], [], $colsDefinition, [], [], ['custom'], ['parentid',  'startdate']);
        $this->additionalColsForBulkDelete = ['startdate'];
        $this->processDeleteForBulk = 'processDeleteForBulk';
        $this->datesToProcess = [];
        $this->_postProcess = '_postProcess';
    }   
    public function processDeleteForBulk($values){
        if ($startDate = Utl::getItem('startdate', $values)){
            $this->datesToProcess[] = $startDate;
        }
    }
    public function _postProcess(){
        if (!empty($this->datesToProcess)){
            Tfk::$registry->get('objectsStore')->objectModel("physiopersosessions")->delete([[['col' => 'startdate', 'opr' => 'IN', 'values' => array_unique($this->datesToProcess)]]]);
            $this->datesToProcess = [];
        }
    }
}
?>

