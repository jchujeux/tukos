<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Admin\Scripts\Outputs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Owner', 'Description');

        $customDataWidgets = [
             'output'    => ['type' => 'textArea'    ,  'atts' => ['edit' =>  ['title' => 'Output:', 'colspan' => '3', 'style' => ['maxHeight' => '200px'], 'disabled' => true, ],
                                                                  ]
                            ],
             'errors'    => ['type' => 'textArea'    ,  'atts' => ['edit' =>  ['title' => 'Errors:', 'colspan' => '3', 'style' => ['maxHeight' => '200px'], 'disabled' => true ]]],
             //'created'   => ['type' => 'textBox'     ,  'atts' => ['edit' =>  ['title' => $this->tr('Created on'), 'style' => 'width:8em', 'disabled' => true]]],
            ];
        $this->customize($customDataWidgets);
    }
}
?>
