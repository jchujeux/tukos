<?php
namespace TukosLib\Objects\Physio\PersoTrack\Sessions;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'startdate'  => 'VARCHAR(30)  DEFAULT NULL',
            'sessionid' => 'TINYINT DEFAULT NULL',
            'exerciseid' => 'TINYINT DEFAULT NULL',
            'duration'   => 'VARCHAR(30)  DEFAULT NULL',
            'stress' => 'TINYINT DEFAULT NULL',
            'series' => 'TINYINT DEFAULT NULL',
            'repeats'=> 'VARCHAR(30)  DEFAULT NULL',
            'extra' => 'VARCHAR(30)  DEFAULT NULL',
            'extra1' => 'VARCHAR(30)  DEFAULT NULL',
            'painduring' => 'TINYINT DEFAULT NULL',
            'painafter' => 'TINYINT DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'physiopersosessions',  ['parentid' => ['physiopersotreatments']], [], $colsDefinition, [], [], ['custom'], ['name', 'startdate']);
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
            $item = array_merge($plan, $item);
        }
        return $item;
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['duration' => '60'], $init));
    }
}
?>

