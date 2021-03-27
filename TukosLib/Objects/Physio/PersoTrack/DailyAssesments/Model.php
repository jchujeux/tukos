<?php
namespace TukosLib\Objects\Physio\PersoTrack\DailyAssesments;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'startdate'  => 'VARCHAR(30)  DEFAULT NULL',
            'painduring' => 'TINYINT DEFAULT NULL',
            'painafter' => 'TINYINT DEFAULT NULL',
            'painnextday' => 'TINYINT DEFAULT NULL',
            'mood' => 'TINYINT DEFAULT NULL',
            'fatigue' => 'TINYINT DEFAULT NULL',
            'otherexceptional' => 'VARCHAR(512) DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'physiopersodailies',  ['parentid' => ['physiopersotreatments']/*, 'patient' => ['physiopatients']*/], [], $colsDefinition, [], [], ['custom'], ['physiopersotreatments.patient',  'startdate']);
    }   
    public function getOne ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null, $absentColsFlag = 'forbid'){
        if (array_search('physiopersotreatments.patient', $atts['cols'])!== false){
            $atts['join'][] = ['inner', 'physiopersotreatments', 'tukos.parentid = physiopersotreatments.id'];
        }
        return parent::getOne($atts, $jsonColsPaths, $jsonNotFoundValue, $absentColsFlag);
    }
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $item = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
        if (!empty($item['parentid'])){
            $treatmentModel = Tfk::$registry->get('objectsStore')->objectModel('physiopersotreatments');
            $planId = $treatmentModel->getOne(['where' => ['id' => $item['parentid']], 'cols' => ['parentid']])['parentid'];
            if (!empty($planId)){
                $planModel = Tfk::$registry->get('objectsStore')->objectModel('physiopersoplans');
                $plan = $planModel->getOneExtended(['where' => ['id' => $planId], 'cols' => ['exercises']], ['exercises' => []]);
            }
            $item = array_merge($item, $plan);
        }
        return $item;
    }
    public function getAll ($atts, $jsonColsPaths = [], $jsonNotFoundValues = null, $processLargeCols = false){
        if (array_search('physiopersotreatments.patient', $atts['cols']) !== false){
            $atts['join'][] = ['inner', 'physiopersotreatments', 'tukos.parentid = physiopersotreatments.id'];
        }
        return parent::getAll($atts, $jsonColsPaths, $jsonNotFoundValues, $processLargeCols);
    }
}
?>

