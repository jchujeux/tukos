<?php
namespace TukosLib\Objects\Views\Edit;

use TukosLib\Objects\Views\Edit\SubObjects;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;

class View {

    protected $_firstDataElts = ['id', 'parentid', 'name'];
    protected $_lastDataElts  = ['permission', 'grade', 'contextid', 'updated', 'updator', 'created', 'creator'];

    function __construct($controller){
        $this->controller = $controller;
    	$this->view = $controller->view;
    	$this->objectName = $controller->objectName;
    	$this->user = $controller->user;
    	$this->paneMode = $controller->paneMode;
    	$tr = $this->view->tr;
        if (isset($this->view->dataWidgets['configstatus'])){
            $this->_lastDataElts[] = 'configstatus';
        }
        if ($this->view->subObjects){
            SubObjects::addWidgets($this->view->subObjects, $this->view);
        }
        $this->actionWidgets = [
            'save' => ['type' => 'ObjectSave', 'atts' => ['serverAction' => 'Save', 'label' => $this->view->tr('Save'), 'sendToServer' => ['changedValues', 'itemCustomization']]],                                                               
            'delete'  => ['type' => 'ObjectDelete', 'atts' => ['label' => $this->view->tr('Delete')]],                                                               
            'edit'  => Widgets::ObjectEdit(['storeArgs' => ['object' => $this->view->objectName],    'placeHolder' => $this->view->tr('Select item to edit'),
                 'title' => $this->view->tr('Select item to edit'), 'dropdownFilters' => ['contextpathid' => '$tabContextId']]),
            'new'  => ['type' => 'ObjectNew', 'atts' => ['serverAction' => 'Edit', 'label' => $this->view->tr('New'), 'isNew' => true, 'confirmAtts' => ['title' => $tr('fieldsHaveBeenModified'), 'content' => $tr('sureWantToForget')]]],
            'duplicate'  => ['type' => 'ObjectDuplicate', 'atts' => ['label' => $this->view->tr('Duplicate')]],
            'reset'  => ['type' => 'ObjectReset', 'atts' => ['serverAction' => 'Reset', 'label' => $this->view->tr('Reset'), 'confirmAtts' => ['title' => $tr('fieldsHaveBeenModified'), 'content' => $tr('sureWantToForget')]]],
            'calendartab'  => ['type' => 'ObjectCalendar', 'atts' => ['label' => $this->view->tr('Calendar')]],
        	'export'  => ['type' => 'ObjectExport', 'atts' => ['label' => $this->view->tr('export')]],
            'process'  => ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Process')]],
            'clearFeedback'  => ['type' => 'ObjectFieldClear', 'atts' => ['label' => $this->view->tr('Clear Feedback'), 'fieldToClear' => 'feedback']],
            'feedback'  => Widgets::tukosTextArea(
                    ['title' => $this->view->tr('Feedback'), 'label' => '<b>' . $this->view->tr('Feedback') . ':</b>', 'cols' => 100, 'disabled' => true, 'style' => ['maxHeight' => '50px', 'overflow' => 'auto']]),
        ];

        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 75],
                    'widgets' => array_values(array_diff(array_merge($this->_firstDataElts, $this->view->addedDataWidgetsElts), $this->view->_exceptionCols['edit'])),
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['30%', '70%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                    'widgets' => (isset($this->view->dataWidgets['worksheet']) ? ['worksheet', 'comments'] : ['comments']),
                ],
                'row3' => [
                    'tableAtts' =>  ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                    'widgets' => array_values(array_diff(array_keys($this->view->subObjects), $this->view->_exceptionCols['edit'])),
                ],
                'row4' => [
                    'tableAtts' => ['cols' => 8, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 60],
                    'widgets' => array_values(array_diff($this->_lastDataElts, $this->view->_exceptionCols['edit'])),
                ],
                'row5' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0',],
                    'widgets' =>  (in_array('history', $this->view->model->allCols) ? ['history'] : []),
                ],
            ],
        ];
            
        $this->actionLayout = [
            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
            'contents' => [
                'actions' => [
                    'tableAtts' => ['cols' => 11, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Actions') . ':</b>'],
                    'widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit', 'calendartab', 'export'],
                ],
                'feedback' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false,  'label' => '<b>' . $this->view->tr('Feedback') . ':</b>'],
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
        		'paneMode' => $this->paneMode,
            'customviewid'  => $this->user->customViewId($this->objectName, 'edit', $this->paneMode),
            'postElts'      => $this->editPostCols(),
        	'sendOnSave'	=> $this->view->sendOnSave(),
        	'sendOnDelete'	=> $this->view->sendOnDelete(),
        	'doNotEmpty'    => $this->view->doNotEmpty(),
        	'dataElts'      => $dataElts,
            'widgetsDescription'      => array_merge ($this->view->widgetsDescription($dataElts, true), (isset($this->view->actionWidgets) ? $this->view->actionWidgets : $this->actionWidgets)),
            'objectIdCols'  => array_intersect($this->view->model->idCols, $dataElts),
            'subObjects'    => array_keys($this->view->subObjects),
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
