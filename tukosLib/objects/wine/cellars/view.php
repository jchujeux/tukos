<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Wine\Cellars; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){

        parent::__construct($objectName, $translator, 'Owner', 'Description');

        $customDataWidgets = [
        ];
        $subObjects['wineinputs']  = [
            'atts' => [
                'title'     => $this->tr('Pending Wine Input'),
                'summaryRow' => ['cols' => ['quantity' => ['content' => ['Total: ', ['filter' => ['status' => 'PENDING'], 'init' => 0, 'rhs' => "return Number(#quantity#);"],' items']]]],
            ],
            'filters'   => ['parentid' => '@id', ['col' => 'status', 'opr' => 'IN', 'values' => ['IGNORE', 'PENDING']]],
            'allDescendants' => false
        ];
        $subObjects['wineoutputs'] = [
            'atts' => [
                'title'     => $this->tr('Pending Wine Output'), 
                'summaryRow' => ['cols' => ['quantity' => ['content' => ['Total: ', ['filter' => ['status' => 'PENDING'], 'init' => 0, 'rhs' => "return Number(#quantity#);"],' items']]]],
            ],
            'filters'   => ['parentid' => '@id', ['col' => 'status', 'opr' => 'IN', 'values' => ['IGNORE', 'PENDING']]],
            'allDescendants' => false,
        ];
        $this->customize($customDataWidgets, $subObjects);

        $this->customContentAtts = [
            'edit' => ['actionLayout' => ['contents' => ['actions' => ['widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit', 'calendartab', 'export', 'process']]]]],
        ];
    }    
}
?>
