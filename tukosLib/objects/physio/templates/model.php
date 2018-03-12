<?php
namespace TukosLib\Objects\Physio\Templates;

use TukosLib\Objects\AbstractModel;

class Model extends AbstractModel {

	protected $templateTypeOptions = ['cdcssynthesis'];
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'templatetype'  => 'VARCHAR(30)  DEFAULT NULL',
        ];
        parent::__construct(
            $objectName, $translator, 'physiotemplates',
            ['parentid' => ['people', 'organizations', 'physioassesments', 'physiocdcs', 'physiotemplates']], 
            [], $colsDefinition, '', ['templatetype']
        );
    }   
}
?>
