<?php
namespace TukosLib\Objects\Physio\PersoTrack\Treatments\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Sports\Programs\SessionsTracking;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;

class View extends EditView{
    
    use LocalActions, SessionsTracking;
    
    function __construct($actionController){
        parent::__construct($actionController);
        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => '130'],
                'widgets' => ['id', 'parentid', 'name', 'patient', 'fromdate', 'duration', 'todate', 'displayeddate']
            ],
            'row2' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['70%', '30%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'contents' => [
                            'row1' => [
                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['35%', '65%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                        'widgets' => ['objective', 'torespect', 'protocol', 'comments'],
                                    ],
                                    'col2' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                        'widgets' => ['calendar', 'qsmchart', 'physiopersodailies'],
                                    ],
                                ],
                            ],
                            'row2' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['weeklies']
                            ]
                        ]],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                        'widgets' => ['exercises'],
                    ],
                ]
            ],
            'row3' => [
                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                'widgets' => ['physiopersosessions'],
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
        $this->onOpenAction = $this->view->gridOpenAction($this->view->gridWidgetName) . $this->view->gridOpenAction('weeklies') . $this->view->openEditAction();
        $this->setSessionsTrackingActionWidget(false, 'physiopersosessions', ['duration', 'distance', 'elevationgain', 'gcavghr', 'gcmechload']);
    }
}
?>

