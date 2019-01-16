<?php

namespace TukosLib\Objects\Admin\Mail\Boxes\Views\Edit;

use TukosLib\Objects\Views\Edit\SubObjects;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View {

    function __construct($actionController){
        $this->view = $actionController->view;
        if ($this->view->subObjects){
            SubObjects::addWidgets($this->view->subObjects, $this->view);
        }

        $this->actionWidgets = [
            'get'   => [
                'type' => 'ObjectProcess',
                'atts' => [
                    'label'   => $this->view->tr('Get'), 'allowSave' => true, 
                    'urlArgs' => ['action' => 'Save', 'query' => ['params' => json_encode(['new' => 'get', 'existing' => 'get'])],],
                ],
            ],
            'delete'   => ['type' => 'ObjectDelete',       'atts' => ['label' => $this->view->tr('Delete')]],                                                               
            'edit'     => Widgets::ObjectEdit([
                'storeArgs' => ['object' => $this->view->objectName],    'placeHolder' => $this->view->tr('Select item to edit'), 'title' => $this->view->tr('Select item to edit'),
                'dropdownFilters' => ['contextpathid' => '$tabContextId', 'accountid' => '@parentid',],

            ]),
            'new'      => ['type' => 'ObjectNew'  ,        'atts' => ['label' => $this->view->tr('New')]],
            'reset'    => ['type' => 'ObjectReset',        'atts' => ['label' => $this->view->tr('Reset')]],
            'create'   => [
                'type' => 'ObjectProcess',
                'atts' => [
                    'label'   => $this->view->tr('Create'), 'allowSave' => true, 
                    'urlArgs' => ['action' => 'Save', 'query' => ['params' =>  json_encode(['new' => 'create', 'existing' => 'create'])],],
                ],
            ],
            'clearFeedback'  => ['type' => 'ObjectFieldClear',   'atts' => ['label' => $this->view->tr('Clear Feedback'), 'fieldToClear' => 'feedback']],
            'feedback'  => Widgets::textArea(['title' => $this->view->tr('Feedback'), 'label' => '<b>' . $this->view->tr('Feedback') . ':</b>', 'cols' => 100, 'disabled' => true]),
        ];


        $this->dataLayout   = [
            'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true/*, 'style' => 'height: 100%; width: 100%'*/, 'labelWidth' => 60],
                'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                'widgets' => $this->dataElts(),
        ];

        $this->actionLayout = [
            'tableAttts'=> ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
            'Contents'=> [
                'actions' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Actions') . ':<b>'],
                    'widgets' => ['get', 'reset', 'delete', 'new', 'edit', 'create']
                ],
                'feedback' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Feedback') . ':<b>'],
                    'widgets' => ['clearFeedback', 'feedback']
                ]
            ],
        ];
    }

    public function dataElts(){
        return array_keys($this->view->dataWidgets);
    }

    function editPostCols(){
        return $this->dataElts();
    }                            

    public function formContent($atts = []){
        $defAtts = [
            'object'     => $this->view->objectName,
            'contextPaths'  => $this->view->user->customContextAncestorsPaths($this->view->objectName),
            'viewMode'  => 'Edit',
            'postElts'  => $this->editPostCols(),
            'widgetsDescription'  => array_merge ($this->view->widgetsDescription($this->dataElts(), true), (isset($this->view->actionWidgets) ? $this->view->actionWidgets : $this->actionWidgets)),
         'objectIdCols' => array_intersect($this->view->model->idCols, $this->dataElts()),
            'subObjects'=> array_keys($this->view->subObjects),
            'dataLayout'=> $this->dataLayout,
          'actionLayout'=> $this->actionLayout,
        ];
        $formContent =  Utl::array_merge_recursive_replace($defAtts, $atts);
        return Utl::array_merge_recursive_replace($formContent, $this->view->user->getCustomView($this->view->objectName, 'edit', $this->paneMode));
    } 
}
?>
