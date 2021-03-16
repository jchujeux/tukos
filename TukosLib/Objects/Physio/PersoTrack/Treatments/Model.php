<?php
namespace TukosLib\Objects\Physio\PersoTrack\Treatments;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'patient' => 'MEDIUMINT DEFAULT NULL',
            'fromdate' => 'VARCHAR(30)  DEFAULT NULL',
            'duration'  => 'VARCHAR(30)  DEFAULT NULL',
            'todate'     => 'VARCHAR(30)  DEFAULT NULL',
            'weeklies' => 'longtext',
        ];
        parent::__construct($objectName, $translator, 'physiopersotreatments', ['parentid' => ['physiopersoplans'], 'patient' => ['physiopatients']], ['weeklies'], $colsDefinition, [], [], ['custom'], ['name', 'patient']);
    }
    
    function initialize($init=[]){
        return parent::initialize(array_merge(
            ['fromdate' => date('Y-m-d', $nextMondayStamp = strtotime('last monday')), 'duration' =>'[5,"week"]', 'todate' => date('Y-m-d', strtotime('next sunday +4 weeks', $nextMondayStamp))], $init));
    }
    
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $item = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
        $item['calendar'] = '';
        if (!empty($item['parentid'])){
            $treatmentPlanModel = Tfk::$registry->get('objectsStore')->objectModel('physiopersoplans');
            $treatmentPlan = $treatmentPlanModel->getOneExtended(['where' => ['id' => $item['parentid']], 'cols' => ['objective', 'exercises', 'protocol', 'torespect']], ['exercises' => []]);
            $item = array_merge($item, $treatmentPlan);
        }
        return $item;
    }
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
        if(!empty($values['fromdate']) && !empty($values['todate']) && empty($values['duration'])){
            $values['duration'] = DUtl::duration(strtotime($values['todate']) - strtotime($values['fromdate']), ['week']);
        }
        return parent::insert($values, $init, $jsonFilter, $reference);
    }
}
?>
