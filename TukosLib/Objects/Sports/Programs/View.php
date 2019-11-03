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
	    $intensityOptionsString = "['" . implode("','", Sports::$intensityOptions) . "']";
	    $stressOptionsString = "['" . implode("','", Sports::$stressOptions) . "']";
	    parent::__construct($objectName, $translator, 'Sportsman', 'Title');
		$this->doNotEmpty = ['displayeddate', 'synchrostart', 'synchroend'];
		
		$this->setGridWidget('sptsessions', 'startdate', 'startdate');
		$tVolume = $this->tr('volume'); $tStress = $this->tr('stress'); $tLoad = $this->tr('load'); $tIntensity = $this->tr('intensity'); $tWeekOfTheYear = $this->tr('weekoftheyear'); $tDistance = $this->tr('distance');
		$tElevationGain = $this->tr('elevationgain'); $tPerceivedEffort = $this->tr('perceivedeffort'); $tSensations = $this->tr('sensations'); $tMood = $this->tr('mood'); 
		$tFatigue = $this->tr('fatigue'); $tDayOfWeek = $this->tr('dayofweek'); $tDateOfDay = $this->tr('dateofday');
		$qtPerceivedEffort = $this->tr('perceivedeffort', 'escapeSQuote');
		$tDaysOfWeek = [];
		foreach(Dutl::daysOfWeek as $day){
		    $tDaysOfWeek[] = $this->tr($day);
		}
		$tDaysOfWeek = '["' . implode('","', $tDaysOfWeek) . '"]';
        $loadChartLocalActionString = <<<EOT
	tWidget.plots.week.values = dutils.difference(tWidget.form.valueOf('fromdate'), newValue, 'week') + 1;
	tWidget.chart.addPlot('week', tWidget.plots.week);
	tWidget.chart.render();
	return true;
EOT;
        $weekLoadChartLocalActionString = <<<EOT
    dojo.ready(function(){
        var date = new Date(sWidget.form.valueOf('displayeddate')), weekLoadChartWidget = sWidget.form.getWidget('weekloadchart'), dayDate, chartItem, chartData = [], normalizationVolume = 2,
            gridWidget = sWidget.form.getWidget('sptsessions'), dayType = weekLoadChartWidget.get('daytype'), filter = new gridWidget.store.Filter(), daysOfWeek = $tDaysOfWeek;
        for (i = 1; i <= 7; i++){
          dayDate = dutils.formatDate(dutils.getDayOfWeek(i, date));
          chartItem = {day: dayType === 'dayofweek' ? daysOfWeek[i-1] : dayDate, load: 0, intensity: 0, volume: 0, stress: 0};
          gridWidget.collection.filter(filter.eq('startdate', dayDate).ne('mode', 'performed')).forEach(function(item){
            if (item.sport !== 'rest'){
                chartItem.volume = dutils.seconds(item.duration, 'time') / 60; chartItem.intensity = $intensityOptionsString.indexOf(item.intensity) + 1; chartItem.load = chartItem.intensity * chartItem.volume/60/normalizationVolume;
                chartItem.stress = $stressOptionsString.indexOf(item.stress) + 1;
            }
          });
          chartItem.loadTooltip = '$tLoad: ' + chartItem.load;
          chartItem.volumeTooltip = chartItem.volume + ' ' + 'minutes';
          chartItem.intensityTooltip = '$tIntensity: ' + chartItem.intensity;
          chartItem.stressTooltip = '$tStress: ' + chartItem.stress;
          chartData.push(chartItem);
        }
        weekLoadChartWidget.set('value', {store: chartData, axes: {x: {title: dayType === 'dayofweek' ? '$tDayOfWeek' : '$tDateOfDay'}}});
    });
    return true;
EOT;
        $weekPerformedLoadChartLocalActionString = <<<EOT
	dojo.ready(function(){
	var date = new Date(sWidget.form.valueOf('displayeddate')), dayDate, chartItem, chartData = [],
	    gridWidget = sWidget.form.getWidget('sptsessions'), filter = new gridWidget.store.Filter();
	for (i = 1; i <= 7; i++){
	  dayDate = dutils.formatDate(dutils.getDayOfWeek(i, date));
	  chartItem = {day: dayDate, distance: 0, elevationgain: 0, volume: 0, perceivedEffort: 0, sensations: 0, mood: 0, fatigue: 0};
	  gridWidget.collection.filter(filter.eq('startdate', dayDate).eq('mode', 'performed')).forEach(function(item){
	    if (item.sport !== 'rest'){
	       var duration = dutils.seconds(item.duration, 'time') / 60;
           chartItem.volume = duration; chartItem.distance = item.distance; chartItem.elevationgain = item.elevationgain / 10;chartItem.perceivedEffort = Number(item.perceivedEffort) || 5;
	       chartItem.sensations = Number(item.sensations) || 5; chartItem.mood = Number(item.mood) || 5;
	       chartItem.fatigue = 11 - (Number(item.sensations || 5) + Number(item.mood || 5))/2;
        }
	  });
	  chartItem.distanceTooltip = '$tDistance: ' + chartItem.distance + ' km';
	  chartItem.volumeTooltip = chartItem.volume + ' ' + 'minutes';
	  chartItem.elevationGainTooltip = '$tElevationGain: ' + chartItem.elevationgain * 10 + ' m';
	  chartItem.perceivedEffortTooltip = '$qtPerceivedEffort: ' + chartItem.perceivedEffort;
	  chartItem.sensationsTooltip = '$tSensations: ' + chartItem.sensations;
	  chartItem.moodTooltip = '$tMood: ' + chartItem.mood;
	  chartItem.fatigueTooltip = '$tFatigue: ' + chartItem.fatigue;
	  chartData.push(chartItem);
	}
	sWidget.form.getWidget('weekperformedloadchart').set('value', {store: chartData, axes: {x: {title: 'date'}}});
	});
	return true;	
EOT;
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
		            //'storeArgs' => ['data' => [['id' => 'weekoftheyear', 'name' => $tWeekOfTheYear], ['id' =>  'weekofprogram', 'name' =>  $this->tr('weekofprogram')]]]],
		            'storeArgs' => ['data' => $idPropertyStoreData]],
		        'showTable' => ['att' =>  'showTable', 'type' => 'StoreSelect', 'name' => $this->tr('showTable'),
		        'storeArgs' => ['data' => Utl::idsNamesStore(['yes', 'no'], $this->tr)]],
		    'tableWidth' => ['att' => 'tableWidth', 'type' => 'NumberUnitBox', 'name' => $this->tr('tableWidth'),
		        'units' => [['id' => '', 'name' => ''], ['id' => 'auto', 'name' => 'auto'], ['id' => '%', 'name' => '%'], ['id' => 'em', 'name' => 'em'], ['id' => 'px', 'name' => 'px']]]
		];};
		$programLoadChartIdPropertyStoreData = [['id' => 'weekoftheyear', 'name' => $tWeekOfTheYear], ['id' =>  'weekofprogram', 'name' =>  $this->tr('weekofprogram')]];
		$weekLoadChartIdPropertyStoreDataDescription = [['id' => 'dayofweek', 'name' => $tDayOfWeek], ['id' =>  'dateofday', 'name' =>  $tDateOfDay]];
		$plannedLoadChartDescription = function ($chartName/*loadchart' or 'weeklyloadchart'*/, $idProperty /*'week' or 'day'*/, $idPropertyType, $sortAttribute/*'weekof' or 'dayofweek'*/, $xTitle/*$tWeekOfTheYear or $tDayOfWeek*/,
		                                         $timeUnit, $customizableAtts)
		  use ($tIntensity, $tLoad, $tStress, $tVolume, $tWeekOfTheYear, $weekLoadChartLocalActionString)  {
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
		        'onWatchLocalAction' => [$idProperty.'type' => [
		            $chartName => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $idProperty === 'day' ? $weekLoadChartLocalActionString : "Pmg.setFeedback('savecustomforeffect', null, null, true); return true;"]]
		        ]],
		        'customizableAtts' => $customizableAtts
		    ]]];
		};
		$performedLoadChartDescription = function($chartName, $idProperty, $idPropertyType, $sortAttribute, $xTitle, $customizableAtts) use ($tDistance, $tVolume, $tElevationGain, $tPerceivedEffort, $tSensations, $tMood, $tFatigue){
		  return ['type' => 'chart', 'atts' => ['edit' => [
		    'title' => $this->tr($chartName), 'idProperty' => $idProperty, 'kwArgs'	 => ['sort'=> [['attribute' => $sortAttribute, 'descending' => false]]],
		    'style' => ['width' => '700px'],
		    //'chartStyle' => ['height' => '300px'],
		    'chartHeight' => '300px',
		    'showTable' => 'no',
		    'tableAtts' => [
		        'columns' => [$idProperty => ['label' => $this->tr($idProperty), 'field' => $idProperty, 'width' => 65], 'distance' => ['label' => $tDistance . ' (km)', 'field' => 'distance', 'width' => 60],
		            'elevationgain' => ['label' => $tElevationGain . ' (dam)', 'field' => 'elevationgain', 'width' => 60], 'volume' => ['label' => $tVolume . ' (minutes)', 'width' => 60],
		            'perceivedEffort' => ['label' => $tPerceivedEffort, 'width' => 60], 'sensations' => ['label' => $tSensations, 'width' => 60], 
		            'mood' => ['label' => $tMood, 'width' => 60], 'fatigue' => ['label' => $tFatigue, 'width' => 60]]
		    ],
		    ($idProperty.'type') => $idPropertyType,
		    'axes' =>  [
		        'x'   => ['title' => $xTitle, 'titleOrientation' => 'away', 'titleGap' => 5, 'labelCol' => $idProperty, 'majorTicks' => true, 'majorTickStep' => 1, 'minorTicks' => false],
		        'y1' => ['title' => $tVolume . '(' . $this->tr('minute') . 's) ' . $tDistance . ' (km) & ' . $tElevationGain . ' (dam)', 'vertical' => true, 'min' => 0/*, 'max' => 500*/],
		        'y2' => ['title' => $tPerceivedEffort . ' & ' . $tSensations . ' & ' . $tMood . ' & ' . $tFatigue, 'vertical' => true, 'leftBottom' => false, 'min' => 0, 'max' => 10],
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
		        $tSensations => [ 'value' => ['y' => 'sensations', 'text' => $idProperty, 'tooltip' => 'sensationsTooltip'], 'options' => ['plot' => 'lines']],
		        $tMood => [ 'value' => ['y' => 'mood', 'text' => $idProperty, 'tooltip' => 'moodTooltip'], 'options' => ['plot' => 'lines']],
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
		    'weekloadchart' => $plannedLoadChartDescription('weekloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfWeek, 'minute', $loadChartCustomization('day', $weekLoadChartIdPropertyStoreDataDescription)),
		    'performedloadchart' => $performedLoadChartDescription('performedloadchart', 'week', 'weekoftheyear', 'weekof', $tWeekOfTheYear, $loadChartCustomization('week', $programLoadChartIdPropertyStoreData)),
		    'weekperformedloadchart' => $performedLoadChartDescription('weekperformedloadchart', 'day', 'dateofday', 'dayofweek', $tDayOfWeek, $loadChartCustomization('day', $weekLoadChartIdPropertyStoreDataDescription)),
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
				    'renderCallback' => "if (rowData.mode === 'performed'){domstyle.set(node, 'fontStyle', 'italic');}"
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
