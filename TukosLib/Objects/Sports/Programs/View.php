<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Collab\Calendars\CalendarsViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

	use CalendarsViewUtils, ViewActionStrings;
	
	function __construct($objectName, $translator=null){
	    parent::__construct($objectName, $translator, 'Sportsman', 'Title');
		$this->doNotEmpty = ['displayeddate', 'synchrostart', 'synchroend'];
		$tr = $this->tr;
		$this->setGridWidget('sptsessions');
		$this->allowedNestedWatchActions = 0;
		$this->allowedNestedRowWatchActions = 0;
		$chartsCols = [];
		foreach(['duration', 'distance', 'elevationgain', 'sts', 'lts', 'tsb'] as $col){
		    $chartsCols[$col] = ['plot' => 'lines', 'tCol' => $tr($col)];
		}
		foreach(['load', 'intensity', 'stress', 'gctrimp100', 'perceivedload', 'perceivedeffort', 'sensations', 'mood', 'fatigue'] as $col){
		    $chartsCols[$col] = ['plot' => 'cluster', 'tCol' => $tr($col)];
		}
		foreach(['duration' => '(mn)', 'distance' => '(km)', 'elevationgain' => '(dam)'] as $col => $legendUnit){
		    $chartsCols[$col]['legendUnit'] = $legendUnit;
		}
		foreach(['distance' => ' km', 'elevationgain' => ' m'] as $col => $tooltipUnit){
		    $chartsCols[$col]['tooltipUnit'] = $tooltipUnit;
		}
		$chartsCols['elevationgain']['tooltipUnit'] = ' (m)';
		foreach(['elevationgain' => ['day' => 10, 'week' => 10], 'gctrimp100' => ['day' => 25, 'week' => 100], 'sts' => ['day' => 1, 'week' => 0.1], 'lts' => ['day' => 1, 'week' => 0.1], 'tsb' => ['day' => 1, 'week' => 0.1]] as $col => $scalingFactor){
		    $chartsCols[$col]['scalingFactor'] = $scalingFactor;
		}
		foreach(['load' => ['day' => 120, 'week' => 600], 'perceivedload' => ['day' => 120, 'week' => 600]] as $col => $normalizationFactor){
		    $chartsCols[$col]['normalizationFactor'] = $normalizationFactor;
		}
		foreach(['intensity', 'stress', 'perceivedeffort', 'sensations', 'mood', 'fatigue'] as $col){
		    $chartsCols[$col]['isDurationAverage'] = true;
		}
		$chartTypes = [
		    'program' => ['idp' => 'week', 'defaultidptype' => 'weekoftheyear', 'idptypes' => [['id' => 'weekoftheyear', 'name' => $tr('weekoftheyear')], ['id' =>  'weekofprogram', 'name' =>  $tr('weekofprogram')]],
		          'sortAttribute' => 'weekof'
		    ],
		    'weekly' => ['idp' => 'day', 'defaultidptype' => 'dateofday', 'idptypes' => [['id' => 'dayofweek', 'name' => $tr('dayofweek')], ['id' =>  'dateofday', 'name' =>  $tr('dateofday')]], 
		        'sortAttribute' => 'dayofweek']
		];
		$chartFilter = ['planned' => 'ne', 'performed' => 'eq'];
		$chartCols = [
		    'planned' => array_intersect_key($chartsCols, array_flip(['duration', 'distance', 'elevationgain', 'intensity', 'load', 'stress'])),
		    'performed' => array_intersect_key($chartsCols, array_flip(['duration', 'distance', 'elevationgain', 'gctrimp100', 'sts', 'lts', 'tsb', 'perceivedeffort', 'perceivedload', 'sensations', 'mood', 'fatigue']))
		];
		$summaryRow = ['cols' => [
		    'day' => ['content' =>  ['Total']],
		    'duration' => ['atts' => ['formatType' => 'minutesToHHMM'], 'content' => [['rhs' => "var duration = #duration#.split(':'); return res + duration[0]*60 + Number(duration[1]);"]]],
		    'distance' => ['content' => [['rhs' => "return res + Number(#distance#);"]]],
		    'elevationgain' => ['content' => [['rhs' => "return res + Number(#elevationgain#);"]]],
		    'gctrimp100' => ['content' => [['rhs' => "return res + Number(#gctrimp100#);"]]]
		]];
		$chartsAtts = [
		    'loadchart' => ['name' => 'loadchart', 'type' => $chartTypes['program'], 'filter' => $chartFilter['planned'], 'cols' => $chartCols['planned']],
		    'performedloadchart' => ['name' => 'performedloadchart', 'type' => $chartTypes['program'], 'filter' => $chartFilter['performed'], 'cols' => $chartCols['performed']],
		    'weekloadchart' => ['name' => 'weekloadchart', 'type' => $chartTypes['weekly'], 'filter' => $chartFilter['planned'], 'cols' => $chartCols['planned']],
		    'weekperformedloadchart' => ['name' => 'weekperformedloadchart', 'type' => $chartTypes['weekly'], 'filter' => $chartFilter['performed'], 'cols' => $chartCols['performed']]
		];
		$this->addToTranslate(['w', 'dateofday', 'dayofweek', 'weekoftheyear', 'weekofprogram', 'weekendingon']);
        $dateChangeLocalAction = function($serverTrigger) use ($chartsAtts){
            return [
                'loadchart' => ['localActionStatus' => ['triggers' => ['server' => $serverTrigger, 'user' => true], 'action' => $this->dateChangeLoadChartLocalAction()]],
                'performedloadchart' => ['localActionStatus' => ['triggers' => ['server' => $serverTrigger, 'user' => true], 'action' => $this->dateChangeLoadChartLocalAction()]],
                'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekloadchart')]],
                'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekperformedloadchart')]],
                'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $this->synchroStartLocalAction('newValue', '#synchnextmonday'),]],
                'weeklies' => $this->dateChangeGridLocalAction('newValue', 'tWidget', 'tWidget.allowApplicationFilter')
            ];
        };
        $loadChartCustomization = function($idProperty, $idPropertyStoreData, $colsToExcludeOptions) use ($tr) {
		    $idPropertyType = $idProperty.'type'; $options = [];
		    return [
		      $idPropertyType => ['att' =>  $idPropertyType, 'type' => 'StoreSelect', 'name' => $this->tr($idPropertyType), 'storeArgs' => ['data' => $idPropertyStoreData]],
		        'colsToExclude' => ['att' => 'colsToExclude', 'type' => 'MultiSelect', 'name' => $this->tr('colsToExclude'), 'options' => $colsToExcludeOptions
		      ]
		    ];
        };
		$loadChartDescription = function($chartAtts, $idpTypeLocalActionString) use ($loadChartCustomization, $tr, $summaryRow) {
		    $idProperty = $chartAtts['type']['idp'];
		    $chartName = $chartAtts['name'];
		    $tableAttsColumns = ['id' => ['field' => 'id'], $idProperty => ['label' => $tr($idProperty), 'field' => $idProperty, 'width' => 65]]; 
		    $series = []; $linesAxisLabel = ''; $clusterAxisLabel = '';
		    foreach($chartAtts['cols'] as $col => $atts){
		        $plot = $atts['plot'];
		        $colLabel = $atts['tCol'];
		        $legendLabel = Utl::getItem('legendUnit', $atts, '');
		        $tableAttsColumns[$col] = ['label' => $colLabel . ' ' . Utl::getItem('tooltipUnit', $atts, '') , 'field' => $col, 'width' => 60];
		        $series[$col] = ['value' => ['y' => $col, 'text' => $idProperty, 'tooltip' => $col . 'Tooltip'], 'options' => ['plot' => $plot, 'label' => $colLabel, 'legend' => $colLabel]];
		        if ($plot === 'lines'){
		            $linesAxisLabel .= $colLabel /*. $legendLabel*/ . ' ';
		        }else{
		            $clusterAxisLabel .= $colLabel /*. $legendLabel*/ . ' ';
		        }
		        $colsToExcludeOptions[$col] = ['option' => $tr($col), 'tooltip' => $tr($col . 'Tooltip')];
		    }
		    return ['type' => 'chart', 'atts' => ['edit' => [
		        'title' => $tr($chartName), 'idProperty' => $idProperty, 'kwArgs'	 => ['sort'=> [['attribute' => $chartAtts['type']['sortAttribute'], 'descending' => false]]],
		        'style' => ['width' => '700px'],
		        'chartHeight' => '300px',
		        'showTable' => 'no',
		        'tableAtts' => substr($chartName, 0, 4) === "week" ? ['columns' => $tableAttsColumns, 'summaryRow' => $summaryRow] : ['columns' => $tableAttsColumns],
		        ($idProperty.'type') => $chartAtts['type']['defaultidptype'],
		        'chartAtts' => $chartAtts,
		        'axes' =>  [
		            'x'   => ['title' => $tr($chartAtts['type']['defaultidptype']), 'titleOrientation' => 'away', 'titleGap' => 5, 'labelCol' => $idProperty, 'majorTicks' => true, 'majorTickStep' => 1, 
		                'minorTicks' => false, 'titleFont' => 'normal normal normal 11pt Arial'],
		            'y1' => ['title' => $clusterAxisLabel, 'vertical' => true, 'min' => 0, 'max' => 10, 'titleFont' => 'normal normal normal 8pt Arial'],
		            'y2' => ['title' => $linesAxisLabel, 'vertical' => true, 'leftBottom' => false/*, 'min' => 0*/, 'titleFont' => 'normal normal normal 8pt Arial'],
		        ],
		        'plots' =>  [
		            'lines' => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y2', 'lines' => true, 'markers' => true, 'tension' => 'X', 'shadow' => ['dx' => 1, 'dy' => 1, 'width' => 2]],
		            'cluster' => ['plotType' => 'ClusteredColumns', 'vAxis' => 'y1', 'gap' => 3],
		            $idProperty	  => ['plotType' => 'Indicator', 'hAxis' => 'x', 'vAxis' => 'y2', 'stroke' => null, 'outline' => null, 'fill' => null, 'labels' => false, 
		                'lineStroke' => ['color' => 'red', 'style' => 'shortDash', 'width' => 2]],
		        ],
		        'legend' => ['type' => 'SelectableLegend', 'options' => []],
		        'series' => $series,
		        'tooltip' => true,
		        'mouseZoomAndPan' => true,
		        //'mouseIndicator' => ['plot' => 'lines', 'kwArgs' => ['series' => 'tsb', 'mouseOver' => true, 'lineStroke' => ['width' => 2, 'color' => 'blue']]],
		        'onWatchLocalAction' => [
		            $idProperty.'type' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $idpTypeLocalActionString]]],
		            'colsToExclude' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.set('value', sWidget.get('value')); return true;"]]],
		            'hidden' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->hiddenLoadChartAction($chartName)]]]
		        ],
		        'customizableAtts' => $loadChartCustomization($idProperty, $chartAtts['type']['idptypes'], $colsToExcludeOptions)
		    ]]];
		};
		$customDataWidgets = [
			'comments' => ['atts' => ['edit' => ['height' => '200px']]],
		    'fromdate' => ViewUtils::tukosDateBox($this, 'Begins on', ['atts' => ['edit' => [
							'onChangeLocalAction' => [
								'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(newValue, sWidget.valueOf('#duration'), sWidget.valueOf('#todate'),true)}" ],
							    'loadchart' => ['localActionStatus' => $this->loadChartLocalAction('loadchart')],
							    'performedloadchart' => ['localActionStatus' => $this->loadChartLocalAction('performedloadchart')],
							]]]]),
			'duration'  =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration', ['atts' => [
					'edit' => [
							'onChangeLocalAction' => [
								'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#todate'),true)}" ]
							],
							'unit' => ['style' => ['width' => '6em'], 'onWatchLocalAction' => ['value' => "widget.numberField.set('value', dutils.convert(widget.numberField.get('value'), oldValue, newValue));"]],
					],
					'storeedit' => ['formatType' => 'numberunit'],
					'overview' => ['formatType' => 'numberunit'],
			]]),
			'todate'   => ViewUtils::tukosDateBox($this, 'Ends on', ['atts' => ['edit' => [
							'onChangeLocalAction' => [
								'duration'  => ['value' => "if (!newValue){return '';}else{return dutils.durationString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#duration'),true)}" ],
							    'loadchart' => ['localActionStatus' => $this->loadChartLocalAction('loadchart')],
							    'performedloadchart' => ['localActionStatus' => $this->loadChartLocalAction('performedloadchart')],
							],
						]
					]
				]
			),
			'displayeddate' => $this->displayedDateDescription(['atts' => ['edit' => ['onWatchLocalAction' => ['value' => $dateChangeLocalAction(true)]]]]),
            'googlecalid' => ViewUtils::textBox($this, 'Googlecalid', ['atts' => ['edit' => ['readonly' => true]]]),
            'sportsmanemail' => ViewUtils::textBox($this, 'Email', ['atts' => ['edit' => ['disabled' => true, 'hidden' => true]]]),
			'lastsynctime' => ViewUtils::timeStampDataWidget($this, 'Lastsynctime', ['atts' => ['edit' => ['disabled' => true]]]),
			'synchrostart' => ViewUtils::tukosDateBox($this, 'Synchrostart', ['atts' => ['edit' => ['onWatchLocalAction' => ['value' => [
                        'synchroweeksbefore' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]],
                ]]]]]),
			'synchroend' => ViewUtils::tukosDateBox($this, 'Synchroend', ['atts' => ['edit' => ['onWatchLocalAction' => ['value' => [
                        'synchroweeksafter' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]],
                ]]]]]),
			'synchroweeksbefore' => ViewUtils::tukosNumberBox($this, 'Synchroweeksbefore', ['atts' => ['edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => [
                        'synchrostart' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => 
                        "return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date(sWidget.valueOf('#displayeddate'))), 'week', -newValue));" ]],
                ]]]]]),
			'synchroweeksafter' => ViewUtils::tukosNumberBox($this, 'Synchroweeksafter', ['atts' => ['edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => [
                        'synchroend' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => 
                        	"var nextMonday = sWidget.valueOf('#synchnextmonday') === 'YES';" . 
                        	"return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(nextMonday ? 1 : 7, new Date(sWidget.valueOf('#displayeddate'))), 'week', nextMonday ? newValue + 1 : newValue));" 
                        ]],
                ]]]]]),
			'synchnextmonday' => viewUtils::storeSelect('synchnextmonday', $this, 'Synchnextmonday', null, ['atts' => ['edit' => ['style' => ['width' => '4em'], 'onWatchLocalAction' => ['value' => [
					'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $this->synchroStartLocalAction('#displayeddate', 'newValue'),]],
			]]]]]),
			'questionnairetime'  =>  ViewUtils::timeStampDataWidget($this, 'QuestionnaireTime', ['atts' => ['edit' => ['disabled' => true]]]),
		    'loadchart' => $loadChartDescription($chartsAtts['loadchart'],$this->loadChartLocalAction('loadchart')),
		    'weekloadchart' => $loadChartDescription($chartsAtts['weekloadchart'], $this->weekLoadChartLocalAction('weekloadchart')),
		    'performedloadchart' => $loadChartDescription($chartsAtts['performedloadchart'],$this->loadChartLocalAction('performedloadchart')),
		    'weekperformedloadchart' => $loadChartDescription($chartsAtts['weekperformedloadchart'], $this->weekLoadChartLocalAction('weekperformedloadchart')),
			'calendar' => $this->calendarWidgetDescription([
				'type' => 'StoreSimpleCalendar', 
			    'atts' => ['edit' => [
					'columnViewProps' => ['minHours' => 0, 'maxHours' => 4],
					'style' => ['height' => '350px', 'width' => '700px'], 
					'timeMode' => 'duration', 'durationFormat' => 'time', 'moveEnabled' => true,
					'customization' => ['items' => [
						 'style' => ['backgroundColor' => ['field' => 'intensity', 'map' => Sports::$intensityColorsMap, 'defaultValue' => 'Peru'], 
						     'color' => ['field' => 'mode', 'map' => ['planned' => 'white', 'performed' => 'black']],
						 'fontStyle' => ['field' => 'mode', 'map' => ['planned' => 'normal', 'performed' => 'italic']]],
						 'img'   => ['field' => 'sport', 'map' => Sports::$sportImagesMap, 'imagesDir' => Tfk::publicDir . 'images/'],
						 'ruler' => ['field' => 'stress', 'map' => Sports::$stressOptions, 'atts' => ['minimum' => 0, 'maximum' => 4, 'showButtons' => false, 'discreteValues' => 5]],
					]],
					'onChangeNotify' => [$this->gridWidgetName => [
						'startTime' => 'startdate', 'duration' => 'duration', 'summary' => 'name', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport', 'warmup' => 'warmup',
						'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode', 'gctrimp100' => 'gctrimp100'
					]],
					'onWatchLocalAction' => ['date' => $dateChangeLocalAction(false)]]]], 
				'fromdate', 'todate'),
		    'weeklies' => ViewUtils::JsonGrid($this, 'Weeklies', [
    		        'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
    		        'weekof' => viewUtils::tukosDateBox($this, 'Weekof'),
    		        'athleteweeklyfeeling'    => ViewUtils::textArea($this, 'AthleteWeeklyFeeling'),
    		        'coachweeklycomments'  => ViewUtils::textArea($this, 'CoachWeeklyComments'),
    		    ],
    	        ['atts' => ['edit' => [
    	            'sort' => [['property' => 'weekof', 'descending' => true]], 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'weekof', 'endDateTimeCol' => 'weekof',
    	            'onWatchLocalAction' => ['allowApplicationFilter' => ['weeklies' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')]]
    	        ]]]
	        ),
		    'stsdays' => ViewUtils::tukosNumberBox($this, 'stsdays', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '0.'], 
		        'onChangeLocalAction' => ['stsdays' => ['localActionStatus' => $this->tsbParamsChangeAction('stsdays')]]]]]),
		    'ltsdays' => ViewUtils::tukosNumberBox($this, 'ltsdays', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.'],
		        'onChangeLocalAction' => ['ltsdays' => ['localActionStatus' => $this->tsbParamsChangeAction('ltsdays')]]]]]),
		    'stsratio' => ViewUtils::tukosNumberBox($this, 'stsratio', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '0.00'],
		        'onChangeLocalAction' => ['stsratio' => ['localActionStatus' => $this->tsbParamsChangeAction('stsratio')]]]]]),
		    'initialsts' => ViewUtils::tukosNumberBox($this, 'initialsts', ['atts' => ['edit' => ['style' => ['width' => '5em'],// 'constraints' => ['pattern' => '0.'],
		        'onChangeLocalAction' => ['initialsts' => ['localActionStatus' => $this->tsbParamsChangeAction('initialsts')]]]]]),
		    'initiallts' => ViewUtils::tukosNumberBox($this, 'initiallts', ['atts' => ['edit' => ['style' => ['width' => '5em'],// 'constraints' => ['pattern' => '0.'],
		        'onChangeLocalAction' => ['initiallts' => ['localActionStatus' => $this->tsbParamsChangeAction('initiallts')]]]]]),
		];
	
		$subObjects = [
			'sptsessions' => [
				'atts' => [
				    'title' => $this->tr('Sessions'), /*'storeType' => 'LazyMemoryTreeObjects',  */ 'allDescendants' => true, 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdate',
				        'endDateTimeCol' => 'startdate',
					'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
					'onChangeNotify' => [
						'calendar' => [
							'startdate' => 'startTime',  'duration' => 'duration',  'name' => 'summary', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport', 
						      'warmup' => 'warmup',
							'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode', 'gctrimp100' => 'gctrimp100'
					]],
					'onDropMap' => [
                        'templates' => ['fields' => ['name' => 'name', 'comments' => 'comments', 'startdate' => 'startdate', 'duration' => 'duration', 'intensity' => 'intensity', 'stress' => 'stress', 
                        	'sport' => 'sport', 'warmup' => 'warmup', 'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode', 'gctrimp100' => 'gctrimp100'
                        ]],
						'warmup' => ['mode' => 'update', 'fields' => ['warmup' => 'summary']],
						'mainactivity' => ['mode' => 'update', 'fields' => ['mainactivity' => 'summary']],
						'warmdown' => ['mode' => 'update', 'fields' => ['warmdown' => 'summary']],
					],
					'sort' => [['property' => 'startdate', 'descending' => false]],
					'onWatchLocalAction' => [
					    'allowApplicationFilter' => ['sptsessions' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')],
					    'collection' => [
					        'calendar' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => 'tWidget.currentView.invalidateLayout();return true;']],
					        'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekloadchart'),]],
					        'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekperformedloadchart')]],
					    ]],
				    'renderCallback' => "if (rowData.mode === 'performed'){domstyle.set(node, 'fontStyle', 'italic');}",
				    //'sendOnHidden' => ['athleteweeklyfeeling', 'coachweeklycomments']
				    //'createRowAction' => $this->createRowAction(),
				    //'updateRowAction' => $this->updateRowAction(),
				    'deleteRowAction' => $this->deleteRowAction(),
				    'colsDescription' => [
				        'startdate' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['startdate' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
				        'gctrimp100' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['gctrimp100' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
				        'mode' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['mode' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]]
				    ],
			        'afterActions' => [
				        'createNewRow' => $this->afterCreateRow(),
				        'updateRow' => $this->afterUpdateRow(),
				        'deleteRow' => $this->afterDeleteRow(),
			            'deleteRows' => $this->afterDeleteRows(),
				    ],
				    'beforeActions' => [
				        'deleteRows' => $this->beforeDeleteRows(),
				        'updateRow' => $this->beforeRowChange(),
				        //'deleteRow' => $this->beforeRowChange('delete')
	               ]
				],
				'filters' => ['parentid' => '@id', ['col' => 'startdate', 'opr' => '>=', 'values' => '@fromdate'], 
				    [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
			],

			'templates' => [
				'object' => 'sptsessions',
				'atts' => [
					'title' => $this->tr('sessionstemplates'),/* 'storeType' => 'LazyMemoryTreeObjects', */
					'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false],
				],
				'filters' => ['grade' => 'TEMPLATE'],
				'allDescendants' => true, // 'hasChildrenOnly',
			],

			'warmup' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('warmuptemplates'),/* 'storeType' => 'LazyMemoryTreeObjects', */ 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'warmup'],
				 'allDescendants' => true,
			],
			'mainactivity' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('mainactivitytemplates'),/* 'storeType' => 'LazyMemoryTreeObjects', */  'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'mainactivity'],
				'allDescendants' => 'true'
			],
			'warmdown' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('warmdowntemplates'), /*'storeType' => 'LazyMemoryTreeObjects',*/ 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
				'filters' => ['stagetype' => 'warmdown'],
				 'allDescendants' => true, 
			],
		];
		foreach (array_diff_key($chartsCols, array_flip(['gctrimp100'])) as $col => $description){
		    $subObjects['sptsessions']['atts']['colsDescription'][$col] = ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => [$col => ['localActionStatus'  => $this->cellChartChangeLocalAction()]]]]]];
		}
		$this->customize($customDataWidgets, $subObjects, [ 'grid' => ['calendar', 'displayeddate', 'loadchart', 'weeklies'], 'get' => ['displayeddate', 'loadchart', 'performedloachart', 'weekloadchart', 'weekperformedloadchart'],
		    'post' => ['displayeddate', 'loadchart', 'performedloadchart', 'weekloadchart', 'weekperformedloadchart', 'synchrostart', 'synchroend']], ['weeklies' => []]);
	}
	function OpenEditAction(){
	    return <<<EOT
var form = this;	    
require (["tukos/objects/sports/TsbCalculator", "tukos/objects/sports/LoadChart"], function(TsbCalculator, LoadChart){
    var grid = form.getWidget('sptsessions'), params = {};
    ['stsdays', 'ltsdays', 'stsratio', 'initialsts', 'initiallts'].forEach(function(name){
        params[name] = form.valueOf(name);
    });
    grid.tsbCalculator = new TsbCalculator({sessionsStore: grid.store});
    grid.tsbCalculator.initialize(params);
    grid.loadChartUtils = new LoadChart({sessionsStore: grid.store});
    grid.loadChartUtils.setProgramLoadChartValue(form, 'loadchart');
    grid.loadChartUtils.setProgramLoadChartValue(form, 'performedloadchart');
    grid.loadChartUtils.setWeekLoadChartValue(form, 'weekloadchart');
    grid.loadChartUtils.setWeekLoadChartValue(form, 'weekperformedloadchart');
});
EOT
;
	}
	function beforeRowChange(){
	    return <<<EOT
var idp = this.collection.idProperty, row = this.collection.getSync((args || this.clickedRow.data)[idp]);
this.rowBeforeChange = lang.clone(row);
EOT
	    ;
	}
	function afterCreateRow(){
	    return <<<EOT
var row = arguments[1][0] || this.clickedRow.data;
if (!row.startdate){
    return;
}
if (row.mode === 'performed'){
    this.tsbCalculator.updateRowAction(this, this.store.getSync(row[this.store.idProperty]), true);
}
this.loadChartUtils.updateCharts(this, row.mode);
EOT
	    ;
	}
	function afterUpdateRow(){
	    return <<<EOT
var row = arguments[1][0] || this.clickedRow.data, rowBeforeChange = this.rowBeforeChange, startingRow, isPerformed;
if (rowBeforeChange.mode !== row.mode || rowBeforeChange.startdate !== row.startdate || rowBeforeChange.gctrimp100 !== row.gctrimp100){
    if (row.mode === 'performed' || rowBeforeChange.mode === 'performed'){
        if (rowBeforeChange.startdate !== row.startdate){
            startingRow = false;
        }else{
            startingRow = row;
        }
        isPerformed = row.mode === rowBeforeChange.mode ? 'performed' : 'changed';
    }
}
if (startingRow !== undefined){
    this.tsbCalculator.updateRowAction(this, startingRow ? this.store.getSync(startingRow[this.store.idProperty]) : false, true);
}
this.loadChartUtils.updateCharts(this, isPerformed);
delete this.rowBeforeChange;
EOT
	    ;
	}
	function afterDeleteRow(){
	    return <<<EOT
var row = arguments[1][0] || this.clickedRow.data;
this.loadChartUtils.updateCharts(this, row.mode);
EOT
	    ;
	    
	}
	function beforeDeleteRows(){
	    return <<<EOT

var tsbCalculator = this.tsbCalculator, iterator = tsbCalculator.sessionsIterator, previousItem, hasPerformedDeleted = false, hasPlannedDeleted = false;
this.isBulkRowAction = true;
if (tsbCalculator.isActive()){
    when(tsbCalculator.getCollection().fetchSync(), function(data){
		previousItem = iterator.initialize(data, 'last');
        args.forEach(function(row){
            if (row.gctrimp100){
				while (previousItem !== false && previousItem.startdate >= row.startdate){
					previousItem = iterator.previous();
				}
            }
            if (row.mode === 'performed'){
                hasPerformedDeleted = true;
            }else{
                hasPlannedDeleted = true;
            }
        });
    });
    this.deleteRowsBulkParams = {tsbRow: previousItem, hasPerformedDeleted: hasPerformedDeleted, hasPlannedDeleted: hasPlannedDeleted};
}
EOT
	    ;
	}
	function afterDeleteRows(){
	    return <<<EOT
if (this.deleteRowsBulkParams){
    var params = this.deleteRowsBulkParams;
    if (params.tsbRow !== undefined){
        this.tsbCalculator.updateRowAction(this, params.tsbRow, true);
    }
    if (params.hasPlannedDeleted){
        this.loadChartUtils.updateCharts(this, false);
    }
    if (params.hasPerformedDeleted){
        this.loadChartUtils.updateCharts(this, true);
    }
    delete this.deleteRowsBulkParams;
}
this.isBulkRowAction = false;
EOT
	    ;
	}
	function tsbChangeLocalAction(){
	    return <<<EOT
if (tWidget.column){
    var grid = tWidget.column.grid, form = grid.form, col = tWidget.column.field, rowIdp = tWidget.row.data[grid.store.idProperty], row = grid.store.getSync(rowIdp);
    if ((col === 'mode' && oldValue === 'performed') || (col === 'startdate' && newValue === '')){
        grid.updateDirty(rowIdp, 'sts', '');
        grid.updateDirty(rowIdp, 'lts', '');
        grid.updateDirty(rowIdp, 'tsb', '');
    }
    if (row.mode === 'performed'){
        grid.tsbCalculator.updateRowAction(grid, (col === 'gctrimp100' || (col === 'mode' && newValue === 'performed')) ? row : false, true);
    }
    grid.loadChartUtils.setProgramLoadChartValue(form, 'performedloadchart');
    grid.loadChartUtils.setWeekLoadChartValue(form, 'weekperformedloadchart');
}
return true;
EOT
	    ;
	}
	function cellChartChangeLocalAction(){
	    return <<<EOT
if (tWidget.column){
    var grid = tWidget.column.grid;
    grid.loadChartUtils.updateChartsLocalAction(sWidget, tWidget);
}
return true;
EOT
	    ;
	}
	function cellModeChangeLocalAction(){
	    return <<<EOT
if (tWidget.column){
    var grid = tWidget.column.grid;
    grid.loadChartUtils.updateCharts(grid, 'changed');
}
return true;
EOT
	    ;
	}
	function tsbParamsChangeAction($param){
	    return <<<EOT
var form = sWidget.form, grid = form.getWidget('sptsessions'); 
grid.tsbCalculator.initialize(utils.newObj([['$param', newValue]]));
grid.tsbCalculator.updateRowAction(grid, false, true);
grid.loadChartUtils.setProgramLoadChartValue(form, 'performedloadchart');
grid.loadChartUtils.setWeekLoadChartValue(form, 'weekperformedloadchart');
grid.refresh({skipScrollPosition: true});
return true;
EOT
	    ;
	}
	function hiddenLoadChartAction($chartName){
	    return <<<EOT
var form = sWidget.form, grid = form.getWidget('sptsessions');
form.resize();
if (!newValue){
    if ('$chartName' === 'loadchart' || '$chartName' === 'performedloadchart'){
        grid.loadChartUtils.setProgramLoadChartValue(form, '$chartName');
    }else{
        grid.loadChartUtils.setWeekLoadChartValue(form, '$chartName');
    }
}
return true;
EOT
	    ;
	}
	function createRowAction(){
	    return <<<EOT
this.tsbCalculator.createRowAction(this, row);
EOT
	    ;
	}
	function updateRowAction(){
	    return <<<EOT
this.tsbCalculator.updateRowAction(this, row);
EOT
	    ;
	}
	function deleteRowAction(){
	    return <<<EOT
this.tsbCalculator.deleteRowAction(this, row);
EOT
	    ;
	}
}
?>
