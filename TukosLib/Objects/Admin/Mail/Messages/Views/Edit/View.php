<?php

namespace TukosLib\Objects\Admin\Mail\Messages\Views\Edit;

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
            'save'   => [
                'type' => 'ObjectSave',
                'atts' => [
                    'label'   => $this->view->tr('SaveDraft'), 'allowSave' => true, 
                    'urlArgs' => ['action' => 'Save', 'query' => ['params' => json_encode(['saveOneNew' => 'savedraft', 'saveOneExisting' => 'savedraft'])],],
                ],
            ],
                  'delete'  => ['type' => 'ObjectDelete',       'atts' => ['label' => $this->view->tr('Delete')]],                                                               
                    'edit'  => Widgets::ObjectEdit(['storeArgs' => ['object' => $this->view->objectName],    'placeHolder' => $this->view->tr('Select item to edit'),
                                                    'title' => $this->view->tr('Select item to edit'),
                               ]),
                     'new'  => ['type' => 'ObjectNew'  ,        'atts' => ['label' => $this->view->tr('New')]],
               'duplicate'  => ['type' => 'ObjectDuplicate',    'atts' => ['label' => $this->view->tr('Duplicate')]],
                   'reset'  => ['type' => 'ObjectReset',        'atts' => ['label' => $this->view->tr('Reset')]],
            'send'   => [
                'type' => 'ObjectProcess',
                'atts' => [
                    'label'   => $this->view->tr('Send'), 'allowSave' => true, 'hidden' => true,
                    'urlArgs' => ['action' => 'Save', 'query' => ['params' => json_encode(['saveOneNew' => 'send', 'saveOneExisting' => 'send'])],],
                ],
            ],
           'clearFeedback'  => ['type' => 'ObjectFieldClear',   'atts' => ['label' => $this->view->tr('Clear Feedback'), 'fieldToClear' => 'feedback']],
                'feedback'  => Widgets::textArea(['title' => $this->view->tr('Feedback'), 'label' => '<b>' . $this->view->tr('Feedback') . ':</b>', 'cols' => 100, 'disabled' => true]),
        ];


        $this->dataLayout   = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false,  'content' => ''/*, 'style' => 'height: 100%; width: 100%'*/, 'labelWidth' => 60],
              'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['id', 'parentid', 'mailboxname', 'name', 'from', 'to', 'date', /*'message_id', */'size', 'uid', 'msgno', 'recent', 'flagged', 'answered', 'deleted', 'seen', 'draft', 'udate'] 
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['30%', '70%']],
                    'widgets' => ['body', 'mailmessages']
                ],
            ],
        ];

        $this->actionLayout = [
            'tableAtts'=> ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
            'contents'=> [
                'actions' => [
                    'tableAtts' => ['cols' => 7, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Actions') . ':<b>'],
                    'widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit', 'send']
                ],
                 'feedback' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false,  'label' => '<b>' . $this->view->tr('Feedback') . ':<b>'],
                    'widgets' => ['clearFeedback', 'feedback']
                ]
            ],
        ];
    }

    public function dataElts(){
        return array_keys($this->view->dataWidgets);
    }

    function editPostCols(){
        return ['id', 'parentid', 'mailboxname', 'name', 'from', 'to', 'body', 'mailmessages'];
    }                            

    public function formContent($atts = []){
        $defAtts = [
            'object'     => $this->view->objectName,
            'contextPaths'  => $this->view->user->customContextAncestorsPaths($this->view->objectName),
            'viewMode'  => 'Edit',
            'postElts'  => $this->editPostCols(),
            'dataElts'  => $this->dataElts(),
            'widgetsDescription'  => array_merge ($this->view->widgetsDescription($this->dataElts(), true), (isset($this->view->actionWidgets) ? $this->view->actionWidgets : $this->actionWidgets)),
         'objectIdCols' => array_intersect($this->view->model->idCols, $this->dataElts()),
            'subObjects'=> array_keys($this->view->subObjects),
            'dataLayout'=> $this->dataLayout,
          'actionLayout'=> $this->actionLayout,
        ];
        $formContent =  Utl::array_merge_recursive_replace($defAtts, $atts);
        return Utl::array_merge_recursive_replace($formContent, $this->user->getCustomView($this->objectName, 'edit', $this->paneMode));
    } 
}
?>
