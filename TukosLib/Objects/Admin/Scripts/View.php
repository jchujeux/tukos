<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Admin\Scripts;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){

        parent::__construct($objectName, $translator, 'Owner', 'Description');

        $customDataWidgets = [
            'path'       => ViewUtils::textBox($this, 'Path', ['atts' => ['edit' =>  ['style' => ['width' => '15em']]]]),
            'scriptname' => ViewUtils::textBox($this, 'Script name', ['atts' => ['edit' =>  ['style' => ['width' => '14em']]]]),
            'parameters' => ['type' => 'textArea'    ,  'atts' => ['edit' =>  ['title' => $this->tr('parameters'), 'colspan' => 2]]],
            'runmode'    => ViewUtils::storeSelect('runMode', $this, 'Run Mode'),
            'status'     => ViewUtils::storeSelect('status', $this, 'Status'),
            'startdate'  => ViewUtils::dateTimeBoxDataWidget($this, 'startdate'),
            'enddate'    => ViewUtils::dateTimeBoxDataWidget($this, 'enddate'),
            'timeinterval'=>ViewUtils::numberUnitBox('timeInterval', $this, 'Time interval'),
            'laststart'  => ViewUtils::timeStampDataWidget($this, 'Last start', ['atts' => ['edit' => ['disabled' => true]]]),
            'lastend'    => ViewUtils::timeStampDataWidget($this, 'Last end', ['atts' => ['edit' => ['disabled' => true]]]),
        ];
        $subObjects['scriptsoutputs']    = ['atts' => ['title'     => $this->tr('Scripts outputs'), 'sort' => [['property' => 'updated', 'descending' => true]]],
                                            'filters'   => ['parentid' => '@id'],
                                       'allDescendants' => false];
        $this->customize($customDataWidgets, $subObjects);
        $this->customContentAtts = [//        $this->customContentAtts = ['edit' => ['widgetsDescription' => ['export' => ['atts' => ['conditionDescription' => "return this.valueOf('id');"]]]]];
            
            'edit'      => ['actionLayout' => ['contents' => ['actions' => ['widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit', 'calendartab', 'export', 'process']]]],
                            'widgetsDescription' => ['process' => ['atts' => ['clientTimeout' => 64000]]]],
            'overview'  => ['actionLayout' => ['contents' => ['selection' => ['widgets' => ['duplicate', 'modify', 'delete', 'edit', 'import', 'export', 'process']]]]],
        ];
    }    

    function overviewProcess($idsToProcess){
        return $this->model->process($idsToProcess);
    }

}
?>
