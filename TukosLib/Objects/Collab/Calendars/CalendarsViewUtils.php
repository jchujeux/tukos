<?php
namespace TukosLib\Objects\Collab\Calendars;

use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;

trait CalendarsViewUtils {
	
	
	
	protected function setGridWidget($name, $startDateTimeProperty, $endDateTimeProperty){
		$this->gridWidgetName = $name;
		$this->startDateTimeProperty = $startDateTimeProperty;
		$this->endDateTimeProperty = $endDateTimeProperty;
		$this->dateChangeGridWidgetLocalAction = ['collection' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->gridWidgetFilterActionString('newValue', 'tWidget', 'tWidget.allowApplicationFilter')]];
		$this->allowApplicationFilterChangeGridWidgetLocalAction = ['collection' => ['triggers' => ['server' => false, 'user' => true], 'action' => $this->gridWidgetFilterActionString("tWidget.valueOf('displayeddate')", 'tWidget', 'newValue')]];
		$this->gridWidgetOpenAction = 
			"var tWidget = this.getWidget('" . $this->gridWidgetName . "'), tDate = this.valueOf('displayeddate');" .
			"tWidget.set('collection', function(){\n" . $this->gridWidgetFilterActionString('tDate', 'tWidget', 'tWidget.allowApplicationFilter') . "}())";
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
						'displayeddate' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => 
							"var newDisplayedDate = dutils.formatDate(newValue), lowBound = sWidget.form.valueOf('" . $start . "'), highBound = sWidget.form.valueOf('" . $end . "');\n" .
							"if ((lowBound && newDisplayedDate < lowBound) || (highBound && newDisplayedDate > highBound)){\n" .
							"	Pmg.setFeedback('" . $this->tr('beyondcalendarrange') . "', '', '\\n', true); \n" .
							"}\n" .
							"return newDisplayedDate;"
						]],
						$this->gridWidgetName => $this->dateChangeGridWidgetLocalAction,
				]],
			]]],
			$custom
		);
	}
	
	protected function  displayedDateDescription($custom = []){
		return Utl::array_merge_recursive_replace(
			ViewUtils::tukosDateBox($this, 'displayeddate', ['atts' => ['edit' => [
				   'value' => date('Y-m-d'),
						'onWatchLocalAction' => ['value' => [
							'calendar' => ['date' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return newValue;" ]],
							$this->gridWidgetName => $this->dateChangeGridWidgetLocalAction,
						]],
			]]]),
			$custom);
	}
	
	private function gridWidgetFilterActionString($dateValue, $targetWidget, $allowApplicationFilterValue){
		return
			"var date = $dateValue, store = $targetWidget.store, allowApplicationFilter = $allowApplicationFilterValue;\n" .
			"if (allowApplicationFilter === 'yes'){\n" .
				"var mondayStamp = dutils.getDayOfWeek(1, typeof date === 'string' ? dutils.parseDate(date) : dutils.parseDate(dutils.formatDate(date)));\n" .
				"var nextMondayStamp = dojo.date.add(mondayStamp, 'week', 1);\n" .
				"store.applicationCollectionFilter = (new store.Filter()).gte('$this->startDateTimeProperty', dutils.toISO(mondayStamp)).lt('$this->endDateTimeProperty',dutils.toISO(nextMondayStamp));\n" .
			"}else{\n" .
				"store.applicationCollectionFilter = new store.Filter();\n" .
			"}" .
			"return store.getRootCollection();\n";
	}
}
?>