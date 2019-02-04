<?php
namespace TukosLib\Objects\Physio\Prescriptions;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {

    protected $quantitativeOptions = ['yes', 'no'];
    protected $physioBeforeOptions = ['yes', 'no', 'unknown'];

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'prescriptor'   => "INT(11) DEFAULT NULL",
            'prescription' => 'longtext DEFAULT NULL',
            'prescriptiondate' => 'date NULL DEFAULT NULL',
            'quantitative' => "ENUM ('" . implode("','", $this->quantitativeOptions) . "') DEFAULT NULL",
            'nbofsessions'   => "INT(11) DEFAULT NULL",
            'physiobefore' => "ENUM ('" . implode("','", $this->physioBeforeOptions) . "') DEFAULT NULL",
            'medicalindic' => 'longtext DEFAULT NULL',
            'otherexams' => 'longtext DEFAULT NULL',

        ];
        parent::__construct($objectName, $translator, 'physioprescriptions', ['parentid' => ['physiopatients'], 'prescriptor' => ['people']], [], $colsDefinition, [], ['quantitative', 'physiobefore'], ['worksheet', 'custom'], ['name', 'parentid', 'prescriptiondate']);
    }    
    public function getPrescriptorChanged($atts){
        $peopleModel = Tfk::$registry->get('objectsStore')->objectModel('people');
        return $peopleModel->getOneExtended(['where' => ['id' => $atts['where']['prescriptor']], 'cols' => ['name', 'firstname', 'email', 'teloffice']]);
    }
}
?>
