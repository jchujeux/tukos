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

	use CalendarsViewUtils;
	
	function __construct($objectName, $translator=null){
	    $stressOptionsString = "['" . implode("','", Sports::$stressOptions) . "']";
	    parent::__construct($objectName, $translator, 'Sportsman', 'Title');
		$this->doNotEmpty = ['displayeddate', 'synchrostart', 'synchroend'];
		
		$this->setGridWidget('sptsessions', 'startdate', 'startdate');
		$chartCols = ['duration' => 'lines', 'distance' => 'lines', 'elevationgain' => 'lines', 'load' => 'cluster', 'intensity' => 'cluster', 'stress' => 'cluster', 'perceivedload' => 'cluster', 'perceivedeffort' => 'cluster', 
		    'sensations' => 'cluster', 'mood' => 'cluster', 'fatigue' => 'cluster'];
		$chartColsTLabel = [];
		foreach($chartCols as $col => $plot){
		    $chartColsTLabel[$col] = $this->tr($col);
		}
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
        $loadChartLocalActionString = <<<EOT
	tWidget.plots.week.values = dutils.difference(tWidget.form.valueOf('fromdate'), newValue, 'week') + 1;
	tWidget.chart.addPlot('week', tWidget.plots.week);
	tWidget.chart.render();
	return true;
EOT;
        $weekLoadChartLocalActionString = function($plannedOrPerformed, $presentCols, $colsTLabel) use ($tDaysOfWeek, $tDayOfWeek, $tDateOfDay){
            if ($plannedOrPerformed === 'planned'){
                $weekLoadChart = 'weekloadchart';
                $sessionsFilter = 'ne';
            }else{
                $weekLoadChart = 'weekperformedloadchart';
                $sessionsFilter = 'eq';
            }
            $presentColsString = json_encode($presentCols);
            $colsTLabelString = json_encode($colsTLabel);
            return <<<EOT
  dojo.ready(function(){
	var date = new Date(sWidget.form.valueOf('displayeddate')), weekLoadChartWidget = sWidget.form.getWidget('$weekLoadChart'), dayDate, chartItem, chartData = [], normalizedDuration = 120,
	    gridWidget = sWidget.form.getWidget('sptsessions'), dayType = weekLoadChartWidget.get('daytype'), filter = new gridWidget.store.Filter(), daysOfWeek = $tDaysOfWeek, presentCols = $presentColsString, colsTLabel = $colsTLabelString;
	for (i = 1; i <= 7; i++){
	  dayDate = dutils.formatDate(dutils.getDayOfWeek(i, date));
	  chartItem = {day: dayType === 'dayofweek' ? daysOfWeek[i-1] : dayDate};
      presentCols.forEach(function(col){
        chartItem[col] = 0;
      });
	  gridWidget.collection.filter(filter.eq('startdate', dayDate)['$sessionsFilter']('mode', 'performed')).forEach(function(item){
	    if (item.sport !== 'rest'){
            presentCols.forEach(function(col){//duration must come first, perceivedLoad must comes after perceivedeffort, load after intensity
                chartItem[col] = Number(item[col]);                
                chartItem[col + 'Tooltip'] = colsTLabel[col] + ': ';
                switch (col){
                    case 'duration':
                        chartItem.duration = dutils.seconds(item.duration, 'time') / 60;
	                    chartItem.durationTooltip += utils.transform(chartItem.duration, 'minutesToHHMM');
                        break;
                    case 'distance':
                        chartItem.distanceTooltip += chartItem[col] + ' km';
                        break;
                    case 'elevationgain':
                        chartItem.elevationgainTooltip += chartItem.elevationgain + ' m';
                        chartItem.elevationgain = chartItem.elevationgain / 10;
                        break;
                    case 'load':
                        chartItem.load = chartItem.intensity * chartItem.duration / normalizedDuration;
                        chartItem.loadTooltip += chartItem.load;
                        break;
                    case 'perceivedload':
                        chartItem.perceivedload = chartItem.perceivedeffort * chartItem.duration / normalizedDuration;
                        chartItem.perceivedloadTooltip += chartItem.perceivedload;
                        break;
                    case 'fatigue':
                        chartItem.fatigue = chartItem.sensations && chartItem.mood ? 11 - (chartItem.sensations + chartItem.mood) / 2 : 0;
                        chartItem.fatigueTooltip += chartItem.fatigue;
                        break;
                    default:
                        chartItem[col + 'Tooltip'] += chartItem[col];
                }
            });	       
        }
	  });
	  chartData.push(chartItem);
	}
	weekLoadChartWidget.set('value', {store: chartData, axes: {x: {title: dayType === 'dayofweek' ? '$tDayOfWeek' : '$tDateOfDay'}}});
  });
  return true;
EOT;
        };
        $synchroStartLocalActionString = function($displayeddate, $synchnextmonday){
		    $displayedDateValue = $displayeddate === "newValue" ? "newValue" : ("sWidget.valueOf('$displayeddate')");
		    $synchNextMondayValue = $synchnextmonday === 'newValue' ? "newValue === 'YES'" : ("sWidget.valueOf('$synchnextmonday') === 'YES'");
		    return <<<EOT
	dojo.ready(function(){
    	var synchroWeeksBefore = parseInt(sWidget.valueOf('#synchroweeksbefore')), synchroWeeksAfter = parseInt(sWidget.valueOf('#synchroweeksafter')),
    		displayeddate = $displayedDateValue, form = sWidget.form;
    	if (Number.isInteger(synchroWeeksBefore)){
            form.getWidget('synchrostart').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date(displayeddate)), 'week', -synchroWeeksBefore)));
    	}
    	if (Number.isInteger(synchroWeeksAfter)){
    		var nextMonday = $synchNextMondayValue;
    		form.getWidget('synchroend').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(nextMonday ? 1 : 7, new Date(displayeddate)), 'week', nextMonday ? synchroWeeksAfter + 1 : synchroWeeksAfter)));
    	}
	});
	return true;
EOT;
		};
		$loadChartCustomization = function($idProperty, $idPropertyStoreData){
		    $idPropertyType = $idProperty.'type';
		    return [
		    'chartHeight' => ['att' => 'chartHeight', 'type' => 'NumberUnitBox', 'name' => $this->tr('chartHeight'),
		        'units' => [['id' => '', 'name' => ''], ['id' => 'auto', 'name' => 'auto'], ['id' => '%', 'name' => '%'], ['id' => 'em', 'name' => 'em'], ['id' => 'px', 'name' => 'px']]],
		        $idPropertyType => ['att' =>  $idPropertyType, 'type' => 'StoreSelect', 'name' => $this->tr($idPropertyType),
		            'storeArgs' => ['data' => $idPropertyStoreData]],
		        'showTable' => ['att' =>  'showTable', 'type' => 'StoreSelect', 'name' => $this->tr('showTable'),
		        'storeArgs' => ['data' => Utl::idsNamesStore(['yes', 'no'], $this->tr)]],
		    'tableWidth' => ['att' => 'tableWidth', 'type' => 'NumberUnitBox', 'name' => $this->tr('tableWidth'),
		        'units' => [['id' => '', 'name' => ''], ['id' => 'auto', 'name' => 'auto'], ['id' => '%', 'name' => '%'], ['id' => 'em', 'name' => 'em'], ['id' => 'px', 'name' => 'px']]]
		];};

		$programLoadChartIdPropertyStoreData = [['id' => 'weekoftheyear', 'name' => $tWeekOfTheYear], ['id' =>  'weekofprogram', 'name' =>  $this->tr('weekofprogram')]];

		$weekLoadChartIdPropertyStoreData = [['id' => 'dayofweek', 'name' => $tDayOfWeek], ['id' =>  'dateofday', 'name' =>  $tDateOfDay]];

		$loadChartDescription = function($chartName, $idProperty, $idPropertyType, $sortAttribute, $xTitle, $presentCols, $chartCols, $chartColsTLabel, $chartColsLegendUnit, $idPropertyTypeLocalActionString, $customizableAtts){
		    $tableAttsColumns = [$idProperty => ['label' => $this->tr($idProperty), 'field' => $idProperty, 'width' => 65]]; $series = []; $linesAxisLabel = ''; $clusterAxisLabel = '';
		    foreach($presentCols as $col){
		        $plot = $chartCols[$col];
		        $colLabel = $chartColsTLabel[$col];
		        $tableAttsColumns[$col] = ['label' => $colLabel . ' ' . Utl::getItem($col, $chartColsLegendUnit, ''), 'field' => $col, 'width' => 60];
		        $series[$colLabel] = ['value' => ['y' => $col, 'text' => $idProperty, 'tooltip' => $col . 'Tooltip'], 'options' => ['plot' => $plot]];
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
			'displayeddate' => $this->displayedDateDescription(['atts' => ['edit' => ['onWatchLocalAction' => ['value' => [
			    'loadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $loadChartLocalActionString,]],
			    'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString('planned', $plannedPresentCols, $chartColsTLabel),]],
			    'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString('performed', $performedPresentCols, $chartColsTLabel),]],
			    'performedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $loadChartLocalActionString,]],
			    'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $synchroStartLocalActionString('newValue', '#synchnextmonday'),]],
			]]]]]),
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
					'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $synchroStartLocalActionString('#displayeddate', 'newValue'),]],
			]]]]]),
			'questionnairetime'  =>  ViewUtils::timeStampDataWidget($this, 'QuestionnaireTime', ['atts' => ['edit' => ['disabled' => true]]]),
		    'loadchart' => $loadChartDescription('loadchart', 'week', 'weekoftheyear', 'weekof', $tWeekOfTheYear, $plannedPresentCols, $chartCols, $chartColsTLabel, $chartColsLegendUnit,
		        "Pmg.setFeedback('savecustomforeffect', null, null, true); return true;", $loadChartCustomization('week', $programLoadChartIdPropertyStoreData)),
		    'weekloadchart' => $loadChartDescription('weekloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfWeek, $plannedPresentCols, $chartCols, $chartColsTLabel, $chartColsLegendUnit,
		        $weekLoadChartLocalActionString('planned', $plannedPresentCols, $chartColsTLabel), $loadChartCustomization('day', $weekLoadChartIdPropertyStoreData)),
		    'performedloadchart' => $loadChartDescription('performedloadchart', 'week', 'weekoftheyear', 'weekof', $tWeekOfTheYear, $performedPresentCols, $chartCols, $chartColsTLabel, $chartColsLegendUnit,
		        "Pmg.setFeedback('savecustomforeffect', null, null, true); return true;", $loadChartCustomization('week', $programLoadChartIdPropertyStoreData)),
		    'weekperformedloadchart' => $loadChartDescription('weekperformedloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfWeek, $performedPresentCols, $chartCols, $chartColsTLabel, $chartColsLegendUnit,
		        $weekLoadChartLocalActionString('performed', $performedPresentCols, $chartColsTLabel), $loadChartCustomization('day', $weekLoadChartIdPropertyStoreData)),
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
					'onWatchLocalAction' => ['date' => [
					    'loadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $loadChartLocalActionString]],
					    'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString('planned', $plannedPresentCols, $chartColsTLabel)]],
					    'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString('performed', $performedPresentCols, $chartColsTLabel)]],
					    'performedloadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $loadChartLocalActionString]],
					    'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $synchroStartLocalActionString('newValue', '#synchnextmonday'),]],
				]]]]], 
				'fromdate', 'todate'),
		];
	
		$subObjects = [
			'sptsessions' => [
				'atts' => [
					'title' => $this->tr('Sessions'), /*'storeType' => 'LazyMemoryTreeObjects',  */ 'allDescendants' => true, 'allowApplicationFilter' => 'yes',
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
					    'allowApplicationFilter' => ['sptsessions' => $this->allowApplicationFilterChangeGridWidgetLocalAction],
					    'collection' => [
					        'calendar' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => 'tWidget.currentView.invalidateLayout();return true;']],
					        'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString('planned', $plannedPresentCols, $chartColsTLabel),]],
					        'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString('performed', $performedPresentCols, $chartColsTLabel),]],
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
		$this->customize($customDataWidgets, $subObjects, [ 'grid' => ['calendar', 'displayeddate', 'loadchart'], 'get' => ['displayeddate', 'weekloadchart', 'weekperformedloadchart'],
		    'post' => ['displayeddate', 'weekloadchart', 'weekperformedloadchart', 'synchrostart', 'synchroend']]);
	}
}
?>
