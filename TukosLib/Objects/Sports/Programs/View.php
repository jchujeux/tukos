<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\Collab\Calendars\CalendarsViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

	use CalendarsViewUtils;
	
	function __construct($objectName, $translator=null){
	    $intensityOptionsString = "['" . implode("','", Sports::$intensityOptions) . "']";
	    $stressOptionsString = "['" . implode("','", Sports::$stressOptions) . "']";
	    parent::__construct($objectName, $translator, 'Sportsman', 'Title');
		$this->doNotEmpty = ['displayeddate', 'synchrostart', 'synchroend'];
		
		$this->setGridWidget('sptsessions', 'startdate', 'startdate');
		$tVolume = $this->tr('volume'); $tStress = $this->tr('stress'); $tLoad = $this->tr('load'); $tIntensity = $this->tr('intensity'); $tWeekOfTheYear = $this->tr('weekoftheyear'); $tDistance = $this->tr('distance');
		$tElevationGain = $this->tr('elevationgain'); $tPerceivedEffort = $this->tr('perceivedeffort'); $tFatigue = $this->tr('fatigue'); $tDayOfTheWeek = $this->tr('dayoftheweek');
		$qtPerceivedEffort = $this->tr('perceivedeffort', 'escapeSQuote');
		
		$loadChartLocalActionString =  
			"tWidget.plots.week.values = dutils.difference(tWidget.form.valueOf('fromdate'), newValue, 'week') + 1;" .
			"tWidget.chart.addPlot('week', tWidget.plots.week);" .
			"tWidget.chart.render();" .
			"return true;";
		$weekLoadChartLocalActionString =
    		"dojo.ready(function(){" .
    		//"var date = new Date(newValue), dayDate, chartItem, chartData = [],\n" .
    		"var date = new Date(sWidget.form.valueOf('displayeddate')), dayDate, chartItem, chartData = [], normalizationVolume = 2,\n" .
    		"    gridWidget = sWidget.form.getWidget('sptsessions'), filter = new gridWidget.store.Filter();\n" .
    		"for (i = 1; i <= 7; i++){\n" .
    		"  dayDate = dutils.formatDate(dutils.getDayOfWeek(i, date));\n" .
    		"  chartItem = {day: dayDate, load: 0, intensity: 0, volume: 0, stress: 0};\n" .
    		"  gridWidget.collection.filter(filter.eq('startdate', dayDate).ne('mode', 'performed')).forEach(function(item){\n" .
    		"    if (item.sport !== 'rest'){\n" .
    		"        chartItem.volume = dutils.seconds(item.duration) / 60; chartItem.intensity = $intensityOptionsString.indexOf(item.intensity) + 1; chartItem.load = chartItem.intensity * chartItem.volume/60/normalizationVolume;\n" .
    		"        chartItem.stress = $stressOptionsString.indexOf(item.stress) + 1;\n" .
    		"    }\n" .
    		"  });\n" .
    		"  chartItem.loadTooltip = '$tLoad: ' + chartItem.load;\n" .
    		"  chartItem.volumeTooltip = chartItem.volume + ' ' + 'minutes';\n" .
    		"  chartItem.intensityTooltip = '$tIntensity: ' + chartItem.intensity;\n" .
    		"  chartItem.stressTooltip = '$tStress: ' + chartItem.stress;\n" .
    		"  chartData.push(chartItem);\n" .
    		"}\n" .
    		"sWidget.form.getWidget('weekloadchart').set('value', {store: chartData, axes: {x: {title: 'date'}}});\n" .
    		"});\n" .
    		"return true;";
		$weekPerformedLoadChartLocalActionString =
    		"dojo.ready(function(){" .
    		//"var date = new Date(newValue), dayDate, chartItem, chartData = [],\n" .
    		"var date = new Date(sWidget.form.valueOf('displayeddate')), dayDate, chartItem, chartData = [],\n" .
    		"    gridWidget = sWidget.form.getWidget('sptsessions'), filter = new gridWidget.store.Filter();\n" .
    		"for (i = 1; i <= 7; i++){\n" .
    		"  dayDate = dutils.formatDate(dutils.getDayOfWeek(i, date));\n" .
    		"  chartItem = {day: dayDate, distance: 0, elevationgain: 0, volume: 0, perceivedEffort: 0, fatigue: 0};\n" .
    		"  gridWidget.collection.filter(filter.eq('startdate', dayDate).eq('mode', 'performed')).forEach(function(item){\n" .
    		"    var duration = dutils.seconds(item.duration) / 60;" .
    		"    if (duration && (item.distance || item.elevationgain || item.perceivedEffort)){" .
    		"        console.log('duration' + duration);" .
    		"        chartItem.volume = duration; chartItem.distance = item.distance; chartItem.elevationgain = item.elevationgain / 10;chartItem.perceivedEffort = item.perceivedEffort || 5;\n" .
    		"        chartItem.fatigue = ((item.sensations || 5) + (item.mood || 5))/2;\n" .
    		"    }" .
    		"  });\n" .
    		"  chartItem.distanceTooltip = '$tDistance: ' + chartItem.distance + ' km';\n" .
    		"  chartItem.volumeTooltip = chartItem.volume + ' ' + 'minutes';\n" .
    		"  chartItem.elevationGainTooltip = '$tElevationGain: ' + chartItem.elevationgain * 10 + ' m';\n" .
    		"  chartItem.perceivedEffortTooltip = '$qtPerceivedEffort: ' + chartItem.perceivedEffort;\n" .
    		"  chartItem.fatigueTooltip = '$tFatigue: ' + chartItem.fatigue;\n" .
    		"  chartData.push(chartItem);\n" .
    		"}\n" .
    		"sWidget.form.getWidget('weekperformedloadchart').set('value', {store: chartData, axes: {x: {title: 'date'}}});\n" .
    		"});\n" .
    		"return true;";		
		$synchroStartLocalActionString = function($displayeddate, $synchnextmonday){
			return 
			"dojo.ready(function(){" .
			"var synchroWeeksBefore = parseInt(sWidget.valueOf('#synchroweeksbefore')), synchroWeeksAfter = parseInt(sWidget.valueOf('#synchroweeksafter')), " .
					"displayeddate = " . ($displayeddate === 'newValue' ? "newValue" : ("sWidget.valueOf('" . $displayeddate . "')")) . ", form = sWidget.form;" .
				"console.log('in synchrostartlocalactionstring');" .
				"if (Number.isInteger(synchroWeeksBefore)){" .
				"form.getWidget('synchrostart').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date(displayeddate)), 'week', -synchroWeeksBefore)));" .
				"}" .
				"if (Number.isInteger(synchroWeeksAfter)){" .
					"var nextMonday = " . ($synchnextmonday === 'newValue' ? "newValue === 'YES'" : ("sWidget.valueOf('" . $synchnextmonday . "') === 'YES'")) . ";" .
					"form.getWidget('synchroend').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(nextMonday ? 1 : 7, new Date(displayeddate)), 'week', nextMonday ? synchroWeeksAfter + 1 : synchroWeeksAfter)));" .
				"}" .
				"});" .
				"return true;";
		};
		$loadChartCustomization = function($idProperty, $idPropertyStoreData){
		    $idPropertyType = $idProperty.'type';
		    return [
		    'chartHeight' => ['att' => 'chartHeight', 'type' => 'NumberUnitBox', 'name' => $this->tr('chartHeight'),
		        'units' => [['id' => '', 'name' => ''], ['id' => 'auto', 'name' => 'auto'], ['id' => '%', 'name' => '%'], ['id' => 'em', 'name' => 'em'], ['id' => 'px', 'name' => 'px']]],
		        $idPropertyType => ['att' =>  $idPropertyType, 'type' => 'StoreSelect', 'name' => $this->tr($idPropertyType),
		            //'storeArgs' => ['data' => [['id' => 'weekoftheyear', 'name' => $tWeekOfTheYear], ['id' =>  'weekofprogram', 'name' =>  $this->tr('weekofprogram')]]]],
		            'storeArgs' => ['data' => $idPropertyStoreData]],
		        'showTable' => ['att' =>  'showTable', 'type' => 'StoreSelect', 'name' => $this->tr('showTable'),
		        'storeArgs' => ['data' => Utl::idsNamesStore(['yes', 'no'], $this->tr)]],
		    'tableWidth' => ['att' => 'tableWidth', 'type' => 'NumberUnitBox', 'name' => $this->tr('tableWidth'),
		        'units' => [['id' => '', 'name' => ''], ['id' => 'auto', 'name' => 'auto'], ['id' => '%', 'name' => '%'], ['id' => 'em', 'name' => 'em'], ['id' => 'px', 'name' => 'px']]]
		];};
		$programLoadChartIdPropertyStoreData = [['id' => 'weekoftheyear', 'name' => $tWeekOfTheYear], ['id' =>  'weekofprogram', 'name' =>  $this->tr('weekofprogram')]];
		$weekLoadChartIdPropertyStoreDataDescription = [['id' => 'dayofweek', 'name' => $tDayOfTheWeek], ['id' =>  'dateofday', 'name' =>  $this->tr('dateofday')]];
		$plannedLoadChartDescription = function ($chartName/*loadchart' or 'weeklyloadchart'*/, $idProperty /*'week' or 'day'*/, $idPropertyType, $sortAttribute/*'weekof' or 'dayofweek'*/, $xTitle/*$tWeekOfTheYear or $tDayOfTheWeek*/,
		                                         $timeUnit, $customizableAtts)
		  use ($tIntensity, $tLoad, $tStress, $tVolume, $tWeekOfTheYear)  {
		    return ['type' => 'chart', 'atts' => ['edit' => [
		        'title' => $this->tr($chartName), 'idProperty' => $idProperty, 'kwArgs'	 => ['sort'=> [['attribute' => $sortAttribute, 'descending' => false]]],
		        'style' => ['width' => '700px'],
		        //'chartStyle' => ['height' => '300px'],
		        'chartHeight' => '300px',
		        'showTable' => 'no',
		        'tableAtts' => [
		            'columns' => [$idProperty => ['label' => $this->tr($idProperty), 'field' => $idProperty, 'width' => 65], 'intensity' => ['label' => $tIntensity, 'field' => 'intensity', 'width' => 60],
		                'stress' => ['label' => $tStress, 'field' => 'stress', 'width' => 60], 'load' => ['label' => $tLoad, 'field' => 'load', 'width' => 60], 'volume' => ['label' => $tVolume, 'width' => 60]]
		        ],
		        ($idProperty.'type') => $idPropertyType,
		        'axes' =>  [
		            'x'   => ['title' => $xTitle, 'titleOrientation' => 'away', 'titleGap' => 5, 'labelCol' => $idProperty, 'majorTicks' => true, 'majorTickStep' => 1, 'minorTicks' => false],
		            'y1' => ['title' => $tIntensity . ', ' . $tLoad . ' & ' . $tStress, 'vertical' => true, 'min' => 0, 'max' => 5],
		            'y2' => ['title' => $tVolume . ' (' . $this->tr("$timeUnit") . 's)', 'vertical' => true, 'leftBottom' => false, 'min' => 0],
		        ],
		        'plots' =>  [
		            $tIntensity => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y1', 'lines' => true, 'markers' => true],
		            $tVolume => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y2', 'lines' => true, 'markers' => true],
		            $tStress => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y1', 'lines' => true, 'markers' => true],
		            'columns'  => ['plotType' => 'Columns', 'hAxis' => 'x', 'vAxis' => 'y1', 'gap' => 5, 'minBarSize' => 3, 'maxBarSize' => 40],
		            $idProperty	  => ['plotType' => 'Indicator', 'hAxis' => 'x', 'vAxis' => 'y1', 'stroke' => null, 'outline' => null, 'fill' => null, 'labels' => false, 'lineStroke' => ['color' => 'red', 'style' => 'shortDash', 'width' => 2]],
		        ],
		        'legend' => ['type' => 'SelectableLegend', 'options' => []],
		        'series' => [
		            $tIntensity => [ 'value' => ['y' => 'intensity', 'text' => $idProperty, 'tooltip' => 'intensityTooltip'], 'options' => ['plot' => $tIntensity]],
		            $tStress	 => ['value' => ['y' => 'stress', 'text' => $idProperty, 'tooltip' => 'stressTooltip'], 'options' => ['plot' => $tStress]],
		            $tLoad	   => ['value' => ['y' => 'load', 'text' => $idProperty, 'tooltip' => 'loadTooltip'], 'options' => ['plot' => 'columns']],
		            $tVolume  => ['value' => ['y' => 'volume', 'text' => $idProperty, 'tooltip' => 'volumeTooltip'], 'options' => ['plot' => $tVolume]],
		        ],
		        'customizableAtts' => $customizableAtts
		    ]]];
		};
		$performedLoadChartDescription = function($chartName, $idProperty, $idPropertyType, $sortAttribute, $xTitle, $customizableAtts) use ($tDistance, $tVolume, $tElevationGain, $tPerceivedEffort, $tFatigue){
		  return ['type' => 'chart', 'atts' => ['edit' => [
		    'title' => $this->tr($chartName), 'idProperty' => $idProperty, 'kwArgs'	 => ['sort'=> [['attribute' => $sortAttribute, 'descending' => false]]],
		    'style' => ['width' => '700px'],
		    //'chartStyle' => ['height' => '300px'],
		    'chartHeight' => '300px',
		    'showTable' => 'no',
		    'tableAtts' => [
		        'columns' => [$idProperty => ['label' => $this->tr($idProperty), 'field' => $idProperty, 'width' => 65], 'distance' => ['label' => $tDistance . ' (km)', 'field' => 'distance', 'width' => 60],
		            'elevationgain' => ['label' => $tElevationGain . ' (dam)', 'field' => 'elevationgain', 'width' => 60], 'volume' => ['label' => $tVolume . ' (minutes)', 'width' => 60],
		            'perceivedEffort' => ['label' => $tPerceivedEffort, 'width' => 60], 'fatigue' => ['label' => $tFatigue, 'width' => 60]]
		    ],
		    ($idProperty.'type') => $idPropertyType,
		    'axes' =>  [
		        'x'   => ['title' => $xTitle, 'titleOrientation' => 'away', 'titleGap' => 5, 'labelCol' => 'week', 'majorTicks' => true, 'majorTickStep' => 1, 'minorTicks' => false],
		        'y1' => ['title' => $tVolume . '(' . $this->tr('minute') . 's) ' . $tDistance . ' (km) & ' . $tElevationGain . ' (dam)', 'vertical' => true, 'min' => 0/*, 'max' => 500*/],
		        'y2' => ['title' => $tFatigue . ' & ' . $tPerceivedEffort, 'vertical' => true, 'leftBottom' => false, 'min' => 0, 'max' => 10],
		    ],
		    'plots' =>  [
		        'lines' => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y2', 'lines' => true, 'markers' => true, 'tension' => 'X', 'shadow' => ['dx' => 1, 'dy' => 1, 'width' => 2]],
		        'cluster' => ['plotType' => 'ClusteredColumns', 'vAxis' => 'y1', 'gap' => 3],
		        'week'	  => ['plotType' => 'Indicator', 'hAxis' => 'x', 'vAxis' => 'y1', 'stroke' => null, 'outline' => null, 'fill' => null, 'labels' => false, 'lineStroke' => ['color' => 'red', 'style' => 'shortDash', 'width' => 2]],
		    ],
		    'legend' => ['type' => 'SelectableLegend', 'options' => []],
		    'series' => [
		        $tElevationGain	 => ['value' => ['y' => 'elevationgain', 'text' => $idProperty, 'tooltip' => 'elevationGainTooltip'], 'options' => ['plot' => 'cluster']],
		        $tDistance => [ 'value' => ['y' => 'distance', 'text' => $idProperty, 'tooltip' => 'distanceTooltip'], 'options' => ['plot' => 'cluster']],
		        $tVolume	   => ['value' => ['y' => 'volume', 'text' => $idProperty, 'tooltip' => 'volumeTooltip'], 'options' => ['plot' => 'cluster']],
		        $tPerceivedEffort => [ 'value' => ['y' => 'perceivedEffort', 'text' => $idProperty, 'tooltip' => 'perceivedEffortTooltip'], 'options' => ['plot' => 'lines']],
		        $tFatigue	   => ['value' => ['y' => 'fatigue', 'text' => $idProperty, 'tooltip' => 'fatigueTooltip'], 'options' => ['plot' => 'lines']],
		    ],
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
			    'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString,]],
			    'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekPerformedLoadChartLocalActionString,]],
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
		    'loadchart' => $plannedLoadChartDescription('loadchart', 'week', 'weekoftheyear', 'weekof', $tWeekOfTheYear, 'hour', $loadChartCustomization('week', $programLoadChartIdPropertyStoreData)),
		    'weekloadchart' => $plannedLoadChartDescription('weekloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfTheWeek, 'minute', $loadChartCustomization('day', $weekLoadChartIdPropertyStoreDataDescription)),
		    'performedloadchart' => $performedLoadChartDescription('performedloadchart', 'week', 'weekoftheyear', 'weekof', $tWeekOfTheYear, $loadChartCustomization('week', $programLoadChartIdPropertyStoreData)),
		    'weekperformedloadchart' => $performedLoadChartDescription('weekperformedloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfTheWeek, $loadChartCustomization('day', $weekLoadChartIdPropertyStoreDataDescription)),
		    'worksheet' => ['atts' => ['edit' => ['dndParams' => ['accept' => ['dgrid-row', 'quarterhour']]/*, 'copyOnly' => true, 'selfAccept' => false*/]]],
			'calendar' => $this->calendarWidgetDescription(
				['atts' => ['edit' => [
					'columnViewProps' => ['minHours' => 0, 'maxHours' => 4],
					'style' => ['height' => '350px', 'width' => '700px'], 
					'timeMode' => 'duration', 'moveEnabled' => true,
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
					    'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString,]],
					    'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekPerformedLoadChartLocalActionString,]],
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
					        'weekloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekLoadChartLocalActionString,]],
					        'weekperformedloadchart' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $weekPerformedLoadChartLocalActionString,]],
					    ]],
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

		$this->customize($customDataWidgets, $subObjects, [ 'grid' => ['calendar', 'displayeddate', 'loadchart'], 'get' => ['displayeddate', 'weekloadchart', 'weekperformedloadchart'], 'post' => ['displayeddate', 'weekloadchart', 'weekperformedloadchart', 'synchrostart', 'synchroend']]);
	}
}
?>
