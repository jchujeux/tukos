<?php
namespace TukosLib\Objects\BackOffice\Views\Edit;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View {
    function __construct($controller){
        $this->controller = $controller;
    	$this->view = $controller->view;
    	$this->objectName = $controller->objectName;
    	$this->paneMode = $controller->paneMode;
    }
    public function formContent($query){
        if (empty($this->dataLayout = $this->view->getDataLayout($query))){
            $this->dataLayout = [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                'widgets' => $this->view->dataWidgetsNames($query),
            ];
        }
        $this->actionLayout = $this->view->getActionLayout($query);
        if ($this->actionLayout === false){
            $this->actionWidgets = $this->actionLayout = [];
        }else{
            if (empty($this->actionLayout)){
                $this->actionLayout = [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'contents' => [
                        'row1' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
                            'widgets' => ['logo', 'title']
                        ],
                        'row2' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
                            'contents' => [
                                'actions' => [
                                    'tableAtts' => ['cols' => 5, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Actions') . ':</b>'],
                                    'widgets' => ['send', 'reset'],
                                ],
                                'feedback' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false,  'label' => '<b>' . $this->view->tr('Feedback') . ':</b>'],
                                    'widgets' => [ 'clearFeedback',  'feedback'],
                                ],
                            ],
                        ]
                    ]
                ];
            }
        }
        if (!empty($this->actionLayout)){
            $tr = $this->view->tr;
            $isMobile = Tfk::$registry->isMobile;
            $this->actionWidgets = [
                'title' => ['type' => 'HtmlContent', 'atts' => ['value' => '<h1>' . $tr('Tukos Questionnaire') . '</h1>']],
                'logo' => ['type' => 'HtmlContent', 'atts' => ['value' => '<img alt="logo" src="' . Tfk::$publicDir . 'images/tukosswissknife.jpg" style="height: ' . ($isMobile ? '40' : '80') . 'px; width: ' . ($isMobile ? '100' : '200') . 'px;' . ($isMobile ? 'float: right;' : '') . '>']],
                'send' => ['type' => 'ObjectSave', 'atts' => ['serverAction' => 'Save', 'label' => $this->view->tr('Send'), 'sendToServer' => ['changedValues'],
                    'urlArgs' => ['action' => 'Save', 'query' => ['form' => 'BackOfficeForm', 'object' => 'backOfficeObject']],
                ]],
                'reset' => ['type' => 'ObjectReset', 'atts' => ['serverAction' => 'Reset', 'label' => $this->view->tr('Reset'),
                    'urlArgs' => ['query' => ['form' => 'BackOfficeForm', 'object' => 'backOfficeObject']]]],
                'clearFeedback'  => ['type' => 'ObjectFieldClear', 'atts' => ['label' => $this->view->tr('Clear Feedback'), 'fieldToClear' => 'feedback']],
                'feedback'  => Widgets::htmlContent(
                    ['title' => $this->view->tr('Feedback'), 'label' => '<b>' . $this->view->tr('Feedback') . ':</b>', 'disabled' => true, 'style' => ['minHeight' => '30px', 'maxHeight' => '50px', 'minWidth' => '30em', 'overflow' => 'auto', 'backgroundColor' => '#F0F0F0']]),
/*
                'feedback'  => Widgets::tukosTextArea(
                    ['title' => $this->view->tr('Feedback'), 'label' => '<b>' . $this->view->tr('Feedback') . ':</b>', 'cols' => 100, 'disabled' => true, 'style' => ['maxHeight' => '50px', 'overflow' => 'auto']]),
*/
            ];
            $this->actionWidgets = Utl::array_merge_recursive_replace($this->actionWidgets, $this->view->getActionWidgets($query));
        }
        $dataElts = $this->view->dataElts($query);
        $formContent =  [
            'object'         => $this->view->objectName,
            'contextPaths'  => [[0]],
            'viewMode'      => 'Edit',
        	'paneMode' => $this->paneMode,
            'noLoadingIcon' => true,
            'widgetsDescription'      => array_merge ($this->view->widgetsDescription($query, true), (isset($this->view->actionWidgets) ? $this->view->actionWidgets : $this->actionWidgets)),
            'postElts' =>  $dataElts,
            'dataElts' => $dataElts,
            'sendOnSave' => $this->view->sendOnSave($query),
            'sendOnReset' => $this->view->sendOnReset($query),
            'objectIdCols'  => [],
            'subObjects'    => [],
            'dataLayout'    => $this->dataLayout,
            'actionLayout'  => $this->actionLayout,
            'style' => ['padding' => '0px'],
            'widgetsHider' => false,
            'title' => $this->view->getTitle($query)
        ];
        if (!empty($onOpenAction = $this->view->onOpenAction($query))){
            $formContent['onOpenAction'] = $onOpenAction;
        }
        return $formContent;
    } 
}
?>
