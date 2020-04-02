<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Collab\Calendars\CalendarsViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

	use CalendarsViewUtils, ViewActionStrings;
	
	function __construct($objectName, $translator=null){
	    $stressOptionsString = "['" . implode("','", Sports::$stressOptions) . "']";
	    parent::__construct($objectName, $translator, 'Sportsman', 'Title');
		$this->doNotEmpty = ['displayeddate', 'synchrostart', 'synchroend'];
		
		$this->setGridWidget('sptsessions');
		$chartCols = ['duration' => 'lines', 'distance' => 'lines', 'elevationgain' => 'lines', 'load' => 'cluster', 'intensity' => 'cluster', 'stress' => 'cluster', 'perceivedload' => 'cluster', 'perceivedeffort' => 'cluster', 
		    'sensations' => 'cluster', 'mood' => 'cluster', 'fatigue' => 'cluster'];
		$chartColsTLabel = [];
		foreach($chartCols as $col => $plot){
		    $chartColsTLabel[$col] = $this->tr($col);
		}
		$chartColsTLabelString = json_encode($chartColsTLabel);
		$chartColsLegendUnit = ['duration' => '(mn)', 'distance' => '(km)', 'elevationgain' => '(dam)'];
		$plannedPresentCols = ['duration', 'distance', 'elevationgain', 'intensity', 'load', 'stress'];
		$performedPresentCols = ['duration', 'distance', 'elevationgain', 'perceivedeffort', 'perceivedload', 'sensations', 'mood', 'fatigue'];
		$tDuration = $this->tr('duration'); $tStress = $this->tr('stress'); $tLoad = $this->tr('load'); $tIntensity = $this->tr('intensity'); $tWeekOfTheYear = $this->tr('weekoftheyear'); $tDistance = $this->tr('distance');
		$tElevationGain = $this->tr('elevationgain'); $tPerceivedEffort = $this->tr('perceivedeffort'); $tPerceivedLoad = $this->tr('perceivedload'); $tSensations = $this->tr('sensations'); $tMood = $this->tr('mood'); 
		$tFatigue = $this->tr('fatigue'); $tDayOfWeek = $this->tr('dayofweek'); $tDateOfDay = $this->tr('dateofday');
		$qtPerceivedEffort = $this->tr('perceivedeffort', 'escapeSQuote');
		$tDaysOfWeek = [];
		foreach(Dutl::daysOfWeek as $day){
		    $tDaysOfWeek[] = $this->tr($day);
		}
		$tDaysOfWeek = json_encode($tDaysOfWeek);
        $weekLoadChartLocalAction = function($plannedOrPerformed, $presentCols) use ($tDaysOfWeek, $tDayOfWeek, $tDateOfDay, $chartColsTLabelString){
            return $this->weekLoadChartLocalAction($plannedOrPerformed, $presentCols, $tDaysOfWeek, $tDayOfWeek, $tDateOfDay, $chartColsTLabelString);
        };
        $dateChangeLocalAction = function($serverTrigger) use ($weekLoadChartLocalAction, $plannedPresentCols,$performedPresentCols){
            return [
                'loadchart' => ['localActionStatus' => ['triggers' => ['server' => $serverTrigger, 'user' => true], 'action' => $this->loadChartLocalAction()]],
                'performedloadchart' => ['localActionStatus' => ['triggers' => ['server' => $serverTrigger, 'user' => true], 'action' => $this->loadChartLocalAction()]],
                'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalAction('planned', $plannedPresentCols),]],
                'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalAction('performed', $performedPresentCols),]],
                'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $this->synchroStartLocalAction('newValue', '#synchnextmonday'),]],
                'weeklies' => $this->dateChangeGridLocalAction('newValue', 'tWidget', 'tWidget.allowApplicationFilter')
            ];
        };
        $loadChartCustomization = function($idProperty, $idPropertyStoreData){
		    $idPropertyType = $idProperty.'type';
		    return [
		    //'chartHeight' => ['att' => 'chartHeight', 'type' => 'NumberUnitBox', 'name' => $this->tr('chartHeight'),
		      //  'units' => [['id' => '', 'name' => ''], ['id' => 'auto', 'name' => 'auto'], ['id' => '%', 'name' => '%'], ['id' => 'em', 'name' => 'em'], ['id' => 'px', 'name' => 'px']]],
		    $idPropertyType => ['att' =>  $idPropertyType, 'type' => 'StoreSelect', 'name' => $this->tr($idPropertyType),
		            'storeArgs' => ['data' => $idPropertyStoreData]],
		    //'showTable' => ['att' =>  'showTable', 'type' => 'StoreSelect', 'name' => $this->tr('showTable'),
		        //'storeArgs' => ['data' => Utl::idsNamesStore(['yes', 'no'], $this->tr)]],
		    //'tableWidth' => ['att' => 'tableWidth', 'type' => 'NumberUnitBox', 'name' => $this->tr('tableWidth'),
		        //'units' => [['id' => '', 'name' => ''], ['id' => 'auto', 'name' => 'auto'], ['id' => '%', 'name' => '%'], ['id' => 'em', 'name' => 'em'], ['id' => 'px', 'name' => 'px']]]
		];};

		$programLoadChartIdPropertyStoreData = [['id' => 'weekoftheyear', 'name' => $tWeekOfTheYear], ['id' =>  'weekofprogram', 'name' =>  $this->tr('weekofprogram')]];

		$weekLoadChartIdPropertyStoreData = [['id' => 'dayofweek', 'name' => $tDayOfWeek], ['id' =>  'dateofday', 'name' =>  $tDateOfDay]];

		$loadChartDescription = function($chartName, $idProperty, $idPropertyType, $sortAttribute, $xTitle, $presentCols, $idPropertyTypeLocalActionString, $customizableAtts) use ($chartCols, $chartColsTLabel, $chartColsLegendUnit){
		    $tableAttsColumns = [$idProperty => ['label' => $this->tr($idProperty), 'field' => $idProperty, 'width' => 65]]; $series = []; $linesAxisLabel = ''; $clusterAxisLabel = '';
		    foreach($presentCols as $col){
		        $plot = $chartCols[$col];
		        $colLabel = $chartColsTLabel[$col];
		        $tableAttsColumns[$col] = ['label' => $colLabel . ' ' . Utl::getItem($col, $chartColsLegendUnit, ''), 'field' => $col, 'width' => 60];
		        $series[$col] = ['value' => ['y' => $col, 'text' => $idProperty, 'tooltip' => $col . 'Tooltip'], 'options' => ['plot' => $plot, 'label' => $colLabel, 'legend' => $colLabel]];
		        if ($plot === 'lines'){
		            $linesAxisLabel .= $colLabel . Utl::getItem($col, $chartColsLegendUnit, '') . ' ';
		        }else{
		            $clusterAxisLabel .= $colLabel . Utl::getItem($col, $chartColsLegendUnit, '') . ' ';
		        }
		    }
		    return ['type' => 'chart', 'atts' => ['edit' => [
		        'title' => $this->tr($chartName), 'idProperty' => $idProperty, 'kwArgs'	 => ['sort'=> [['attribute' => $sortAttribute, 'descending' => false]]],
		        'style' => ['width' => '700px'],
		        'chartHeight' => '300px',
		        'showTable' => 'no',
		        'tableAtts' => ['columns' => $tableAttsColumns],
		        ($idProperty.'type') => $idPropertyType,
		        'axes' =>  [
		            'x'   => ['title' => $xTitle, 'titleOrientation' => 'away', 'titleGap' => 5, 'labelCol' => $idProperty, 'majorTicks' => true, 'majorTickStep' => 1, 'minorTicks' => false, 'titleFont' => 'normal normal normal 11pt Arial'],
		            'y1' => ['title' => $clusterAxisLabel, 'vertical' => true, 'min' => 0, 'max' => 10, 'titleFont' => 'normal normal normal 8pt Arial'],
		            'y2' => ['title' => $linesAxisLabel, 'vertical' => true, 'leftBottom' => false, 'min' => 0, 'titleFont' => 'normal normal normal 8pt Arial'],
		        ],
		        'plots' =>  [
		            'lines' => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y2', 'lines' => true, 'markers' => true, 'tension' => 'X', 'shadow' => ['dx' => 1, 'dy' => 1, 'width' => 2]],
		            'cluster' => ['plotType' => 'ClusteredColumns', 'vAxis' => 'y1', 'gap' => 3],
		            $idProperty	  => ['plotType' => 'Indicator', 'hAxis' => 'x', 'vAxis' => 'y2', 'stroke' => null, 'outline' => null, 'fill' => null, 'labels' => false, 'lineStroke' => ['color' => 'red', 'style' => 'shortDash', 'width' => 2]],
		        ],
		        'legend' => ['type' => 'SelectableLegend', 'options' => []],
		        'series' => $series,
		        'onWatchLocalAction' => [$idProperty.'type' => [
		            $chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true],
		                'action' => $idPropertyTypeLocalActionString]]
		        ]],
		        'customizableAtts' => $customizableAtts
		    ]]];
		};
		$customDataWidgets = [
			'comments' => ['atts' => ['edit' => ['height' => '200px']]],
		    'fromdate' => ViewUtils::tukosDateBox($this, 'Begins on', ['atts' => ['edit' => [
							'onChangeLocalAction' => [
								'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(newValue, sWidget.valueOf('#duration'), sWidget.valueOf('#todate'),true)}" ]
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
								'duration'  => ['value' => "if (!newValue){return '';}else{return dutils.durationString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#duration'),true)}" ]
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
		    'loadchart' => $loadChartDescription('loadchart', 'week', 'weekoftheyear', 'weekof', $tWeekOfTheYear, $plannedPresentCols,
		        "Pmg.setFeedback('savecustomforeffect', null, null, true); return true;", $loadChartCustomization('week', $programLoadChartIdPropertyStoreData)),
		    'weekloadchart' => $loadChartDescription('weekloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfWeek, $plannedPresentCols,
		        $weekLoadChartLocalAction('planned', $plannedPresentCols), $loadChartCustomization('day', $weekLoadChartIdPropertyStoreData)),
		    'performedloadchart' => $loadChartDescription('performedloadchart', 'week', 'weekoftheyear', 'weekof', $tWeekOfTheYear, $performedPresentCols,
		        "Pmg.setFeedback('savecustomforeffect', null, null, true); return true;", $loadChartCustomization('week', $programLoadChartIdPropertyStoreData)),
		    'weekperformedloadchart' => $loadChartDescription('weekperformedloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfWeek, $performedPresentCols,
		        $weekLoadChartLocalAction('performed', $performedPresentCols), $loadChartCustomization('day', $weekLoadChartIdPropertyStoreData)),
		    'worksheet' => ['atts' => ['edit' => ['dndParams' => ['accept' => ['dgrid-row', 'quarterhour']]/*, 'copyOnly' => true, 'selfAccept' => false*/]]],
			'calendar' => $this->calendarWidgetDescription([
				'type' => 'StoreSimpleCalendar', 
			    'atts' => ['edit' => [
					'columnViewProps' => ['minHours' => 0, 'maxHours' => 4],
					'style' => ['height' => '350px', 'width' => '700px'], 
					'timeMode' => 'duration', 'durationFormat' => 'time', 'moveEnabled' => true,
					'customization' => ['items' => [
						 'style' => ['backgroundColor' => ['field' => 'intensity', 'map' => Sports::$intensityColorsMap, 'defaultValue' => 'Peru'], 'color' => ['field' => 'mode', 'map' => ['planned' => 'white', 'performed' => 'black']],
						  'fontStyle' => ['field' => 'mode', 'map' => ['planned' => 'normal', 'performed' => 'italic']]],
						 'img'   => ['field' => 'sport', 'map' => Sports::$sportImagesMap, 'imagesDir' => Tfk::publicDir . 'images/'],
						 'ruler' => ['field' => 'stress', 'map' => Sports::$stressOptions, 'atts' => ['minimum' => 0, 'maximum' => 4, 'showButtons' => false, 'discreteValues' => 5]],
					]],
					'onChangeNotify' => [$this->gridWidgetName => [
						'startTime' => 'startdate', 'duration' => 'duration', 'summary' => 'name', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport', 'warmup' => 'warmup', 
						'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode'
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
		];
	
		$subObjects = [
			'sptsessions' => [
				'atts' => [
				    'title' => $this->tr('Sessions'), /*'storeType' => 'LazyMemoryTreeObjects',  */ 'allDescendants' => true, 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdate', 'endDateTimeCol' => 'startdate',
					'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
					'onChangeNotify' => [
						'calendar' => [
							'startdate' => 'startTime',  'duration' => 'duration',  'name' => 'summary', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport', 'warmup' => 'warmup',
							'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode'
					]],
					'onDropMap' => [
                        'templates' => ['fields' => ['name' => 'name', 'comments' => 'comments', 'startdate' => 'startdate', 'duration' => 'duration', 'intensity' => 'intensity', 'stress' => 'stress', 
                        	'sport' => 'sport', 'warmup' => 'warmup', 'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode'
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
					        'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalAction('planned', $plannedPresentCols),]],
					        'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalAction('performed', $performedPresentCols),]],
					    ]],
				    'renderCallback' => "if (rowData.mode === 'performed'){domstyle.set(node, 'fontStyle', 'italic');}",
				    //'sendOnHidden' => ['athleteweeklyfeeling', 'coachweeklycomments']
				],
				'filters' => ['parentid' => '@id', ['col' => 'startdate', 'opr' => '>=', 'values' => '@fromdate'], [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
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
		$this->customize($customDataWidgets, $subObjects, [ 'grid' => ['calendar', 'displayeddate', 'loadchart', 'weeklies'], 'get' => ['displayeddate', 'weekloadchart', 'weekperformedloadchart'],
		    'post' => ['displayeddate', 'weekloadchart', 'weekperformedloadchart', 'synchrostart', 'synchroend']], ['weeklies' => []]);
	}
}
?>
