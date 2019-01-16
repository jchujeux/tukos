<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Contexts;

use TukosLib\Objects\AbstractView;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent Context', 'Context Name');

        $customDataWidgets = [
            'parentid' => [
                'type' => 'objectSelectDropDown', 
                'atts' => [
                    'edit' => [
                        'title' => $this->tr('Parent Context'), 'style' => ['width' => '12em'], 'object' => 'contexts',
                        'dropDownWidget' => ['type' => 'storeTree', 'atts' => $this->user->contextTreeAtts($this->tr)],
                    ],
                    'storeedit'=>['editorArgs' => ['style' => ['width' => '8em']]],
                ], 
            ],
        ];

        $subObjects['contexts'] = ['atts' => ['title' => $this->tr('sub-contexts'),], 'filters' => ['parentid' => '@id'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
