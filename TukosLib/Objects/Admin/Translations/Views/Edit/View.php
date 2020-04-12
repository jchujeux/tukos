<?php

namespace TukosLib\Objects\Admin\Translations\Views\Edit;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;

class View {


    function __construct($controller){
        $this->controller = $controller;
    	$this->view = $controller->view;
    	$this->paneMode = $controller->paneMode;
        $this->actionWidgets = [
                     'save' => ['type' => 'ObjectSave',         'atts' => ['label' => $this->view->tr('Save')]],                                                               
                  'delete'  => ['type' => 'ObjectDelete',       'atts' => ['label' => $this->view->tr('Delete')]],                                                               
                    'edit'  => Widgets::ObjectEdit(['storeArgs' => ['object' => $this->view->objectName],    'placeHolder' => $this->view->tr('Select item to edit'),
                                                    'title' => $this->view->tr('Select item to edit'), 'dropdownFilters' => ['contextpathid' => '$tabContextId'],
                               ]),
                     'new'  => ['type' => 'ObjectNew'  ,        'atts' => ['label' => $this->view->tr('New')]],
               'duplicate'  => ['type' => 'ObjectDuplicate',    'atts' => ['label' => $this->view->tr('Duplicate')]],
                   'reset'  => ['type' => 'ObjectReset',        'atts' => ['label' => $this->view->tr('Reset')]],
        		'export'  => ['type' => 'ObjectExport', 'atts' => ['label' => $this->view->tr('export')]],
                 'process'  => ['type' => 'ObjectProcess',      'atts' => ['label' => $this->view->tr('Process')]],
           'clearFeedback'  => ['type' => 'ObjectFieldClear',   'atts' => ['label' => $this->view->tr('Clear Feedback'), 'fieldToClear' => 'feedback']],
                'feedback'  => Widgets::textArea(['title' => $this->view->tr('Feedback'), 'label' => '<b>' . $this->view->tr('Feedback') . ':</b>', 'cols' => 100, 'disabled' => true, 'style' => ['maxHeight' => '3em', 'overflow' => 'auto']]),
        ];

        $this->dataLayout = [
            'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
            'widgets' => ['id', 'name', 'setname', 'en_us', 'fr_fr', 'es_es']
        ];
            
        $this->actionLayout = [
            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
            'contents' => [
                'actions' => [
                    'tableAtts' => ['cols' => 10, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Actions') . ':<b>'],
                    'widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit', 'export'],
                ],
                'feedback' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false,  'label' => '<b>' . $this->view->tr('Feedback') . ':<b>'],
                    'widgets' => [ 'clearFeedback',  'feedback'],
                ],
            ],
        ];
    }

    public function editPostCols(){// cols which value that can be sent back to the server for processing / storage (via save)
        return array_values(array_diff(array_keys($this->view->dataWidgets), $this->view->_exceptionCols['post']));
    }
    public function formContent($atts = []){
        $dataElts = $this->view->dataElts();
        $defAtts = [
            'object'         => $this->view->objectName,
            'contextPaths'  => $this->view->user->customContextAncestorsPaths($this->view->objectName),
            'viewMode'      => 'Edit',
            'customviewid'  => $this->view->user->customViewId($this->view->objectName, 'edit', $this->paneMode, 'user'),
            'tukosviewid'  => $this->view->user->customViewId($this->view->objectName, 'edit', $this->paneMode, 'tukos'),
            'postElts'      => $this->editPostCols(),
        	'sendOnSave'	=> $this->view->sendOnSave(),
        	'sendOnDelete'	=> $this->view->sendOnDelete(),
        	'doNotEmpty'    => $this->view->doNotEmpty(),
        	'dataElts'      => $dataElts,
            'widgetsDescription'      => array_merge ($this->view->widgetsDescription($dataElts, true), (isset($this->view->actionWidgets) ? $this->view->actionWidgets : $this->actionWidgets)),
            'objectIdCols'  => [],
            'subObjects'    => [],
            'dataLayout'    => $this->dataLayout,
            'actionLayout'  => $this->actionLayout,
            'style' => ['padding' => '0px']
        ];
        if (isset($this->onOpenAction)){
            $defAtts['onOpenAction'] = $this->onOpenAction;
        }
        return  Utl::array_merge_recursive_replace($defAtts, $atts);
    } 
}
?>
