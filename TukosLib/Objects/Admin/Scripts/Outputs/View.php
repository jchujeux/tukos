<?php
namespace TukosLib\Objects\Admin\Scripts\Outputs;

use TukosLib\Objects\AbstractView;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Owner', 'Description');

        $customDataWidgets = [
             'output'    => ['type' => 'textArea',  'atts' => ['edit' =>  ['title' => 'Output:', 'colspan' => '3', 'style' => ['maxHeight' => '200px'], 'disabled' => true]]],
             'errors'    => ['type' => 'textArea',  'atts' => ['edit' =>  ['title' => 'Errors:', 'colspan' => '3', 'style' => ['maxHeight' => '200px'], 'disabled' => true]]],
            ];
        $this->customize($customDataWidgets);
    }
}
?>
