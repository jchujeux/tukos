<?php
namespace TukosLib\Objects\Collab\Calendars;

use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;

trait CalendarsViewUtils {
	
	protected function dateChangeGridLocalAction($dateValue, $targetWidget, $allowApplicationFilterValue){
        return ['collection' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->gridWidgetFilterActionString($dateValue, $targetWidget, $allowApplicationFilterValue)]];
    }
    public function gridOpenAction($gridWidgetName){
        return <<<EOT
  var tWidget = this.getWidget('$gridWidgetName'), dateValue = this.valueOf('displayeddate');
  tWidget.set('collection', function(){
    {$this->gridWidgetFilterActionString('dateValue', 'tWidget', 'tWidget.allowApplicationFilter')}}());
EOT;
    }
	protected function setGridWidget($name){
		$this->gridWidgetName = $name;
	}
	protected function calendarWidgetDescription($custom = [], $start = 'periodstart', $end = 'periodend'){
		return Utl::array_merge_recursive_replace([
			'type' => 'storeCalendar', 'atts' => ['edit' => [
				'title' => $this->tr('Calendar'), 'dateInterval' => 'week', 'gridWidget' => $this->gridWidgetName,  'colspan' => 1, 'columnViewProps' => ['hourSize' => 60, 'minHours' => 6, 'maxHours' => 24],  'style' => ['height' => '750px'],
				'dndParams' => ['accept' => ['dgrid-row', 'quarterhour']], 'selectionMode' => 'multiple', 
				'onChangeNotify' => [
						$this->gridWidgetName => ['startTime' => 'startdatetime', 'endTime' => 'enddatetime', 'duration' => 'duration', 'allDay' => 'allday', 'summary' => 'name', 'comments' => 'comments'],
				],
				'onWatchLocalAction' => ['date' => [
						'displayeddate' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => <<<EOT
var newDisplayedDate = dutils.formatDate(newValue), lowBound = sWidget.form.valueOf('$start'), highBound = sWidget.form.valueOf('$end');
if ((lowBound && newDisplayedDate < lowBound) || (highBound && newDisplayedDate > highBound)){
	Pmg.setFeedback('{$this->tr('beyondcalendarrange')}', '', null, true);
}else{
    Pmg.setFeedback('');
}
return newDisplayedDate;
EOT
						]],
				    $this->gridWidgetName => $this->dateChangeGridLocalAction('newValue', 'tWidget', 'tWidget.allowApplicationFilter'),
				]],
			]]],
			$custom
		);
	}
	
	protected function  displayedDateDescription($custom = []){
		return Utl::array_merge_recursive_replace(
			ViewUtils::tukosDateBox($this, 'displayeddate', ['atts' => ['edit' => [
				'value' => date('Y-m-d'), 'forceMarkIfChanged' => true,
				'onWatchLocalAction' => ['value' => [
							'calendar' => ['date' => ['triggers' => ['server' => true, 'user' => true], 'action' => "return newValue;" ]],
						    $this->gridWidgetName => $this->dateChangeGridLocalAction('newValue', 'tWidget', 'tWidget.allowApplicationFilter'),
						]],
			]]]),
			$custom);
	}
	
	private function gridWidgetFilterActionString($dateValue, $tWidget, $allowApplicationFilterValue){
		return <<<EOT
  var date = $dateValue, allowApplicationFilter = $allowApplicationFilterValue, store = $tWidget.store, startDateTimeCol = $tWidget.startDateTimeCol, endDateTimeCol = $tWidget.endDateTimeCol;
  if (allowApplicationFilter === 'yes'){
	var mondayStamp = dutils.getDayOfWeek(1, typeof date === 'string' ? dutils.parseDate(date) : dutils.parseDate(dutils.formatDate(date)));
	var nextMondayStamp = dojo.date.add(mondayStamp, 'week', 1);
    store.applicationCollectionFilter = new store.Filter().or(new store.Filter().eq(startDateTimeCol, undefined), new store.Filter().gte(startDateTimeCol, dutils.toISO(mondayStamp)).lt(endDateTimeCol,dutils.toISO(nextMondayStamp)));
  }else{
	store.applicationCollectionFilter = new store.Filter();
  }
  return store.getRootCollection();
EOT;
	}
}
?>