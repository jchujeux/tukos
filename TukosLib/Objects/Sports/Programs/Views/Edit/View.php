<?php

namespace TukosLib\Objects\Sports\Programs\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\Sports\Programs\SessionsTracking;
use TukosLib\Objects\Sports\Programs\ProgramsConfig;
use TukosLib\TukosFramework as Tfk;

class View extends EditView{

    use LocalActions, ViewActionStrings, SessionsTracking, ProgramsConfig;
    
	function __construct($actionController){
       parent::__construct($actionController);

        $tr = $this->view->tr;
        $qtr = function($string) use ($tr){
            return $tr($string, 'escapeSQuote');
        };
        $customContents = [
            	'row1' => [
                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => '130'],
                    'widgets' => ['id', 'parentid', 'coach', 'name', 'fromdate', 'duration', 'todate', 'displayeddate', 'googlecalid', 'lastsynctime', 'sportsmanemail', 'coachemail', 'coachorganization', 'synchrostart', 'synchroend', 'synchroweeksbefore', 'synchroweeksafter',
                        'synchnextmonday', 'questionnairetime', 'stsdays', 'ltsdays', 'stsratio', 'initialsts', 'initiallts', 'displayfromdate', 'displayfromsts', 'displayfromlts', 'synchrosource']
                ],
            	'row2' => [
            	    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['80%', '20%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],      
                    'contents' => [              
                        'col1' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 
                            'contents' => [
                                'row1' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['50%', '50%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],
                                    'widgets' => ['comments', 'calendar']],
                                'row2' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'widgets' => ['loadchart',  'weekloadchart']],
                                'row3' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'widgets' => ['performedloadchart', 'weekperformedloadchart']],
                                'spidersrow' => [
                                    'tableAtts' => ['cols' => 6, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'widgets' => []],
                                'row4' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'widgets' => ['weeklies']
                                ]
                            ]],
                        'col2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'id' => 'templatesPane'], 
                            'widgets' => ['templates',  'warmup', 'mainactivity', 'warmdown'],
                       ],
                    ]
                ],
                'row3' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0'],
                    'widgets' => ['sptsessions'],
                ]
        ];
        $this->dataLayout['contents'] = array_merge($customContents, Utl::getItems(['rowbottom', 'rowacl'], $this->dataLayout['contents']));
        $this->onOpenAction =  $this->view->OpenEditAction() . $this->onViewOpenAction() .  $this->view->gridOpenAction($this->view->gridWidgetName) . $this->view->gridOpenAction('weeklies') . $this->viewModeOptionOpenAction();
        $plannedOptionalCols = ['name', 'duration', 'intensity', 'sport', 'sportimage', 'stress', 'distance', 'elevationgain', 'content']; $plannedColOptions = [];
        $performedOptionalCols = ['name', 'duration', 'sport', 'sportimage', 'distance', 'elevationgain', 'perceivedeffort', 'sensations', 'mood', 'athletecomments', 'coachcomments']; $plannedColOptions = [];
        $optionalWeeks = ['performedthisweek', 'plannedthisweek', 'performedlastweek', 'plannedlastweek'];
        foreach($plannedOptionalCols as $col){
            $plannedColOptions[$col] = isset($this->view->chartsCols[$col]) ? $this->view->chartsCols[$col]['tCol'] : $this->view->tr($col);
        }
        foreach($performedOptionalCols as $col){
            $performedColOptions[$col] = isset($this->view->chartsCols[$col]) ? $this->view->chartsCols[$col]['tCol'] : $this->view->tr($col);
        }
        foreach($optionalWeeks as $week){
           $weekOptions[$week] = $tr($week);
       }
       $this->actionWidgets['new']['atts']['afterActions'] = $this->actionWidgets['delete']['atts']['afterActions'] = $this->actionWidgets['reset']['atts']['afterActions'] = [
            'postAction' => <<<EOT
var grid = this.form.getWidget('sptsessions');
grid.loadChartUtils.updateCharts(grid, 'changed');
EOT
        ];
       $this->actionWidgets['export']['atts']['dialogDescription'] = [
            'paneDescription' => [
                'widgetsDescription' => [
                   'presentation' => Widgets::storeSelect(Widgets::complete(
                        ['storeArgs' => ['data' => Utl::idsNamesStore($this->view->model->options('presentation'), $this->view->tr)], 'title' => $this->view->tr('presentation'), 'value' => 'perdate',
                         'onWatchLocalAction' => $this->watchLocalAction('presentation'),
                    ])),
                    'plannedcolstoinclude' => Widgets::multiSelect(Widgets::complete(['title' => $this->view->tr('plannedcolstoinclude'), 'options' => $plannedColOptions, 'style' => ['height' => '150px'],
                        'onWatchLocalAction' =>  $this->watchLocalAction('plannedcolstoinclude')])),
                    'performedcolstoinclude' => Widgets::multiSelect(Widgets::complete(['title' => $this->view->tr('performedcolstoinclude'), 'options' => $performedColOptions, 'style' => ['height' => '150px'],
                        'onWatchLocalAction' =>  $this->watchLocalAction('performedcolstoinclude')])),
                    'optionalweeks' => Widgets::multiSelect(Widgets::complete(['title' => $this->view->tr('weekstoinclude'), 'options' => $weekOptions, 'style' => ['height' => '150px'], 'onWatchLocalAction' =>  $this->watchLocalAction('optionalweeks')])),
                    'contentseparator' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('separator'), 'style' => ['width' => '5em'], 'onWatchLocalAction' =>  $this->watchLocalAction('contentseparator')])),
                   'prefixwarmup' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('warmup'), 'style' => ['width' => '10em'], 'onWatchLocalAction' =>  $this->watchLocalAction('prefixwarmup')])),
                   'prefixmainactivity' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('mainactivity'), 'style' => ['width' => '10em'],  'onWatchLocalAction' => $this->watchLocalAction('prefixmainactivity')])),
                   'prefixwarmdown' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('warmdown'), 'style' => ['width' => '10em'], 'onWatchLocalAction' => $this->watchLocalAction('prefixwarmdown')])),
                   'prefixcomments' => Widgets::textBox(Widgets::complete(['title' => $this->view->tr('prefix') . ' ' . $this->view->tr('comments'), 'style' => ['width' => '10em'], 'onWatchLocalAction' =>  $this->watchLocalAction('prefixcomments')])),
                   'duration' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showduration'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('duration')])),
                   'intensity' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showintensity'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('intensity')])),
                   'sport' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showsport'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('sport')])),
                   'sportimage' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showsportimage'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('sportimage')])),
                   'stress' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showmechstress'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('stress')])),
                   'rowintensitycolor' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('rowintensitycolor'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('rowintensitycolor')])),
                   'firstday' => Widgets::tukosDateBox(['title' => $this->view->tr('weekof'), 'onWatchLocalAction' => ['value' => [
                        'weekoftheyear' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return dutils.getISOWeekOfYear(newValue)"]],
                        'weekofprogram' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return dutils.difference(sWidget.pane.form.valueOf('fromdate'), newValue, 'week') + 1"]],
                        'update' => ['hidden' => ['action' => "return false;" ]],
                    ]]]),
                   'lastday' => Widgets::tukosDateBox(['title' => $this->view->tr('todate'), 'onWatchLocalAction' => ['value' => ['update' => ['hidden' => ['action' => "return false;" ]]]]]),
                   'weekoftheyear' => Widgets::textBox(Widgets::complete(['title' =>$this->view->tr('weekoftheyear'), 'style' => ['width' => '3em']])),
                   'weekofprogram' => Widgets::textBox(Widgets::complete(['title' =>$this->view->tr('weekofprogram'), 'style' => ['width' => '3em']])),
                   'weeksinprogram' => Widgets::textBox(Widgets::complete(['title' =>$this->view->tr('weeksinprogram'), 'style' => ['width' => '3em']])),
                    'update' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('prepare'), 'hidden' => true, 'onClickAction' => $this->paneUpdateOnClickAction()]],
                    'weeklytable'  => Widgets::htmlContent(Widgets::complete(['title' => $this->view->tr('weeklyprogram'), 'hidden' => true])),
                ],
                'layout' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                    'contents' => [
                        'row8' => [
                            'tableAtts' =>['cols' =>1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                            'contents' => [
                                'titlerow' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'contents' => ['row1' => ['tableAtts' => ['label' => $this->view->tr('weeklyprogram')]]],
                                ],
                                'row2' => [
                                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert'],
                                    'contents' => [
                                        'col1' => [
                                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                            'widgets' => ['optionalweeks']
                                        ],
                                        'col2' => [
                                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                                            'contents' => [
                                                'row8' => [
                                                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                                                    'widgets' => ['firstday', 'lastday', 'weekoftheyear', 'weekofprogram', 'weeksinprogram'],
                                                ],
                                                'row9' => [
                                                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 100],
                                                    'widgets' => ['prefixwarmup', 'prefixmainactivity', 'prefixwarmdown', 'prefixcomments','contentseparator'],
                                                ],
                                                'row10' => [
                                                    'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 50],
                                                    'widgets' => ['presentation'/*, 'duration', 'intensity', 'sport', 'sportimage', 'stress'*/, 'rowintensitycolor'],
                                                ],
                                            ]
                                        ],
                                        'col3' => [
                                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                            'widgets' => ['plannedcolstoinclude', 'performedcolstoinclude']
                                        ]
                                    ]
                                ],
                                'row11' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['update'],
                                ],
                                'row12' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
                                    'widgets' => ['weeklytable'],
                                ],
                            ],
                        ],
                    ],
                ],                          
                'onOpenAction' => $this->exportPaneOnOpenAction(),
                'customContentCallback' => $this->exportCustomContent($tr),
            ]
        ];
       $this->actionWidgets['googlesync'] =  ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Googlesync'), 'allowSave' => true,
           'urlArgs' => ['query' => ['params' => json_encode(['process' => 'googleSynchronize', 'save' => true])]], 'includeWidgets' => ['parentid', 'googlecalid', 'synchrostart', 'synchroend', 'lastsynctime'],
           'conditionDescription' => $this->googleSyncConditionDescription($qtr('needgooglecalid'), $qtr('youneedtoselectagooglecalid')),
       ]];
       $this->actionLayout['contents']['actions']['widgets'][] = 'googlesync';
       $this->actionWidgets['googleconf'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Googleconf'), 'allowSave' => true]];
		$this->actionLayout['contents']['actions']['widgets'][] = 'googleconf';
		$this->actionWidgets['googleconf']['atts']['dialogDescription'] = [
		    'paneDescription' => [
		        'widgetsDescription' => [
		            'googlecalid' => Widgets::restSelect(Widgets::complete([
		                'title' => $this->view->tr('googlecalid'),	'storeArgs' => ['object' => 'calendars', 'params' => ['getOne' => 'calendarSelect', 'getAll' => 'calendarsSelect']],
		                'onWatchLocalAction' =>  ['value' => ['googlecalid' => ['localActionStatus' => ['action' => $this->googleConfCalIdOnWatchAction()]],]]
		            ])),
		            'newcalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('newcalendar'), 'onClickAction' => $this->googleConfNewCalendarOnClickAction(),
		            ]],
		            'managecalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('managecalendar'), 'onClickAction' => $this->googleConfManageCalendarOnClickAction($qtr('needgooglecalid'), $qtr('youneedtoclicknewcalendar')),
		            ]],
		            
		            'close' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('close'), 'onClickAction' =>
		                "this.pane.close();\n"
		            ]],
		            'newname' => Widgets::textBox(Widgets::complete(['label' => $tr('newcalendarname'), 'hidden' => true])),
		            'newacl' => Widgets::simpleDgrid(Widgets::complete(
		                ['label' => $tr('accessrules')/*, 'storeType' => 'MemoryTreeObjects'*/, 'hidden' => true, 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'style' => ['width' => '400px'],
		                    'colsDescription' => [
		                        'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
		                        'email'  => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('email')]]), false),
		                        'role' => Widgets::description(Widgets::storeSelect([
		                            'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['reader', 'writer', 'owner'], $tr)], 'label' => $tr('role')]
		                        ]), false),
		                    ]])),
		            'name' => Widgets::textBox(Widgets::complete(['label' => $tr('calendarname'), 'hidden' => true])),
		            'acl' => Widgets::simpleDgrid(Widgets::complete(
		                ['label' => $tr('accessrules'), 'hidden' => true, 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'style' => ['width' => '400px'],
		                    'colsDescription' => [
		                        'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
		                        'email'  => Widgets::description(Widgets::textBox(['edit' => ['label' => $tr('email')]]), false),
		                        'role' => Widgets::description(Widgets::storeSelect([
		                            'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['reader', 'writer', 'owner'], $tr)], 'label' => $tr('role')]
		                        ]), false),
		                    ]])),
		            'createcalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('createcalendar'), 'hidden' => true, 'onClickAction' => $this->googleConfCreateCalendarOnClickAction(),
		            ]],
		            'updateacl' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('updateacl'), 'hidden' => true, 'onClickAction' => $this->googleConfUpdateAclOnClickAction(),
		            ]],
		            'deletecalendar' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('deletecalendar'), 'hidden' => true, 'onClickAction' => $this->googleConfDeleteCalendarOnClickAction(),
		            ]],
		            'hide' => ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr('hide'), 'hidden' => true, 'onClickAction' => $this->googleConfHideOnClickAction(),
		            ]],
		        ],
		        'layout' => [
		            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'labelWidth' => 100],
		            'contents' => [
		                'row1' => [
		                    'tableAtts' =>['cols' =>1,  'customClass' => 'labelsAndValues', 'showLabels' => true],
		                    'widgets' => ['googlecalid'],
		                ],
		                'row2' => [
		                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false],
		                    'widgets' => ['close', 'newcalendar', 'managecalendar'],
		                ],
		                'row4' => [
		                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
		                    'widgets' => ['newname', 'name'],
		                ],
		                'row5' => [
		                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
		                    'widgets' => ['newacl', 'acl'],
		                ],
		                'row6' => [
		                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false],
		                    'widgets' => ['createcalendar', 'updateacl', 'deletecalendar', 'hide'],
		                ],
		            ],
		        ],
		        'onOpenAction' => $this->googleConfOnOpenAction(),
		    ]];
        $this->setProgramsConfigActionWidget();
        $this->setSessionsTrackingActionWidget();
        $this->actionWidgets['viewplanned'] = ['type' => 'TukosRadioButton', 'atts' => ['name' => 'modeOption', 'label' => $tr('viewplanned'), 'value' => 'viewplanned', 'onClickAction' => $this->viewModeOptionOnClick('viewplanned')]];
        $this->actionWidgets['viewperformed'] = ['type' => 'TukosRadioButton', 'atts' => ['name' => 'modeOption', 'label' => $tr('viewperformed'), 'value' => 'viewperformed', 'onClickAction' => $this->viewModeOptionOnClick('viewperformed')]];
        $this->actionWidgets['viewall'] = ['type' => 'TukosRadioButton', 'atts' => ['name' => 'modeOption', 'label' => $tr('viewall'), 'value' => 'viewall', 'checked' => true, 'onClickAction' => $this->viewModeOptionOnClick('viewall')]];
        $this->actionLayout['tableAtts']['cols'] = 3;
        $this->actionLayout['contents'] = array_merge(array_splice($this->actionLayout['contents'], 0, 1), 
            ['sessionViewOptions' => ['tableAtts' => ['cols' => 1, 'customClass' => 'actionTable', 'showLabels' => true,  'label' => '<b>' . $this->view->tr('SessionsMode') . ':</b>'], 'widgets' => [ 'viewplanned',  'viewperformed', 'viewall']]],
            $this->actionLayout['contents']);
	}
	public function viewModeOptionOpenAction(){
	    $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
	    $plannedCols = json_Encode($sessionsModel->plannedCols);
	    $performedCols = json_Encode($sessionsModel->performedCols);
	    return <<<EOT
var form = this;
require (["tukos/objects/sports/programs/LocalActions"], function(LocalActions){
    form.localActions = new LocalActions({form: form, plannedColumns: {$plannedCols}, performedColumns: {$performedCols}});
    if (form.viewModeOption){
        form.getWidget(form.viewModeOption).set('checked', true);
        form.localActions.viewModeOption(form.viewModeOption);
    }
});
EOT
	    ;
	}
	public function viewModeOptionOnClick($optionName){
        return <<<EOT
this.form.localActions.viewModeOption('{$optionName}', true);
EOT
        ;
	}
}
?>
