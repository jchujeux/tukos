<?php

namespace TukosLib\Objects\Sports\Programs\Views\Edit;

use TukosLib\Objects\Views\Edit\View as EditView;
use TukosLib\Objects\Views\LocalActions;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\Sports\GoldenCheetah as GC;

class View extends EditView{

    use LocalActions, ViewActionStrings;
    
	function __construct($actionController){
       parent::__construct($actionController);

        $tr = $this->view->tr;
        $qtr = function($string) use ($tr){
            return $tr($string, 'escapeSQuote');
        };
        $customContents = [

            	'row1' => [
                    'tableAtts' => ['cols' => 7, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['id', 'parentid', 'name', 'fromdate', 'duration', 'todate', 'displayeddate', 'googlecalid', 'lastsynctime', 'sportsmanemail', 'synchrostart', 'synchroend', 'synchroweeksbefore', 'synchroweeksafter', 'synchnextmonday', 'questionnairetime']
                ],
            	'row2' => [
            	    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'spacing' => '0', 'widgetWidths' => ['66%', '33%'], 'widgetCellStyle' => ['verticalAlign' => 'top']],      
                    'contents' => [              
                        'col1' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 
                            'contents' => [
                                'row1' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'widgetWidths' => ['50%', '50%']],
                                    'widgets' => ['comments', 'calendar']],
                                'row2' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'widgets' => ['loadchart',  'weekloadchart']],
                                'row3' => [
                                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'widgets' => ['performedloadchart', 'weekperformedloadchart']],
                                'row4' => [
                                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                                    'widgets' => ['weeklies']
                                ]
                            ]],
                        'col2' => [
                            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 
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
        $this->onOpenAction = $this->onViewOpenAction() .  $this->view->gridOpenAction($this->view->gridWidgetName) . $this->view->gridOpenAction('weeklies');
        $plannedOptionalCols = ['name', 'duration', 'intensity', 'sport', 'sportimage', 'stress', 'distance', 'elevationgain', 'content']; $plannedColOptions = [];
        $performedOptionalCols = ['name', 'duration', 'sport', 'sportimage', 'distance', 'elevationgain', 'perceivedeffort', 'sensations', 'mood', 'athletecomments', 'coachcomments']; $plannedColOptions = [];
        $optionalWeeks = ['performedthisweek', 'plannedthisweek', 'performedlastweek', 'plannedlastweek']; $weekOptions;
        foreach($plannedOptionalCols as $col){
            $plannedColOptions[$col] = $this->view->tr($col);
        }
        foreach($performedOptionalCols as $col){
            $performedColOptions[$col] = $this->view->tr($col);
        }
        foreach($optionalWeeks as $week){
           $weekOptions[$week] = $tr($week);
       }
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
		                'onWatchLocalAction' =>  ['value' => ['googlecalid' => ['localActionStatus' => ['action' => "sWidget.pane.form.setValueOf('googlecalid', newValue);sWidget.pane.form.setValueOf('lastsynctime', null);"]],]]
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
		                        'email'  => Widgets::description(Widgets::TextBox(['edit' => ['label' => $tr('email')]]), false),
		                        'role' => Widgets::description(Widgets::storeSelect([
		                            'edit' => ['storeArgs' => ['data' => Utl::idsNamesStore(['reader', 'writer', 'owner'], $tr)], 'label' => $tr('role')]
		                        ]), false),
		                    ]])),
		            'name' => Widgets::textBox(Widgets::complete(['label' => $tr('calendarname'), 'hidden' => true])),
		            'acl' => Widgets::simpleDgrid(Widgets::complete(
		                ['label' => $tr('accessrules'), 'hidden' => true, 'storeArgs' => ['idProperty' => 'idg'], 'initialId' => false, 'style' => ['width' => '400px'],
		                    'colsDescription' => [
		                        'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
		                        'email'  => Widgets::description(Widgets::TextBox(['edit' => ['label' => $tr('email')]]), false),
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
		$durationFormat = ['renderCell' => 'renderContent', 'formatType' => 'secondsToHHMMSS']; $numberFormat = ['renderCell' => 'renderContent', 'formatType' => 'number', 'formatOptions' => ['places' => 0]];
		$this->actionWidgets['sessionstracking'] = ['type' => 'ObjectProcess', 'atts' => ['label' => $this->view->tr('Sessionstracking'), 'allowSave' => true, 'includeWidgets' => ['parentid', 'synchrostart', 'synchroend']]];
		$this->actionLayout['contents']['actions']['widgets'][] = 'sessionstracking';
		$this->actionWidgets['sessionstracking']['atts']['dialogDescription'] = [
		    //'closeOnBlur' => true,
		    'paneDescription' => [
		        'widgetsDescription' => [
		            'eventformurl' => Widgets::checkBox(Widgets::complete(['title' => $this->view->tr('showeventtrackingformurl'), 'onWatchLocalAction' => $this->watchCheckboxLocalAction('eventformurl')])),
		            'formlogo' => Widgets::textBox(Widgets::complete(['label' => $tr('trackingformlogo'), 'style' => ['width' => '15em'], 'onWatchLocalAction' => $this->watchLocalAction('formlogo')])),
		            'formpresentation' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['MobileTextBox', 'default'], $tr)], 'label' => $tr('formpresentation'),
		                'onWatchLocalAction' => $this->watchLocalAction('formpresentation')])),
		            'version' => Widgets::storeSelect(Widgets::complete(['storeArgs' => ['data' => Utl::idsNamesStore(['V2'], $tr, [false, 'ucfirst', false])], 'label' => $tr('version'),
		                'value' => $this->view->model->defaultSessionsTrackingVersion, 'onWatchLocalAction' => $this->watchLocalAction('version')])),
		            'gcathlete' => Widgets::textBox(Widgets::complete(['title' => $tr('Gcathlete'), 'style' => ['width' => '15em'], 'onWatchLocalAction' => $this->urlChangeLocalAction('gcathlete', $tr)])),
		            'gcsynchrostart' => Widgets::tukosDateBox(['title' => $tr('synchrostart'), 'onWatchLocalAction' => $this->urlChangeLocalAction('gcsynchrostart', $tr, false)]),
		            'gcsynchroend' => Widgets::tukosDateBox(['title' => $tr('synchroend'), 'onWatchLocalAction' => $this->urlChangeLocalAction('gcsynchrostart', $tr, false)]),
		            'gcmetricstoinclude' => Widgets::multiSelect(Widgets::complete(['title' => $tr('gcmetricstoinclude'), 'options' => GC::metricsOptions($tr), 'style' => ['height' => '150px'], 
		                'onWatchLocalAction' =>  $this->urlChangeLocalAction('gcmetricstoinclude', $tr)])),
		            'gcactivitiesmetrics' => Widgets::basicGrid(Widgets::complete(['label' => $tr('gcactivitiesmetrics'), 'allowSelectAll' => true, 'dynamicColumns' => true, 'adjustLastColumn' => false, 
		                'colsDescription' => GC::metricsColsDescription($tr), 'nonGcCols' => GC::nonGcCols(), 'permanentGcOptions' => GC::permanentGcOptions()])),
		            'gclink' => Widgets::htmlContent(['title' => $tr('gclink'), 'readonly' => true]),
		            'gcinput' => Widgets::textArea(Widgets::complete(['title' => $tr('gcinput')])),
		            'gcimport' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('gcimport'), 'onClickAction' => $this->gcimportOnClickAction($tr)]],
		            'gcsync' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('gcsync'), 'onClickAction' => $this->gcsyncOnClickAction($tr)]],
		            'close' => ['type' => 'TukosButton', 'atts' => ['label' => $tr('close'), 'onClickAction' => "this.pane.close();\n"]],
		        ],
		        'layout' => [
		            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
		            'contents' => [
		                'row1' => [
		                    'tableAtts' =>['cols' => 4,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 150],
		                    'widgets' => ['eventformurl', 'formlogo', 'formpresentation', 'version'],
		                ],
		                'headerRow' => [
		                    'tableAtts' => ['cols' =>  1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'], 
		                    'contents' => [
		                        'title' => [
		                            'tableAtts' => ['cols' =>  1, 'customClass' => 'labelsAndValues', 'label' => $tr('gcsynchronization')]
		                        ]
		                ]],
		                'row2' => [
		                    'tableAtts' =>['cols' => 4,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'labelWidth' => 150],
		                    'widgets' => ['gcathlete', 'gcsynchrostart', 'gcsynchroend'],
		                ],
		                'row3' => [
		                    'tableAtts' =>['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'widgetWidths' => ['10%', '90%']],
		                    'contents' => [
		                        'col1' => [
		                            'tableAtts' =>['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
		                            'widgets' => ['gcmetricstoinclude']
		                        ],
		                        'col2' => [
		                            'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
		                            'contents' => [
		                                'row1' => [
		                                    'tableAtts' => ['cols' => 2,  'customClass' => 'labelsAndValues', 'showLabels' => false],
		                                    'contents' => [
		                                        'col1' => [
		                                            'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
		                                            'widgets' => ['gclink']
		                                        ],
		                                        'col2' => [
		                                            'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false],
		                                            'widgets' => ['gcimport', 'gcsync']
		                                        ]
		                                    ]
		                                ],
		                                'row2' => [
		                                    'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
		                                    'widgets' => ['gcinput'],
		                                ],
		                            ],
		                        ]
		                    ],
		                ],
		                'row4' => [
		                    'tableAtts' =>['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
		                    'widgets' => ['gcactivitiesmetrics'],
		                ],
		                'row5' => [
		                    'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => false],
		                    'widgets' => ['close'],
		                ],
		            ],
		        ],
		        'onOpenAction' => $this->sessionsTrackingOnOpenAction($tr),
		    ]];
	}
/*
	private function sessionsTrackingActionWidgetDescription($action){
	    return ['type' => 'TukosButton', 'atts' => ['label' => $this->view->tr($action), 'onClickAction' => $this->sessionsTrackingActionButtonsOnClickAction($action)
	        ]];
	}
*/
}
?>
