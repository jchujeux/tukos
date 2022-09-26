<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Objects\Physio\WoundTrack\IndicatorsConfig;
use TukosLib\Objects\Physio\WoundTrack\GameTracks\EditConfig;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

	use LocalActions, IndicatorsConfig, EditConfig;
	
	function __construct($actionController){
       parent::__construct($actionController);
        $tr = $this->view->tr;
        $customContents = [
            'row1' => [
                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'widgetWidths' => ['30%', '70%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                'contents' => [
                    'col1' => [
                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                        'contents' => [
                            'row1' => [
                                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => '50'],
                                'widgets' => ['id', 'parentid', 'name']
                            ],
                            'row2' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['border' => '1px solid grey'], 'id' => 'gamePlanPane', 'hidden' => Tfk::$registry->isMobile],
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
                            'rowtrendcharts' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'id' => 'roadTrackAnalysis'],
                                'widgets' => [],
                            ],
                            'row4' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                'widgets' => ['records'],
                            ],
                            'row1' => [
                                'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'style' => ['border' => '1px solid grey'], 'widgetCellStyle' => ['verticalAlign' => 'top'], 'hidden' => true /*Tfk::$registry->isMobile*/],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                        'contents' =>  [
                                            'col' => [
                                                'tableAtts' => ['cols' => 3, 'customClass' => 'actionTable', 'label' => $tr('entrymode'), 'showLabels' => false],
                                                'widgets' => ['newrecord', 'existingrecord', 'cancelmode']
                                            ]
                                        ]
                                    ],
                                    'col2' => [
                                        'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'style' => ['border' => '1px solid grey'], 'orientation' => 'vert', 'id' => 'recordFiltersInfoPane', 'labelWidth' => 50],
                                        'widgets' => ['rowId', 'recordtype', 'recorddate']
                                    ],
                                    'col3' => [
                                        'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert'],
                                        'widgets' => ['actualize', 'visualize']
                                    ],
                                ]
	                        ],
                            'row2' => [
                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['50%', '50%'], 'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px'], 'style' => ['padding' => '0px'], 'id' => 'activityPane'],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['padding' => '0px'], 'labelCellStyle' => ['border' => '1px solid grey', 'padding' => '0px']],
                                        'contents' => [
                                            'row1' => [
                                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $tr('Mechanicalload'), 'style' => ['border' => '1px solid grey', 'padding' => '0px'], 'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px']],
                                                'contents' => [
                                                    'row1' => [
                                                        'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px'], 'id' => 'runningPane', 'labelWidth' => '80'],
                                                        'widgets' => ['duration', 'distance', 'elevationgain'/*, 'elevationloss'*/, 'perceivedload']
                                                    ],
                                                    'row2' => [
                                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic'], 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px'], 'id' => 'intensityPane'],
                                                        'widgets' => ['perceivedintensity', 'intensitydetails']
                                                    ],
                                                    'row3' => [
                                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic'], 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px']],
                                                        'widgets' => ['activitydetails', 'perceivedstress', 'stressdetails']
                                                    ]
                                                ]
                                            ],
                                            /*'row2' => [
                                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'label' => $tr('physioconstraints'), 'style' => ['border' => '1px solid grey']],
                                                'contents' => [
                                                    'row3' => [
                                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal'], 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px']],
                                                        'widgets' => ['globaldifficulty', 'globaldifficultydetails']
                                                    ],
                                                ],
                                            ],*/
                                            'row3' => [
                                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'label' => $tr('mentalconstraints'), 'style' => ['border' => '1px solid grey', 'padding' => '0px']],
                                                'contents' => [
                                                    'row4' => [
                                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic'], 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['padding' => '0px']],
                                                        'widgets' => ['mentaldifficulty', 'mentaldifficultydetails']
                                                    ]
                                                ],
                                            ]
                                        ]
                                    ],
                                    'col2' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['padding' => '0px'], 'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px'], 'labelCellStyle' => ['border' => '1px solid grey']],
                                        'contents' => [
                                            'row1' => [
                                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'label' => $tr('biopsysocialconstraints'), 'style' => ['border' => '1px solid grey', 'padding' => '0px'], 'labelCellStyle' => ['fontWeight' => 'normal', 'fontStyle' => 'italic']],
                                                'widgets' => ['globalsensation', 'globalsensationdetails', 'environment', 'environmentdetails', 'recovery', 'recoverydetails']
                                            ]
                                        ]
                                    ],
                                ]
                            ],
                            'row3' => [
                                'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'widgetCellStyle' => ['verticalAlign' => 'top', 'padding' => '0px'], 'style' => ['border' => '1px solid grey', 'padding' => '0px'], 'id' => 'noteIndicatorsPane'],
                                'contents' => [
                                    'col1' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                        'widgets' => ['notecomments']
                                    ],
                                    'indicators' => [
                                        'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['tableLayout' => 'fixed']],
                                        'widgets' => [],
                                    ],
                                ]
                                
                            ],
                        ],
                    ]
                ],
            ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowhistory', 'rowacl'], $this->dataLayout['contents']));
        $this->actionWidgets['showhidegameplan'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('ShowHideAnalysis'), 'onClickAction' => "this.form.localActions.showHideAnalysis();"]];
        $this->actionWidgets['showhideanalysis'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('ShowHideGamePlan'), 'onClickAction' => "this.form.localActions.showHideGamePlan();"]];
        $this->actionLayout['contents']['actions']['widgets'][] = 'showhidegameplan';
        $this->actionLayout['contents']['actions']['widgets'][] = 'showhideanalysis';
        $this->actionWidgets['newrecord'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('newrecord'), 'onClickAction' => "this.form.localActions.newRecordOnClickAction();"]];
        $this->actionWidgets['existingrecord'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('existingrecord'), 'onClickAction' => "this.form.localActions.existingRecordOnClickAction();"]];
        $this->actionWidgets['actualize'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('actualizerecord'), 'hidden' => true, 'onClickAction' => 'this.form.localActions.actualize();']];
        $this->actionWidgets['visualize'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('visualizerecord'), 'hidden' => true, 'onClickAction' => 'this.form.localActions.visualize();']];
        $this->actionWidgets['cancelmode'] = ['type' => 'TukosButton', 'atts' => ['label' => $tr('cancelmode'), 'hidden' => true, 'onClickAction' => 'this.form.localActions.cancelMode();']];
        $this->actionWidgets['reset']['atts']['afterActions']/* = $this->actionWidgets['save']['atts']['afterActions']*/ = $this->actionWidgets['delete']['atts']['afterActions'] = $this->actionWidgets['new']['atts']['afterActions'] = ['postAction' => 'this.form.localActions.afterServerAction();'];
        $this->setEditConfigActionWidget();
        
        $this->onOpenAction =  $this->openAction();
	}
	public function openAction(){
	    $visualize = $this->view->tr('visualizerecord');
	    $delete = $this->view->tr('deleteRow');
	    return <<<EOT
const form = this, records = form.getWidget('records');
form.markAllChanges = true;
require (["tukos/objects/physio/woundTrack/gametracks/LocalActions"], function(LocalActions){
    form.localActions = new LocalActions({form: form});
form.localActions.hideEditionWidgets();
records.contextMenuItems = {
        row: [
            {atts: {label: '$visualize', onClick: function(evt){form.localActions.rowVisualizeClickAction(records.clickedRowValues());}}},
            {atts: {label: Pmg.message('deleteRow'), onClick: function(evt){records.deleteRow();}}}
         ]
};
});
EOT
	    ;
	}
}
?>
