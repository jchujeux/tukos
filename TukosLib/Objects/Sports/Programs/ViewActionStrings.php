<?php
namespace TukosLib\Objects\Sports\Programs;


trait ViewActionStrings{

  protected function dateChangeLoadChartLocalAction(){
      return <<<EOT
      tWidget.plots.week.values = dutils.difference(dutils.getDayOfWeek(1, new Date(tWidget.form.valueOf('fromdate'))), newValue, 'week') + 1;
	tWidget.chart.addPlot('week', tWidget.plots.week);
	try{
        tWidget.chart.render();
    }catch(err){
        console.log('Error rendering chart in localChartAction for widget: ' + tWidget.widgetName);
    }
	return true;
EOT;
  }
  protected function loadChartLocalAction($chartWidgetName){
      return <<<EOT
var form = sWidget.form, grid = form.getWidget('sptsessions');
grid.loadChartUtils.setProgramLoadChartValue(form, '$chartWidgetName');
return true;
EOT
      ;
  }
  protected function weekLoadChartLocalAction($chartWidgetName){
      return <<<EOT
var form = sWidget.form, grid = form.getWidget('sptsessions');
grid.loadChartUtils.setWeekLoadChartValue(form, '$chartWidgetName');
return true;
EOT
      ;
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
