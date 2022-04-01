<?php
namespace TukosLib\Objects\Sports\Athletes;

use TukosLib\Objects\Physio\Patients\View as PatientsView;
use TukosLib\Objects\ObjectTranslator;

class View extends PatientsView {
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, (new ObjectTranslator('physiopatients'))->tr);
    }
}
?>
