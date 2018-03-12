<?php
/**
 *
 * class for viewing methods and properties for the $tasks model object
 */
namespace TukosLib\Objects\Collab\Tasks;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'responsible'=>ViewUtils::objectSelectMulti('responsible', $this, 'Responsible'),
            'accountable'=>ViewUtils::objectSelectMulti('accountable', $this, 'Accountable'),
            'consulted' => ViewUtils::objectSelectMulti('consulted'  , $this, 'Consulted'),
            'informed'  => ViewUtils::objectSelectMulti('informed'   , $this, 'Informed'),
            'start'     => ['type' => 'tukosDateBox',  'atts' => ['edit' =>  ['title' => $this->tr('Start date'), 'style' => ['width' => '6em']]],], 
            'end'       => ['type' => 'tukosDateBox',  'atts' => ['edit' =>  ['title' => $this->tr('End date')  , 'style' => ['width' => '6em']]]], 
            'mendays'   => [
                'type' => 'numberTextBox',
                'atts' => ['edit' =>  ['title' => $this->tr('Men days') , 'style' => ['width' => '4em']]],
                'objToEdit' => ['floatval' => []],
                'objToStoreEdit' => ['floatval' => []],
            ], 
            'completed' => ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Completed') , 'style' => ['width' => '4em']]],
                             'objToEdit' => ['floatval' => []],
                             'objToStoreEdit' => ['floatval' => []],
                            ],
            /* 'gantt' => ['type' => 'ganttColumn'  , 'atts' => []],*/
        ];
        $subObjects['tasks'] = [
            'atts' => ['title' => $this->tr('Sub-tasks')],
            'view'      => $this,
            'filters'   => ['parentid' => '@id'],
            'allDescendants' => true
        ];
        $subObjects['notes'] = [
            'atts' => ['title' => $this->tr('Notes'), 'storeType' => 'LazyMemoryTreeObjects'/*, 'storeArgs' => ['view' => 'edit', 'action' => 'getItems?table=notes']*/],
            'filters'   => ['parentid' => '@id'],
            'allDescendants' => true
        ];
        $this->customize($customDataWidgets, $subObjects/*, ['edit' => ['gantt'], 'get' => ['gantt'], 'post' => ['gantt']]*/);
    }
}
?>
