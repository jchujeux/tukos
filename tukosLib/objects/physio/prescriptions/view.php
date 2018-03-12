<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Physio\Prescriptions;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Patient', 'Title');

        //$this->dataWidgets['name']['atts']['edit']['style']['width'] = '40em';

        $customDataWidgets = [
            'prescriptor' => ViewUtils::objectSelect($this, 'Prescriptor', 'people'),
            'prescription' => ViewUtils::editor($this, 'Prescription', ['atts' => ['edit' => ['height' => '100px']]]),
            'prescriptiondate' => ViewUtils::tukosDateBox($this, 'Prescriptiondate'),
            'quantitative' => ViewUtils::storeSelect('quantitative', $this, 'Quantitative'),
            'nbofsessions' => ViewUtils::textBox($this, 'Nbofsessions'),
            'physiobefore' => ViewUtils::storeSelect('physioBefore', $this, 'Physiobefore'),
            'medicalindic' => ViewUtils::editor($this, 'Medicalindic', ['atts' => ['edit' => ['height' => '100px']]]),
            'otherexams' => ViewUtils::editor($this, 'Otherexams', ['atts' => ['edit' => ['height' => '100px']]]),
        ];
        $subObjects['physioassesments'] = [
            'atts'  => ['title' => $this->tr('Physioassesments'),],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => true,
        ];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
