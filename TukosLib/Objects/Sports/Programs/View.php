<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Sports\Sessions\Views\Edit\View as SessionsEditView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Collab\Calendars\CalendarsViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;


class View extends AbstractView {

	use CalendarsViewUtils, ViewActionStrings, SpiderView;
	
	function __construct($objectName, $translator=null){
	    parent::__construct($objectName, $translator, 'Sportsman', 'Title');
		$this->doNotEmpty = ['displayeddate', 'synchrostart', 'synchroend'];
		$tr = $this->tr;
		$this->setGridWidget('sptsessions');
		$this->allowedNestedWatchActions = 1;
		$this->allowedNestedRowWatchActions = 0;
		$chartsCols = [];
		foreach(['duration', 'distance', 'equivalentDistance', 'elevationgain', 'sts', 'lts', 'tsb', 'hracwr'] as $col){
		    $chartsCols[$col] = ['plot' => 'lines', 'tCol' => $tr($col)];
		}
		foreach(['intensity' => 'plannedintensity', 'load' => 'plannedload', 'stress' => 'plannedqsm', 'trimphr' => 'trimphr', 'trimppw' => 'trimppw', 'mechload' => 'computedmechload', 'perceivedeffort' => 'perceivedintensity', 'perceivedload' => 'perceivedload',
		    'perceivedmechload' => 'perceivedstress', 'sensations' => 'sensations', 'mood' => 'mood', 'fatigue' => 'fatigue'] as $col => $colLabel){
		    $chartsCols[$col] = ['plot' => 'cluster', 'tCol' => $tr($colLabel)];
		}
		foreach(['duration' => '(mn)', 'distance' => '(km)', 'equivalentDistance' => '(km)', 'elevationgain' => '(dam)'] as $col => $legendUnit){
		    $chartsCols[$col]['legendUnit'] = $legendUnit;
		}
		foreach(['distance' => ' km', 'equivalentDistance' => 'km', 'elevationgain' => ' m'] as $col => $tooltipUnit){
		    $chartsCols[$col]['tooltipUnit'] = $tooltipUnit;
		}
		$chartsCols['elevationgain']['tooltipUnit'] = ' (m)';
		foreach(['elevationgain' => ['day' => 10, 'week' => 10], 'load' => ['week' => 10, 'day' => 10], 'perceivedload' => ['week' => 10, 'day' => 10], 'trimphr' => ['day' => 25, 'week' => 10], 'trimppw' => ['day' => 25, 'week' => 10], 'mechload' => ['day' => 10, 'week' => 50], 
		    'sts' => ['day' => 1, 'week' => 0.1], 'lts' => ['day' => 1, 'week' => 0.1], 'tsb' => ['day' => 1, 'week' => 0.1], 'hracwr' => ['day' => 0.1, 'week' => 0.01]] as $col => $scalingFactor){
		    $chartsCols[$col]['scalingFactor'] = $scalingFactor;
		}
		foreach(['load' => ['day' => 1, 'week' => 7], 'perceivedload' => ['day' => 120, 'week' => 600], 'trimphr' => ['day' => 1, 'week' => 7], 'trimppw' => ['day' => 1, 'week' => 7], 'mechload' => ['day' => 1, 'week' => 7],
		    'perceivedmechload' => ['day' => 1, 'week' => 7]] as $col => $normalizationFactor){
		    $chartsCols[$col]['normalizationFactor'] = $normalizationFactor;
		}
		foreach(['intensity', 'perceivedeffort', 'sensations', 'mood', 'fatigue'] as $col){
		    $chartsCols[$col]['isDurationAverage'] = true;
		}
		$this->chartsCols = $chartsCols;
		$chartTypes = [
		    'program' => ['idp' => 'week', 'defaultidptype' => 'weekoftheyear', 'idptypes' => [['id' => 'weekoftheyear', 'name' => $tr('weekoftheyear')], ['id' =>  'weekofprogram', 'name' =>  $tr('weekofprogram')]],
		          'sortAttribute' => 'weekof'
		    ],
		    'weekly' => ['idp' => 'day', 'defaultidptype' => 'dateofday', 'idptypes' => [['id' => 'dayofweek', 'name' => $tr('dayofweek')], ['id' =>  'dateofday', 'name' =>  $tr('dateofday')]], 
		        'sortAttribute' => 'dayofweek']
		];
		$chartFilter = ['planned' => 'ne', 'performed' => 'eq'];
		$chartCols = [
		    'planned' => array_intersect_key($chartsCols, array_flip(['duration', 'distance', 'equivalentDistance', 'elevationgain', 'intensity', 'load', 'stress'])),
		    'performed' => array_intersect_key($chartsCols, array_flip(['duration', 'distance', 'equivalentDistance',  'elevationgain', 'trimphr', 'trimppw', 'mechload', 'sts', 'lts', 'tsb', 'hracwr', 'perceivedeffort', 'perceivedload', 'perceivedmechload', 'sensations', 'mood', 'fatigue']))
		];
		$summaryRow = ['cols' => [
		    'day' => ['content' =>  ['Total']],
		    'duration' => ['atts' => ['formatType' => 'minutesToHHMM'], 'content' => [['rhs' => "var duration = #duration#.split(':'); return res + duration[0]*60 + Number(duration[1]);"]]],
		    'distance' => ['content' => [['rhs' => "return res + Number(#distance#);"]]],
		    'equivalentDistance' => ['content' => [['rhs' => "return res + Number(#equivalentDistance#);"]]],
		    'elevationgain' => ['content' => [['rhs' => "return res + Number(#elevationgain#);"]]],
		    'trimphr' => ['content' => [['rhs' => "return res + Number(#trimphr#);"]]],
		    'trimppw' => ['content' => [['rhs' => "return res + (Number(#trimppw#) || Number(#trimphr#));"]]],
		    'mechload' => ['content' => [['rhs' => "return res + Number(#mechload#);"]]]
		]];
		$chartsAtts = [
		    'loadchart' => ['name' => 'loadchart', 'type' => $chartTypes['program'], 'filter' => $chartFilter['planned'], 'cols' => $chartCols['planned']],
		    'performedloadchart' => ['name' => 'performedloadchart', 'type' => $chartTypes['program'], 'filter' => $chartFilter['performed'], 'cols' => $chartCols['performed']],
		    'weekloadchart' => ['name' => 'weekloadchart', 'type' => $chartTypes['weekly'], 'filter' => $chartFilter['planned'], 'cols' => $chartCols['planned']],
		    'weekperformedloadchart' => ['name' => 'weekperformedloadchart', 'type' => $chartTypes['weekly'], 'filter' => $chartFilter['performed'], 'cols' => $chartCols['performed']]
		];
		$this->addToTranslate(['w', 'dateofday', 'dayofweek', 'weekoftheyear', 'weekofprogram', 'weekendingon', 'newevent', 'sportsmanhasnouserassociatednoacl']);
        $dateChangeLocalAction = function($serverTrigger) use ($chartsAtts){
            return [
                'loadchart' => ['localActionStatus' => ['triggers' => ['server' => $serverTrigger, 'user' => true], 'action' => $this->dateChangeLoadChartLocalAction()]],
                'performedloadchart' => ['localActionStatus' => ['triggers' => ['server' => $serverTrigger, 'user' => true], 'action' => $this->dateChangeLoadChartLocalAction()]],
                'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekloadchart')]],
                'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekperformedloadchart')]],
                'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $this->synchroStartLocalAction('newValue', '#synchnextmonday'),]],
                'weeklies' => $this->dateChangeGridLocalAction('newValue', 'tWidget', 'tWidget.allowApplicationFilter'),
                'displayeddate' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.form.kpiChartUtils && sWidget.form.kpiChartUtils.setDisplayedDateChartsValue();"]]
            ];
        };
        $loadChartCustomization = function($idProperty, $idPropertyStoreData, $colsToExcludeOptions) use ($tr) {
		    $idPropertyType = $idProperty.'type'; $options = [];
		    return [
		      $idPropertyType => ['att' =>  $idPropertyType, 'type' => 'StoreSelect', 'name' => $this->tr($idPropertyType), 'storeArgs' => ['data' => $idPropertyStoreData]],
		        'colsToExclude' => ['att' => 'colsToExclude', 'type' => 'MultiSelect', 'name' => $this->tr('colsToExclude'), 'options' => $colsToExcludeOptions],
		        'y1custommin' => ['att' => 'y1custommin', 'type' => 'TextBox', 'name' => $this->tr('y1custommin')],
		        'y1custommax' => ['att' => 'y1custommax', 'type' => 'TextBox', 'name' => $this->tr('y1custommax')],
		        'y2custommin' => ['att' => 'y2custommin', 'type' => 'TextBox', 'name' => $this->tr('y2custommin')],
		        'y2custommax' => ['att' => 'y2custommax', 'type' => 'TextBox', 'name' => $this->tr('y2custommax')],
		        'applyscalingfactor' => ['att' => 'applyscalingfactor', 'type' => 'StoreSelect', 'name' => $this->tr('applyscalingfactor'), 'storeArgs' => ['data' => Utl::idsNamesStore(['YES', 'NO'], $tr)]]
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
		        $tableAttsColumns[$col] = ['label' => $colLabel . ' ' . Utl::getItem('tooltipUnit', $atts, '') , 'field' => $col, 'width' => 60];
		        $series[$col] = ['value' => ['y' => $col, 'text' => $idProperty, 'tooltip' => $col . 'Tooltip'], 'options' => ['plot' => $plot, 'label' => $colLabel, 'legend' => $colLabel]];
		        if ($plot === 'lines'){
		            //$linesAxisLabel .= $colLabel /*. $legendLabel*/ . ' ';
		        }else{
		            //$clusterAxisLabel .= $colLabel /*. $legendLabel*/ . ' ';
		        }
		        $colsToExcludeOptions[$col] = ['option' => isset($this->chartsCols[$col]) ? $this->chartsCols[$col]['tCol'] : $tr($col), 'tooltip' => $tr($col . 'Tooltip')];
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
		            'lines' => ['type' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y2', 'lines' => true, 'markers' => true, 'tension' => 'X', 'shadow' => ['dx' => 1, 'dy' => 1, 'width' => 2]],
		            'cluster' => ['type' => 'ClusteredColumns', 'vAxis' => 'y1', 'gap' => 3],
		            $idProperty	  => ['type' => 'Indicator', 'hAxis' => 'x', 'vAxis' => 'y2', 'stroke' => null, 'outline' => null, 'fill' => null, 'labels' => false, 
		                'lineStroke' => ['color' => 'red', 'style' => 'shortDash', 'width' => 2]],
		        ],
		        'legend' => ['type' => 'SelectableLegend', 'options' => []],
		        'series' => $series,
		        'tooltip' => true,
		        'mouseZoomAndPan' => true,
		        'onWatchLocalAction' => [
		            $idProperty.'type' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $idpTypeLocalActionString]]],
		            'colsToExclude' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.set('value', sWidget.get('value')); return true;"]]],
		            'y1custommin' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.set('value', sWidget.get('value')); return true;"]]],
		            'y1custommax' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.set('value', sWidget.get('value')); return true;"]]],
		            'y2custommin' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.set('value', sWidget.get('value')); return true;"]]],
		            'y2custommax' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => "sWidget.set('value', sWidget.get('value')); return true;"]]],
		            'applyscalingfactor' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->loadChartChangeAction($chartName, 'applyscalingfactor')]]],
		            'hidden' => [$chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->loadChartChangeAction($chartName, 'hidden')]]]
		        ],
		        'customizableAtts' => $loadChartCustomization($idProperty, $chartAtts['type']['idptypes'], $colsToExcludeOptions)
		    ]]];
		};
		$customDataWidgets = [
		    'parentid' => ['atts' => ['edit' => ['storeArgs' => ['cols' => ['email']], 'onChangeLocalAction' => ['sportsmanemail' => ['value' => "return sWidget.getItemProperty('email');"], 'parentid' => ['localActionStatus' => $this->programAclLocalAction()]]]]],
		    'comments' => ['atts' => ['edit' => ['style' => ['height' => '300px']]]],
		    'coach' => ViewUtils::objectSelect($this, 'Coach', 'people', ['atts' => [
		        'edit' => ['storeArgs' => ['cols' => ['email', 'parentid']]/*, 'onChangeLocalAction' => ['coachemail' => ['value' => "return sWidget.getItemProperty('email');"]]*/,'onWatchLocalAction' => ['value' => [
		            'coachorganization' => ['value' => ['triggers' => ['server' => true, 'user' => true], 'action' => "return sWidget.getItemProperty('parentid');"]],
		            'coachemail' => ['value' => ['triggers' => ['server' => false, 'user' => 'true'], 'action' => "return sWidget.getItemProperty('email');"]],
		            'coach' => ['localActionStatus' => $this->programAclLocalAction()]
		        ]]],
		        'overview' => ['hidden' => true]
		    ]]),
	        'coachorganization' => ViewUtils::objectSelect($this, 'CoachOrganization', 'organizations', ['atts' => ['edit' => ['hidden' => true], 'overview' => ['hidden' => true]]]),
		    'fromdate' => ViewUtils::tukosDateBox($this, 'Begins on', ['atts' => ['edit' => [
							'onChangeLocalAction' => [
								'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(newValue, sWidget.valueOf('#duration'), sWidget.valueOf('#todate'),true)}" ],
							    'displayfromdate' => ['value' => "return sWidget.valueOf('fromdate');"],
							    'loadchart' => ['localActionStatus' => $this->loadChartLocalAction('loadchart')],
							    'performedloadchart' => ['localActionStatus' => $this->loadChartLocalAction('performedloadchart')],
							]]]]),
			'duration'  =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration', ['atts' => [
					'edit' => [
							'onChangeLocalAction' => [
								'todate'  => ['value' => "if (!newValue){return '';}else{return dutils.dateString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#todate'),true)}" ]
							],
							'unit' => ['style' => ['width' => '6em']/*, 'onWatchLocalAction' => ['value' => "widget.numberField.set('value', parseInt(dutils.convert(widget.numberField.get('value'), oldValue, newValue)));"]*/],
					],
					'storeedit' => ['formatType' => 'numberunit'],
					'overview' => ['formatType' => 'numberunit', 'hidden' => true],
			]]),
			'todate'   => ViewUtils::tukosDateBox($this, 'Ends on', ['atts' => ['edit' => [
							'onChangeLocalAction' => [
								'duration'  => ['value' => "if (!newValue){return '';}else{return dutils.durationString(sWidget.valueOf('#fromdate'), newValue, sWidget.valueOf('#duration'),false)}" ],
							    'loadchart' => ['localActionStatus' => $this->loadChartLocalAction('loadchart')],
							    'performedloadchart' => ['localActionStatus' => $this->loadChartLocalAction('performedloadchart')],
							],
						]
					]
				]
			),
			'displayeddate' => $this->displayedDateDescription(['atts' => ['edit' => ['onWatchLocalAction' => ['value' => $dateChangeLocalAction(true)]]]]),
            'googlecalid' => ViewUtils::textBox($this, 'Googlecalid', ['atts' => ['edit' => ['disabled' => true]]]),
		    'sportsmanemail' => ViewUtils::textBox($this, 'SportsmanEmail', ['atts' => ['edit' => [/*'disabled' => true, */'hidden' => true], 'overview' => ['hidden' => true]]]),
		    'coachemail' => ViewUtils::textBox($this, 'CoachEmail', ['atts' => ['edit' => [/*'disabled' => true, */'hidden' => true], 'overview' => ['hidden' => true]]]),
		    'lastsynctime' => ViewUtils::timeStampDataWidget($this, 'Lastsynctime', ['atts' => ['edit' => ['disabled' => true]]]),
			'synchrostart' => ViewUtils::tukosDateBox($this, 'Synchrostart', ['atts' => [
			    'edit' => ['onWatchLocalAction' => ['value' => ['synchroweeksbefore' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]]]]],
			]]),
			'synchroend' => ViewUtils::tukosDateBox($this, 'Synchroend', ['atts' => [
			    'edit' => ['onWatchLocalAction' => ['value' => ['synchroweeksafter' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]]]]],
			]]),
			'synchroweeksbefore' => ViewUtils::tukosNumberBox($this, 'Synchroweeksbefore', ['atts' => [
			    'edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => ['synchrostart' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => 
                        "return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date(sWidget.valueOf('#displayeddate'))), 'week', -newValue));" ]]]]],
			    'overview' => ['hidden' => true]
			]]),
			'synchroweeksafter' => ViewUtils::tukosNumberBox($this, 'Synchroweeksafter', ['atts' => [
			    'edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => ['synchroend' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => 
                        	"var nextMonday = sWidget.valueOf('#synchnextmonday') === 'YES';" . 
                        	"return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(nextMonday ? 1 : 7, new Date(sWidget.valueOf('#displayeddate'))), 'week', nextMonday ? newValue + 1 : newValue));" 
                        ]]]]],
			    'overview' => ['hidden' => true]
			]]),
			'synchnextmonday' => viewUtils::storeSelect('synchnextmonday', $this, 'Synchnextmonday', null, ['atts' => [
			    'edit' => ['style' => ['width' => '4em'], 'onWatchLocalAction' => ['value' => ['synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $this->synchroStartLocalAction('#displayeddate', 'newValue')]]]]],
			    'overview' => ['hidden' => true]
			]]),
		    'synchrosource' => ViewUtils::textBox($this, 'Synchrosource', ['atts' => ['edit' => ['hidden' => true]]]),
			'questionnairetime'  =>  ViewUtils::timeStampDataWidget($this, 'QuestionnaireTime', ['atts' => ['edit' => ['disabled' => true]]]),
		    'loadchart' => $loadChartDescription($chartsAtts['loadchart'],$this->loadChartLocalAction('loadchart')),
		    'weekloadchart' => $loadChartDescription($chartsAtts['weekloadchart'], $this->weekLoadChartLocalAction('weekloadchart')),
		    'performedloadchart' => $loadChartDescription($chartsAtts['performedloadchart'],$this->loadChartLocalAction('performedloadchart')),
		    'weekperformedloadchart' => $loadChartDescription($chartsAtts['weekperformedloadchart'], $this->weekLoadChartLocalAction('weekperformedloadchart')),
			'calendar' => $this->calendarWidgetDescription([
				'type' => 'StoreSimpleCalendar', 
			    'atts' => ['edit' => [
			        //'date' => date('Y-m-d', strtotime('next monday')),
			        'columnViewProps' => ['minHours' => 0, 'maxHours' => 4],
					'style' => ['height' => '300px', 'minWidth' => '600px'], 
					'timeMode' => 'duration', 'durationFormat' => 'time', 'moveEnabled' => true,
					'customization' => ['items' => [
						 'style' => ['backgroundColor' => ['field' => 'intensity', 'map' => Sports::$intensityColorsMap, 'defaultValue' => 'Peru'], 
						     'color' => ['field' => 'mode', 'map' => ['planned' => 'white', 'performed' => 'black']],
						 'fontStyle' => ['field' => 'mode', 'map' => ['planned' => 'normal', 'performed' => 'italic']]],
						 'img'   => ['field' => 'sport', 'map' => Sports::$sportImagesMap, 'imagesDir' => Tfk::$registry->rootUrl . Tfk::$publicDir . 'images/'],
						 'ruler' => ['field' => 'stress', 'map' => Sports::$stressOptions, 'atts' => ['minimum' => 0, 'maximum' => 4, 'showButtons' => false, 'discreteValues' => 5]],
					]],
					'onChangeNotify' => [$this->gridWidgetName => [
						'startTime' => 'startdate', 'duration' => 'duration', 'summary' => 'name', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport'/*, 'warmup' => 'warmup',
					    'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown'*/, 'mode' => 'mode'/*, 'trimphr' => 'trimphr', 'trimppw' => 'trimppw'*/
					]],
					'onWatchLocalAction' => ['date' => $dateChangeLocalAction(false)]]]], 
				'fromdate', 'todate'),
		    'weeklies' => ViewUtils::JsonGrid($this, 'Weeklies', [
    		        'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
    		        'weekof' => viewUtils::tukosDateBox($this, 'Weekof'),
    		        'athleteweeklyfeeling'    => ViewUtils::textArea($this, 'AthleteWeeklyFeeling'),
    		        'coachweeklycomments'  => ViewUtils::lazyEditor($this, 'CoachWeeklyComments'),
    		    ],
    	        ['atts' => ['edit' => [
    	            'sort' => [['property' => 'weekof', 'descending' => true]], 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'weekof', 'endDateTimeCol' => 'weekof',
    	            'onWatchLocalAction' => ['allowApplicationFilter' => ['weeklies' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')]],
    	            'beforeActions' => ['createNewRow' => $this->weekliesBeforeCreateNewRow()]
    	        ]]]
	        ),
		    'stsdays' => ViewUtils::tukosNumberBox($this, 'stsdays', ['atts' => [
		        'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '0.'], 'onChangeLocalAction' => ['stsdays' => ['localActionStatus' => $this->tsbParamsChangeAction("'stsdays'")]]],
		        'overview' => ['hidden' => true]
		    ]]),
		    'ltsdays' => ViewUtils::tukosNumberBox($this, 'ltsdays', ['atts' => [
		        'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '00.'], 'onChangeLocalAction' => ['ltsdays' => ['localActionStatus' => $this->tsbParamsChangeAction("'ltsdays'")]]],
		        'overview' => ['hidden' => true]
		    ]]),
		    'stsratio' => ViewUtils::tukosNumberBox($this, 'stsratio', ['atts' => [
		        'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '0.00'], 'onChangeLocalAction' => ['stsratio' => ['localActionStatus' => $this->tsbParamsChangeAction("'stsratio'")]]],
		        'overview' => ['hidden' => true]
		    ]]),
		    'initialsts' => ViewUtils::tukosNumberBox($this, 'initialsts', ['atts' => [
		        'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##'], 'onChangeLocalAction' => ['initialsts' => ['localActionStatus' => $this->tsbParamsChangeAction("'initialsts'")]], 'hidden' => true],
		        'overview' => ['hidden' => true]
		    ]]),
		    'initiallts' => ViewUtils::tukosNumberBox($this, 'initiallts', ['atts' => [
		        'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##'], 'onChangeLocalAction' => ['initiallts' => ['localActionStatus' => $this->tsbParamsChangeAction("'initiallts'")]]],
		        'overview' => ['hidden' => true]
		    ]]),
		    'initialhracwr' => ViewUtils::tukosNumberBox($this, 'initialhracwr', ['atts' => [
		        'edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#.#'], 'onChangeLocalAction' => ['initialhracwr' => ['localActionStatus' => $this->initialProgressivityChangeAction()]]],
		        'overview' => ['hidden' => true]
		    ]]),
		    'displayfromdate' => ViewUtils::tukosDateBox($this, 'Displayfrom', ['atts' => ['edit' => [
		        'onChangeLocalAction' => [
		            //'loadchart' => ['localActionStatus' => $this->loadChartLocalAction('loadchart')],
		            //'performedloadchart' => ['localActionStatus' => $this->loadChartLocalAction('performedloadchart')],
		            'displayfromdate' => ['localActionStatus' => $this->displayFromDateChangeAction()]
		        ]]]]),
		    'displayfromsts' => ViewUtils::tukosNumberBox($this, 'Displayinitialsts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##']], 'overview' => ['hidden' => true]]]),
		    'displayfromlts' => ViewUtils::tukosNumberBox($this, 'Displayinitiallts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '###.##']],  'overview' => ['hidden' => true]]]),
		    //'displayfrom' => ViewUtils::textBox($this, 'indicators')
		];
	
		$subObjects = [
			'sptsessions' => [
				'atts' => [
				    'title' => $this->tr('Sessions'), 'allDescendants' => true, 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdate',
				        'endDateTimeCol' => 'startdate'/*, 'freezeWidth' => true*/, 'minWidth' => '50',
					'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
					'onChangeNotify' => [
						'calendar' => [
							'startdate' => 'startTime',  'duration' => 'duration',  'name' => 'summary', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport'/*, 
						      'warmup' => 'warmup',
						    'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown'*/, 'mode' => 'mode'/*, 'trimphr' => 'trimphr', 'trimppw' => 'trimppw'*/
					]],
					'onDropMap' => [
                        'templates' => ['fields' => ['name' => 'name', 'comments' => 'comments', 'startdate' => 'startdate', 'duration' => 'duration', 'intensity' => 'intensity', 'stress' => 'stress', 
                            'sport' => 'sport', 'warmup' => 'warmup', 'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 'mode' => 'mode', 'trimphr' => 'trimphr', 'trimppw' => 'trimppw'
                        ]],
						'warmup' => ['mode' => 'update', 'fields' => ['warmup' => 'summary']],
						'mainactivity' => ['mode' => 'update', 'fields' => ['mainactivity' => 'summary']],
						'warmdown' => ['mode' => 'update', 'fields' => ['warmdown' => 'summary']],
					],
					'sort' => [['property' => 'startdate', 'descending' => true], ['property' => 'sessionid', 'descending' => true]],
					'onWatchLocalAction' => [
					    'allowApplicationFilter' => ['sptsessions' => $this->dateChangeGridLocalAction("tWidget.form.valueOf('displayeddate')", 'tWidget', 'newValue')],
					    'value' => ['sptsessions' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->sptSessionsTsbAction()]]],
					    'collection' => [
					        'sptsessions' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => false], 'action' => $this->sptSessionsTsbAction()]],
					        'calendar' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => 'tWidget.currentView && tWidget.currentView.invalidateLayout();return true;']],
					        //'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekloadchart'),]],
					        //'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->weekLoadChartLocalAction('weekperformedloadchart')]],
					    ]],
				    'renderCallback' => "if (rowData.mode === 'performed'){domstyle.set(node, 'fontStyle', 'italic');}",
				    //'sendOnHidden' => ['athleteweeklyfeeling', 'coachweeklycomments']
				    //'createRowAction' => $this->createRowAction(),
				    //'updateRowAction' => $this->updateRowAction(),
				    'deleteRowAction' => $this->deleteRowAction(),
				    'colsDescription' => [
				        'startdate' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['startdate' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
				        'trimphr' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['trimphr' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
				        'mode' => ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => ['mode' => ['localActionStatus' => $this->tsbChangeLocalAction()]]]]]],
				        /*'sts' => ['atts' => ['storeedit' => ['noMarkAsChanged' => true]]],
				        'lts' => ['atts' => ['storeedit' => ['noMarkAsChanged' => true]]],
				        'tsb' => ['atts' => ['storeedit' => ['noMarkAsChanged' => true]]],*/
				    ],
			        'afterActions' => ['createNewRow' => $this->afterCreateRow(), 'updateRow' => $this->afterUpdateRow(), 'deleteRow' => $this->afterDeleteRow(), 'deleteRows' => $this->afterDeleteRows()],
				    'beforeActions' => ['createNewRow' => $this->beforeCreateRow(), 'deleteRows' => $this->beforeDeleteRows(), 'updateRow' => $this->beforeRowChange()],
				    'noCopyCols' => ['googleid', 'stravaid'],
				    'editActionLayout' => SessionsEditView::editDialogLayout(),
				],
			    'filters' => ['parentid' => '@id', ['col' => 'startdate', 'opr' => '>=', 'values' => '@displayfromdate'], ['col' => 'startdate', 'opr' => '>=', 'values' => '@fromdate'], 
				    [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
			    'removeCols' => ['sportsman','grade', 'configstatus'],
			    'hiddenCols' => ['parentid', 'warmupdetails', 'mainactivitydetails', 'warmdowndetails', 'sessionid', 'googleid', 'mode', 'coachcomments', 'sts', 'lts', 'tsb', 'timemoving', 'avghr', 'avgpw', 'hr95', 'trimpavghr', 'trimpavgpw', 'trimphr', 'trimppw', 'mechload', 'h4time', 'h5time',
			            'contextid', 'updated'],
			    'ignorecolumns' => ['athleteweeklycomments', 'coachweeklyresponse'] // temporary: these were suppressed but maybe present in some customization items
			],

			'templates' => [
			    'object' => 'sptsessions',
				'atts' => [
					'title' => $this->tr('sessionstemplates'),
					'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false], /*'freezeWidth' => true, 'minGridWidth' => '600', 'width' => 600,*/
				    'sort' => [['property' => 'name']],
				    'beforeActions' => ['createNewRow' => $this->templatesBeforeCreateNewRow()],
				    'afterActions' => ['createNewRow' => $this->templatesAfterCreateNewRow()],
				    'editActionLayout' => SessionsEditView::editDialogLayout(),
				],
				'filters' => ['grade' => 'TEMPLATE'],
			    'initialRowValue' => ['mode' => 'planned'],
			    'removeCols' => /*$this->user->isRestrictedUser() ? ['sportsman','googleid', 'warmupdetails', 'mainactivitydetails', 'warmdowndetails', 'coachcomments', 'comments', 'grade', 'configstatus'] : */['sportsman','grade', 'configstatus'],
			    'hiddenCols' => ['parentid'/*, 'stress'*/, 'warmupdetails', 'mainactivitydetails', 'warmdowndetails', 'sessionid', 'googleid', 'mode', 'coachcomments', 'sts', 'lts', 'tsb', 'timemoving', 'avghr', 'avgpw', 'hr95', 'trimpavghr', 'trimpavgpw', 'trimphr', 'trimppw', 'mechload', 'h4time', 'h5time',
			        'contextid', 'updated'],
			    'ignorecolumns' => ['athleteweeklycomments', 'coachweeklyresponse'], // temporary: these were suppressed but maybe present in some customization items
			    'allDescendants' => true, 'width' => '400'
			],

			'warmup' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('warmuptemplates'),/* 'storeType' => 'LazyMemoryTreeObjects', */ 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false], 'hidden' => true],
				'filters' => ['stagetype' => 'warmup'],
				 'allDescendants' => true,
			],
			'mainactivity' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('mainactivitytemplates'),/* 'storeType' => 'LazyMemoryTreeObjects', */  'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false], 'hidden' => true],
				'filters' => ['stagetype' => 'mainactivity'],
				'allDescendants' => 'true'
			],
			'warmdown' => [
				'object' => 'sptsessionsstages',
				'atts' => ['title' => $this->tr('warmdowntemplates'), /*'storeType' => 'LazyMemoryTreeObjects',*/ 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false], 'hidden' => true],
				'filters' => ['stagetype' => 'warmdown'],
				 'allDescendants' => true, 
			],
		];
		foreach (array_diff_key($chartsCols, array_flip(['trimphr'/*, 'trimppw'*/, 'load', 'perceivedload', 'fatigue', 'hracwr'])) as $col => $description){
		    $defaultColsDescription[$col] = ['atts' => ['storeedit' => ['editorArgs' => ['onChangeLocalAction' => [$col => ['localActionStatus'  => $this->cellChartChangeLocalAction()]]]]]];
		    
		}
		$subObjects['sptsessions']['atts']['colsDescription'] = Utl::array_merge_recursive_replace($defaultColsDescription, $subObjects['sptsessions']['atts']['colsDescription']);
		$this->customize($customDataWidgets, $subObjects, [ 'grid' => ['calendar', 'displayeddate', 'synchrostart', 'synchroend', 'loadchart', 'performedloadchart', 'weekloadchart', 'weekperformedloadchart', 'weeklies'],
		    'get' => ['displayeddate', 'loadchart', 'performedloachart', 'weekloadchart', 'weekperformedloadchart'],
		    'post' => ['displayeddate', 'loadchart', 'performedloadchart', 'weekloadchart', 'weekperformedloadchart', 'synchrostart', 'synchroend']], ['weeklies' => [], 'displayfrom' => []]);
	}
	public static function programAclLocalAction(){
	    $tukosBackOfficeUserId = Tfk::tukosBackOfficeUserId;
	    return <<<EOT
const form = sWidget.form, acl = form.getWidget('acl'), coachId = form.valueOf('coach'), athleteId = form.valueOf('parentid');
if (!coachId){
    Pmg.setFeedback('needtodefinecoach');
    return false;
}
if (athleteId && coachId){
    Pmg.serverDialog({object: 'users', view: 'Edit', action: 'GetItems', query: {storeatts: JSON.stringify({where: [{col: 'parentid', opr: 'IN', values: [athleteId, coachId]}], cols: ['id', 'parentid']})}}).then(
    	function (response){
            if (response.data.items.length > 0){
                //acl.set('value', '');
                acl.deleteRows(lang.clone(acl.store.fetchSync()), true);
                acl.addRow(null, {userid: $tukosBackOfficeUserId,permission:"2"});
                if (response.data.items.length === 1){
                        acl.addRow(null, {userid: response.data.items[0].id, permission: "3"});
                }else{
                    response.data.items.forEach(function(item){
                        acl.addRow(null, {userid: item.id, permission: item.parentid == athleteId ? "2" : "3"});
                    });
                }
                let sessionsWidget = form.getWidget('sptsessions'), idp = sessionsWidget.store.idProperty, sessionsRows = sessionsWidget.store.fetchSync();
                let aclValue = {1: {rowId: 1, userid: $tukosBackOfficeUserId, permission:"3"}}, rowId = 2;
                response.data.items.forEach(function(item){
                    aclValue[rowId] = {rowId: rowId, userid: item.id, permission:"3"};
                    rowId += 1;
                });
                aclValue = JSON.stringify(aclValue);
                sessionsRows.forEach(function(row){
                    const toUpdate = {acl:  aclValue};
                    toUpdate[idp] = row[idp];
                    sessionsWidget.updateRow(toUpdate);
                });
            }else{
                Pmg.setFeedback(Pmg.message('sportsmanorcoachhasnouserassociatednoacl', 'sptprograms'), null, null, true);
            }
		}
	);
}else{
    acl.deleteRows(lang.clone(acl.store.fetchSync()), true);
}
return true;
EOT;
	}
	function sptSessionsTsbAction(){
	    return <<<EOT
require (["tukos/objects/sports/TsbCalculator", "tukos/objects/sports/LoadChart"], function(TsbCalculator, LoadChart){
    var grid = sWidget, form = grid.form, params = {};
    sWidget.tsbCalculator = this.tsbCalculator || new TsbCalculator({sessionsStore: sWidget.store, form: form, stressProperties: ['trimphr', 'trimpavghr']});
    sWidget.tsbCalculator.initialize();
    sWidget.tsbCalculator.updateRowAction(sWidget, false, true);
    grid.loadChartUtils = grid.loadChartUtils || new LoadChart({sessionsStore: grid.store});
    grid.loadChartUtils.updateCharts(grid, 'changed');
    if (form.programsConfig && form.programsConfig.spiders){
        require(["tukos/objects/sports/KpiChart"], function(KpiChart){
            form.kpiChartUtils = form.kpiChartUtils || new KpiChart({form: form, sessionsStore: grid.store});
            let spiders = JSON.parse(form.programsConfig.spiders);
            for (const spider of spiders){
                form.kpiChartUtils.setChartValue('spider' + spider.id);
            }
            form.openActionCompleted = true;
        });
    }else{
        form.openActionCompleted = true;
    }
});
return true;
EOT
	    ;
	}
	function OpenEditAction(){
	    $currentDate = date('Y-m-d');
	    return <<<EOT
const fromDate = this.valueOf('fromdate'), toDate = this.valueOf('todate');
if (fromDate && toDate){
    if ('$currentDate' < fromDate){
        this.setValueOf('displayeddate', fromDate);
    }else if('$currentDate' > toDate){
        this.setValueOf('displayeddate', toDate);
    }
}
if (Pmg.isRestrictedUser()){
    const self = this;
    ['parentid', 'name', 'fromdate', 'duration', 'todate', 'initialsts', 'initiallts', 'initialhracwr'].forEach(function(widgetName){
        self.getWidget(widgetName).set('disabled', true);
    });
}
EOT
        ;
	}
	function weekliesBeforeCreateNewRow(){
	    return <<<EOT
var row = args || this.clickedRow.data;
row.weekof = this.valueOf('displayeddate');
EOT
	    ;
	}
	function templatesBeforeCreateNewRow(){
	    $restrictedUserParentTemplatesRowName = $this->tr('restricteduserparenttemplatesrowname');
	    return <<<EOT
if (Pmg.isRestrictedUser() && args.name !== "$restrictedUserParentTemplatesRowName"){
    const self = this, row = args, idp = this.collection.idProperty;
    let parentRow = this.store.filter((new this.store.Filter()).eq('name', "$restrictedUserParentTemplatesRowName")).fetchSync();
    if (parentRow.length === 0){
        Pmg.setFeedback(Pmg.message('Cannotcreatenewtemplate'));
        return;
    }else{
        parentRow = parentRow[0];
    }
    row.parentid = parentRow.id;
    row.name = (row.name ? row.name + ' - ' : '') + Pmg.message('newtemplate');
    this.rowIdpToExpand = parentRow[idp];
}
EOT
	    ;
	}
	function templatesAfterCreateNewRow(){
	    return <<<EOT
if (Pmg.isRestrictedUser() && this.rowIdpToExpand){
    const rowToExpand = this.row(this.rowIdpToExpand);
    if (!rowToExpand.data['hasChildren']){
        rowToExpand.data['hasChildren'] = true;
        this.store.putSync(rowToExpand.data, {overwrite: true});
    }
    this.expand(this.row(this.rowIdpToExpand), true);
    delete this.rowIdpToExpand;
}
EOT
	    ;
	}
	function beforeRowChange(){
	    return <<<EOT
if (!this.isBulkRowAction){
    var idp = this.collection.idProperty, rowChanges = (args || this.clickedRow.data), rowBeforeChange = this.collection.getSync(rowChanges[idp]);
    this.rowBeforeChange = lang.clone(rowBeforeChange);
    if ((rowChanges.startdate && (rowChanges.startdate !== rowBeforeChange.startdate)) || (rowChanges.parentid && (rowChanges.parentid !== rowBeforeChange.parentid)) || (rowChanges.mode !== undefined && (rowChanges.mode != rowBeforeChange.mode))){
        rowChanges.sessionid = 1;
        this.store.filter((new this.store.Filter()).eq('startdate', rowChanges.startdate)[rowChanges.mode === 'performed' ? 'eq' : 'ne']('mode', 'performed')).sort('sessionid', 'descending').fetchRangeSync({start: 0, end: 1}).forEach(function(largestSessionIdRow){
            rowChanges.sessionid = Number(largestSessionIdRow.sessionid) + 1;
        });
    }
}
EOT
	    ;
	}
	function beforeCreateRow(){
	    return <<<EOT
var row = args || this.clickedRow.data;
if (!this.isBulkRowAction){
    if(row.startdate){
        this.store.filter((new this.store.Filter()).eq('startdate', row.startdate)[row.mode === 'performed' ? 'eq' : 'ne']('mode', 'performed')).sort('sessionid', 'descending').fetchRangeSync({start: 0, end: 1}).forEach(function(largestSessionIdRow){
            row.sessionid = Number(largestSessionIdRow.sessionid) + 1;
        });
    }
    row.sportsman = this.valueOf('parentid');
    if (!row.mode){
        row.mode = (this.form.viewModeOption === 'viewperformed') ? 'performed' : 'planned';
    }
    if (!row.sport){
        row.sport = 'other';
    }
}
EOT
	    ;
	}
	public function sessionCreationAclLocalAction(){
	    $tukosBackOfficeUserId = Tfk::tukosBackOfficeUserId;
	    return <<<EOT
//const self = this, idp = this.store.idProperty;
Pmg.serverDialog({object: 'users', view: 'Edit', action: 'GetItems', query: {storeatts: JSON.stringify({where: [{col: 'parentid', opr: 'IN', values: [row.sportsman, this.valueOf('coach')]}], cols: ['id', 'parentid'], promote: true})}}).then(
	function (response){
        if (response.data.items.length > 0){
            let acl = {1: {rowId: 1, userid: $tukosBackOfficeUserId, permission:"3"}}, rowId = 2;
            response.data.items.forEach(function(item){
                acl[rowId] = {rowId: rowId, userid: item.id, permission:"3"};
                rowId += 1;
            });
            const toUpdate = {acl:  JSON.stringify(acl)};
            toUpdate[idp] = row[idp];
            self.updateRow(toUpdate);
        }else{
            Pmg.setFeedback(Pmg.message('sportsmanorcoachhasnouserassociatednoacl', 'sptprograms'), null, null, true);
        }
	}
);
EOT
	    ;
	}
	function afterCreateRow(){
	    $tukosBackOfficeUserId = Tfk::tukosBackOfficeUserId;
	    return <<<EOT
const self = this, idp = this.store.idProperty;
if (!this.isBulkRowAction){
    var row = arguments[1][0] || this.clickedRow.data;
    if (!row.startdate){
        return;
    }
    if (row.mode === 'performed'){
        this.tsbCalculator && this.tsbCalculator.updateRowAction(this, this.store.getSync(row[idp]), true);
    }
    {$this->sessionCreationAclLocalAction()}
    this.loadChartUtils.updateCharts(this, row.mode);
}
EOT
	    ;
	}
	function afterUpdateRow(){
	    return <<<EOT
if (!this.isBulkRowAction){
    var row = arguments[1][0] || this.clickedRow.data, rowBeforeChange = this.rowBeforeChange, startingRow, isPerformed;
    if (rowBeforeChange.mode !== row.mode || rowBeforeChange.startdate !== row.startdate || rowBeforeChange.trimphr !== row.trimphr){
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
}
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
            if (row.trimphr){
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
        grid.tsbCalculator.updateRowAction(grid, (col === 'trimphr' || (col === 'mode' && newValue === 'performed')) ? row : false, true);
    }
    grid.loadChartUtils.updateCharts(grid, true);
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
	function tsbParamsChangeAction($reset){
	    return <<<EOT
const form = sWidget.form, grid = form.getWidget('sptsessions'); 
if ($reset){
    utils.forEach({displayfromdate: 'fromdate', displayfromsts: 'initialsts', displayfromlts: 'initiallts'}, function(source, target){
        form.setValueOf(target, form.valueOf(source));
    });
}
grid.tsbCalculator.initialize();
grid.tsbCalculator.updateRowAction(grid, false, true);
grid.refresh({skipScrollPosition: true});
grid.loadChartUtils.updateCharts(grid, true);
return true;
EOT
	    ;
	}
	function initialProgressivityChangeAction(){
	    return <<<EOT
const initialLts = sWidget.form.valueOf('initiallts');
console.log('initialsts: ' + initialLts);
if (initialLts){
    sWidget.form.setValueOf('initialsts', initialLts * newValue);
}
return true;
EOT
	    ;
	}
	function displayFromDateChangeAction(){
	    $tsbParamsChangeAction = $this->tsbParamsChangeAction('false');
	    return <<<EOT
const form = sWidget.form, grid = form.getWidget('sptsessions'), tsbCalculator = grid.tsbCalculator, displayFromDate = form.valueOf('displayfromdate'), fromDate = form.valueOf('fromdate');
if (newValue < oldValue && form.parent.serverFormContent.data.value.displayfromdate > fromDate){
    grid.watchOnChange = false;
    when(form.serverDialog({action: 'Save', query: {id: form.valueOf('id')}}, {displayfromdate: fromDate}, form.get('dataElts'), Pmg.message('actionDone'), false), function(response){
		grid.watchOnChange = true;
        if (response !== false){
			sWidget.form.setValueOf('displayfromdate', displayFromDate);
            tsbCalculator.setDisplayFromStsAndLts(displayFromDate);
            $tsbParamsChangeAction;
		}
	});
return; 
}else{
    tsbCalculator.setDisplayFromStsAndLts(displayFromDate);
    $tsbParamsChangeAction;
}
EOT
	    ;
	}
	function loadChartChangeAction($chartName, $changedAtt){
	    return <<<EOT
var form = sWidget.form, grid = form.getWidget('sptsessions');
form.resize();
if (!newValue || '$changedAtt' !== 'hidden'){
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
	function getActionsToEnableDisable(){
	   return array_merge(parent::getActionsToEnableDisable(), ['googlesync', 'sessionstracking']);
	}
}
?>
