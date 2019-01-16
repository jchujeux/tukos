<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Wine\Outputs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Cellar', 'Description');

        $customDataWidgets = [
            'stockid'   => ViewUtils::objectSelectMulti('stockid', $this, 'Stock item', ['atts' => [
                    'edit' => ['dropdownFilters' => ['cellarid' => '@parentid', ['col' => 'quantity', 'opr' => '>', 'values' => 0]]],
                    'storeedit' => ['editorArgs' => ['dropdownFilters' => ['cellarid' => '@id']]],
                ],
            ]),
            'exitdate'  => ['type' => 'tukosDateBox',  'atts' => ['edit' =>  ['title' => $this->tr('Exit date'), 'style' => ['width' => '6em']]]],    
            'status'    => ViewUtils::storeSelect('status', $this, 'Status'),
            'quantity'  => ['type' => 'numberTextBox',       'atts' => ['edit' =>  ['title' => $this->tr('Quantity')]]],

            ];
        $this->customize($customDataWidgets);
    }    
    function overviewDescription($custom = ['actions' => ['donothing', 'delete', 'duplicate', 'modify', 'process']]){
        return parent::overviewDescription($custom);
    }
}
?>
