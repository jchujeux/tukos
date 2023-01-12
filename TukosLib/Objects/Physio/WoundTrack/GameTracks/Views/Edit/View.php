<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\Edit\EditConfig;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Objects\Physio\WoundTrack\IndicatorsConfig;
use TukosLib\Strava\Views\Edit\SynchronizationAction as StravaSyncAction;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

	use LocalActions, IndicatorsConfig, EditConfig, StravaSyncAction;
	
	function __construct($actionController){
       parent::__construct($actionController);
        $tr = $this->view->tr;
        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['30%', '70%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'id' => 'gamePlanPane', 'hidden' => Tfk::$registry->isMobile],
                        'contents' => [
                            'row1' => [
                                'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => '50'],
                                'widgets' => ['id', 'parentid', 'name', 'patientid', 'physiotherapist']
                            ],
                            'row2' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['border' => '1px solid grey']],
                                'contents' => [
                                    'row1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => $tr('GamePlaninformation'), 'style' => ['border' => '1px solid grey']],
                                        'contents' => [
                                            'row1' => [
                                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => $tr('GamePlaninformation'), 'widgetWidths' => ['60%', '40%'], 'widgetCellStyle' => ['verticalAlign' => 'top'], 'labelCellStyle' => ['border' => '1px solid grey']],
                                                'contents' => [
                                                    'col1' => [
                                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal']],
                                                        'widgets' => ['diagnostic']
                                                    ],
                                                    'col2' => [
                                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => '150'],
                                                        'widgets' => ['dateupdated', 'pathologyof', 'woundstartdate', 'treatmentstartdate']
                                                    ]
                                                ]
                                            ],
                                            'row2' => [
                                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal']],
                                                'widgets' => ['training', 'pain', 'exercises', 'biomechanics', 'notes']
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'col2' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert'],
                        'contents' => [
                            'rowcharts' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'id' => 'roadTrackAnalysis', 'widgetCellStyle' => ['verticalAlign' => 'top']],
                                'widgets' => [],
                            ],
                            'row4' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['records'],
                            ],
                        ],
                    ]
                ],
            ]
        ];
        $this->dataLayout['contents'] = Tfk::$registry->isMobile ? $customContents : array_merge($customContents, Utl::getItems(['rowbottom', 'rowhistory', 'rowacl'], $this->dataLayout['contents']));
        $this->actionWidgets['showhidegameplan'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('ShowHideAnalysis'), 'onClickAction' => "this.form.localActions.showHideAnalysis();"]];
        $this->actionWidgets['showhideanalysis'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('ShowHideGamePlan'), 'onClickAction' => "this.form.localActions.showHideGamePlan();"]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'showhidegameplan';
        $this->actionLayout['contents']['actions']['widgets'][] = 'showhideanalysis';
        $this->setEditConfigActionWidget();
        $this->setStravaActionButton(grid: 'records', athlete: 'patientid', coach: 'physiotherapist', synchrostart: null, synchroend: null, daySortCol: 'rowId', dayDateCol: 'recorddate', 
            sportsMapping: ['col' => 'recordtype', 'map' => ['running' => 1, 'bicycle' => 2]]);
        
        $this->onOpenAction =  $this->openAction();
	}
	public function openAction(){
	    return <<<EOT
const form = this;
form.markAllChanges = true;
require (["tukos/objects/physio/woundTrack/gametracks/LocalActions"], function(LocalActions){
    form.localActions = new LocalActions({form: form});
});
EOT
	    ;
	}
}
?>
