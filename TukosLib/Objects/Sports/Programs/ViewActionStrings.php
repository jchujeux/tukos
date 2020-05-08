<?php
namespace TukosLib\Objects\Sports\Programs;


trait ViewActionStrings{

  protected function loadChartLocalAction(){
      return <<<EOT
	tWidget.plots.week.values = dutils.difference(tWidget.form.valueOf('fromdate'), newValue, 'week') + 1;
	tWidget.chart.addPlot('week', tWidget.plots.week);
	try{
        tWidget.chart.render();
    }catch(err){
        console.log('Error rendering chart in localChartAction for widget: ' + tWidget.widgetName);
    }
	return true;
EOT;
  }
  protected function weekLoadChartLocalAction($plannedOrPerformed, $presentCols, $tDaysOfWeek, $tDayOfWeek, $tDateOfDay, $chartColsTLabelString){
      if ($plannedOrPerformed === 'planned'){
          $weekLoadChart = 'weekloadchart';
          $sessionsFilter = 'ne';
      }else{
          $weekLoadChart = 'weekperformedloadchart';
          $sessionsFilter = 'eq';
      }
      $presentColsString = json_encode($presentCols);
      return <<<EOT
  dojo.ready(function(){
	var date = new Date(sWidget.form.valueOf('displayeddate')), weekLoadChartWidget = sWidget.form.getWidget('$weekLoadChart'), dayDate, chartItem, chartData = [], normalizedDuration = 120,
	    gridWidget = sWidget.form.getWidget('sptsessions'), dayType = weekLoadChartWidget.get('daytype'), filter = new gridWidget.store.Filter(), daysOfWeek = $tDaysOfWeek, presentCols = $presentColsString, colsTLabel = $chartColsTLabelString;
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
  }
  protected function synchroStartLocalAction($displayeddate, $synchnextmonday){
      $displayedDateValue = $displayeddate === "newValue" ? "newValue" : ("sWidget.valueOf('$displayeddate')");
      $synchNextMondayValue = $synchnextmonday === 'newValue' ? "newValue === 'YES'" : ("sWidget.valueOf('$synchnextmonday') === 'YES'");
      return <<<EOT
	dojo.ready(function(){
    	var synchroWeeksBefore = sWidget.valueOf('synchroweeksbefore') || 0, synchroWeeksAfter = sWidget.valueOf('synchroweeksafter') || 0,
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
  }
}
?>
