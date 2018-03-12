<?php

namespace TukosLib\Objects\Views\Overview;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View {

    function __construct($actionController){
        $this->view  = $actionController->view;
        $this->model = $actionController->model;

        $this->view->dataWidgets['overview'] = [
            'type' => 'overviewDgrid', 
            'atts' => ['edit' =>[
                'label'           => $this->view->tr('overview'),
                'maxHeight' => '1000px', 
                'colsDescription' => $this->view->widgetsDescription($this->view->gridCols(), false), 
                'objectIdCols'    => array_values(array_intersect($this->view->gridCols(), $this->model->idCols)),
                'sort'            => [['property' => 'updated', 'descending' => true]],
                'storeArgs'       => ['view' => 'overview', 'action' => 'gridselect'],
                'object'           => $this->view->objectName,
				'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false],
            ]],
        ];
        $this->view->dataWidgets['totalrecords'] = [
            'type' => 'textBox',
            'atts' => ['edit' => ['title' => $this->view->tr('totalentries'), 'style' => ['width' => '5em'], 'disabled' => true]]
        ];
        $this->view->dataWidgets['filteredrecords'] = [
            'type' => 'textBox',
            'atts' => ['edit' => ['title' => $this->view->tr('totalfilteredentries'), 'style' => ['width' => '5em'], 'disabled' => true]]
        ];

            
        $this->actionWidgets   = [ 
               'reset' => ['type' => 'OverviewAction',       'atts' => ['label' => $this->view->tr('Reset'), 'grid' => 'overview', 'serverAction' => 'reset']],
           'duplicate' => ['type' => 'OverviewAction',   'atts' => ['label' => $this->view->tr('Duplicate'), 'grid' => 'overview', 'serverAction' => 'duplicate']],
              'modify' => ['type' => 'OverviewAction',      'atts' => ['label' => $this->view->tr('Modify'), 'grid' => 'overview', 'serverAction' => 'modify']],
              'delete' => ['type' => 'OverviewAction',      'atts' => ['label' => $this->view->tr('Delete'), 'grid' => 'overview', 'serverAction' => 'delete']],
               'edit'  => Widgets::OverviewEdit([
               		'storeArgs' => ['object' => $this->view->objectName],    'placeHolder' => $this->view->tr('Select item to edit'), 'title' => $this->view->tr('Select item to edit'),
               		'urlArgs' => ['action' => 'tab', 'view' => 'edit'], 'dropdownFilters' => ['contextpathid' => '$tabContextId'],
                          ]),
        	'import'  => ['type' => 'SimpleUploader',     'atts' => ['label' => $this->view->tr('Import'), 'multiple' => false, 'uploadOnSelect' => true, 'grid' => 'overview', 'serverAction' => 'process', 'queryParams' => ['process' => 'importItems']]],
        	'export'  => ['type' => 'OverviewAction',     'atts' => ['label' => $this->view->tr('Export'), 'grid' => 'overview', 'serverAction' => 'process', 'queryParams' => ['process' => 'exportItems']]],
        	 'process' => ['type' => 'OverviewAction',     'atts' => ['label' => $this->view->tr('Process'), 'grid' => 'overview', 'serverAction' => 'process']],
       'clearFeedback' => ['type' => 'ObjectFieldClear','atts' => ['label' => $this->view->tr('Clear Feedback'), 'fieldToClear' => 'feedback']],
           'feedback'  => Widgets::textArea(['title' => $this->view->tr('Feedback'), 'label' => '<b>' . $this->view->tr('Feedback') . ':</b>', 'cols' => 130, 'disabled' => true, 'style' => ['maxHeight' => '5em', 'overflow' => 'auto']]),
        ];
        
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'spacing' => '0', 'showLabels' => false],
            'widgets' => ['overview'],
        ];
        
        $this->actionLayout = [
            'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
            'contents' => [
                'actions' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('For all items') . '<b>', 'spacing' => '0'],
                    'widgets' => ['reset'],
                ],
                'selection' => [
                    'tableAtts' => ['cols' => 8, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('For selected items') . '<b>', 'spacing' => '0'],
                    'widgets' => ['duplicate', 'modify', 'delete', 'edit', 'import', 'export']
                ],
                'feedback' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Feedback') . ':<b>', 'spacing' => '0'],
                    'widgets' => ['clearFeedback', 'feedback'],
                ],
            ],
        ];
        $this->summaryLayout = [
            'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'label' => '<b>' . $this->view->tr('Summary') . ':</b>', 'labelWidth' => '20%', 'spacing' => '0'],
            'widgets' => ['filteredrecords', 'totalrecords'],
        ];

        $this->dataElements = ['overview', 'totalrecords', 'filteredrecords'];
    }

    function formContent($atts = []){
        $defAtts =  [
            'object'         => $this->view->objectName,
            'contextPaths'  => $this->view->user->customContextAncestorsPaths($this->view->objectName),
            'viewMode'      => 'overview',
            'customviewid'  => $this->view->user->customViewId($this->view->objectName, 'overview'),
            'widgetsDescription' => array_merge ($this->view->widgetsDescription($this->dataElements, true), $this->actionWidgets),                         
            'dataLayout'  => $this->dataLayout,
            'actionLayout'  => $this->actionLayout,
         'summaryLayout' => $this->summaryLayout,
            'style' => ['padding' => '0px']
        ];
        $formContent =  Utl::array_merge_recursive_replace($defAtts, $atts);
        return Utl::array_merge_recursive_replace($formContent, $this->view->user->getCustomView($this->view->objectName, 'overview'));
    } 
}
?>
