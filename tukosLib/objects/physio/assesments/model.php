<?php
namespace TukosLib\Objects\Physio\Assesments;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    protected $assesmentTypeOptions = ['initial', 'intermediate', 'final'];

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'physiotherapist' => "INT(11) DEFAULT NULL",
            'assesmenttype' => "ENUM ('" . implode("','", $this->assesmentTypeOptions) . "') DEFAULT NULL",
            'assesmentdate' => 'date NULL DEFAULT NULL',
            'assesment' => 'longtext DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'physioassesments', ['parentid' => ['physioprescriptions'], 'physiotherapist' => ['people']], [], $colsDefinition, '', ['assesmenttype'], ['worksheet', 'custom']);
    }    
    function initialize($init=[]){
        return parent::initialize(array_merge(['assesmentdate' => date('Y-m-d')], $init));
    }
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $result = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
        if (!empty($result['parentid'])){
            $prescriptionsModel = Tfk::$registry->get('objectsStore')->objectModel('physioprescriptions');
            $prescription = $prescriptionsModel->getOneExtended(['where' => ['id' => $result['parentid']], 'cols' => ['parentid', 'prescriptor']]);
            $result['patient'] = $prescription['parentid'];
            $result['prescriptor'] = $prescription['prescriptor'];
        }
        return $result;
    }

    public function getDescriptionChanged($atts){
        if (!empty($atts['where']['parentid'])){
            $objectsStore = Tfk::$registry->get('objectsStore');
            $prescriptionsModel = $objectsStore->objectModel('physioprescriptions');
            $prescription = $prescriptionsModel->getOneExtended(['where' => ['id' => $atts['where']['parentid']], 'cols' => ['name', 'parentid', 'prescriptor']]);
            if (isset($prescription)){
                $patientId = $prescription['parentid'];
                if (!empty($patientId)){
                    $patientModel = $objectsStore->objectModel('physiopatients');
                    $patientName = implode(' ', $patientModel->getOne(['where' => ['id' => $prescription['parentid']], 'cols' => $patientModel->extendedNameCols]));
                }else{
                    $patientName = '';
                }
                return ['patient' => $patientId, 'prescriptor' => $prescription['prescriptor'], 'name' => $patientName . '-' . $prescription['name'] . '-' . $this->tr($atts['where']['assesmenttype'])];
            }
        }
        return ['patient' => '', 'prescriptor' => '', 'name' => ''];
    }
}
?>
