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
		parent::__construct($objectName, $translator, 'Sportsman', 'Title');
		$this->doNotEmpty = ['displayeddate'];
		
		$this->setGridWidget('sptsessions', 'startdate', 'startdate');
		$tVolume = $this->tr('volume'); $tStress = $this->tr('stress'); $tLoad = $this->tr('load'); $tIntensity = $this->tr('intensity'); $tWeekOfTheYear = $this->tr('weekoftheyear');
		
		$loadChartLocalActionString =  
			"tWidget.plots.week.values = dutils.difference(tWidget.form.valueOf('fromdate'), newValue, 'week') + 1;" .
			"tWidget.chart.addPlot('week', tWidget.plots.week);" .
			"tWidget.chart.render();" .
			"return true;";
		$synchroStartLocalActionString = function($displayeddate, $synchnextmonday){
			return 
				"var synchroWeeksBefore = parseInt(sWidget.valueOf('#synchroweeksbefore')), synchroWeeksAfter = parseInt(sWidget.valueOf('#synchroweeksafter')), " .
					"displayeddate = " . ($displayeddate === 'newValue' ? "newValue" : ("sWidget.valueOf('" . $displayeddate . "')")) . ", form = sWidget.form;" .
				"if (Number.isInteger(synchroWeeksBefore)){" .
					"form.getWidget('synchrostart').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date(displayeddate)), 'week', -synchroWeeksBefore)));" .
				"}" .
				"if (Number.isInteger(synchroWeeksAfter)){" .
					"var nextMonday = " . ($synchnextmonday === 'newValue' ? "newValue === 'YES'" : ("sWidget.valueOf('" . $synchnextmonday . "') === 'YES'")) . ";" .
					"form.getWidget('synchroend').set('value', dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(nextMonday ? 1 : 7, new Date(displayeddate)), 'week', nextMonday ? synchroWeeksAfter + 1 : synchroWeeksAfter)));" .
				"}" .
				"return true;";
		};
		
		$customDataWidgets = [
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
			'synchnextmonday' => viewUtils::storeSelect('synchnextmonday', $this, 'Synchnextmonday', ['atts' => ['edit' => ['style' => ['width' => '4em'], 'onWatchLocalAction' => ['value' => [
					'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $synchroStartLocalActionString('#displayeddate', 'newValue'),]],
			]]]]]),
			'questionnairetime'  =>  ViewUtils::timeStampDataWidget($this, 'QuestionnaireTime', ['atts' => ['edit' => ['disabled' => true]]]),
			'loadchart' => ['type' => 'chart', 'atts' => ['edit' => [
				'title' => $this->tr('loadchart'), 'idProperty' => 'week', 'kwArgs'	 => ['sort'=> [['attribute' => 'weekof', 'descending' => false]]],
				'style' => ['width' => '700px'],
				'chartStyle' => ['height' => '300px'], 
				'showTable' => 'no',
				'tableAtts' => [
					'maxHeight' => '300px', 'minWidth' => '150px',
					'columns' => ['week' => ['label' => $this->tr('week'), 'field' => 'week', 'width' => 65], 'intensity' => ['label' => $tIntensity, 'field' => 'intensity', 'width' => 60], 
					              'stress' => ['label' => $tStress, 'field' => 'stress', 'width' => 60], 'load' => ['label' => $tLoad, 'field' => 'load', 'width' => 60], 'volume' => ['label' => $tVolume, 'width' => 60]]
				],
				'weektype' => 'weekoftheyear',
				'axes' =>  [
					'x'   => ['title' => $tWeekOfTheYear, 'titleOrientation' => 'away', 'titleGap' => 5, 'labelCol' => 'week', 'majorTicks' => true, 'majorTickStep' => 1, 'minorTicks' => false], 
					'y1' => ['title' => $tIntensity . ', ' . $tLoad . ' & ' . $tStress, 'vertical' => true, 'min' => 0, 'max' => 5],
					'y2' => ['title' => $tVolume . ' (' . $this->tr('hour') . 's)', 'vertical' => true, 'leftBottom' => false, 'min' => 0],
				],
				'plots' =>  [
					$tIntensity => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y1', 'lines' => true, 'markers' => true],
					$tVolume => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y2', 'lines' => true, 'markers' => true],
					$tStress => ['plotType' => 'Lines', 'hAxis' => 'x', 'vAxis' => 'y1', 'lines' => true, 'markers' => true],
					'columns'  => ['plotType' => 'Columns', 'hAxis' => 'x', 'vAxis' => 'y1', 'gap' => 5, 'minBarSize' => 3, 'maxBarSize' => 40],
					'week'	  => ['plotType' => 'Indicator', 'hAxis' => 'x', 'vAxis' => 'y1', 'stroke' => null, 'outline' => null, 'fill' => null, 'labels' => false, 'lineStroke' => ['color' => 'red', 'style' => 'shortDash', 'width' => 2]],
				],
			   'legend' => ['type' => 'SelectableLegend', 'options' => []],
				'series' => [
					$tIntensity => [ 'value' => ['y' => 'intensity', 'text' => 'week', 'tooltip' => 'intensityTooltip'], 'options' => ['plot' => $tIntensity]],
					$tStress	 => ['value' => ['y' => 'stress', 'text' => 'week', 'tooltip' => 'stressTooltip'], 'options' => ['plot' => $tStress]],
					$tLoad	   => ['value' => ['y' => 'load', 'text' => 'week', 'tooltip' => 'loadTooltip'], 'options' => ['plot' => 'columns']],
					$tVolume  => ['value' => ['y' => 'volume', 'text' => 'week', 'tooltip' => 'volumeTooltip'], 'options' => ['plot' => $tVolume]],
				],
				'customizableAtts' => [
					'weektype' => ['att' =>  'weektype', 'type' => 'StoreSelect', 'name' => $this->tr('weektype'),
								   'storeArgs' => ['data' => [['id' => 'weekoftheyear', 'name' => $tWeekOfTheYear], ['id' =>  'weekofprogram', 'name' =>  $this->tr('weekofprogram')]]]],
					'showTable' => ['att' =>  'showTable', 'type' => 'StoreSelect', 'name' => $this->tr('showTable'),
								   'storeArgs' => ['data' => Utl::idsNamesStore(['yes', 'no'], $this->tr)]],
				],
			]]],
			'worksheet' => ['atts' => ['edit' => ['dndParams' => ['accept' => ['dgrid-row', 'quarterhour']]/*, 'copyOnly' => true, 'selfAccept' => false*/]]],
			'calendar' => $this->calendarWidgetDescription(
				['atts' => ['edit' => [
					'columnViewProps' => ['minHours' => 0, 'maxHours' => 4],
					'style' => ['height' => '510px', 'width' => '800px'], 
					'timeMode' => 'duration', 'moveEnabled' => true,
					'customization' => ['items' => [
						 'style' => ['backgroundColor' => ['field' => 'intensity', 'map' => Sports::$intensityColorsMap, 'defaultValue' => 'Peru']],
						 'img'   => ['field' => 'sport', 'map' => Sports::$sportImagesMap, 'imagesDir' => Tfk::jsFullDir('tukos') . '/images/'],
						 'ruler' => ['field' => 'stress', 'map' => Sports::$stressOptions, 'atts' => ['minimum' => 0, 'maximum' => 4, 'showButtons' => false, 'discreteValues' => 5]],
					]],
					'onChangeNotify' => [$this->gridWidgetName => [
						'startTime' => 'startdate', 'duration' => 'duration', 'summary' => 'name', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport', 'warmup' => 'warmup', 
						'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown', 
					]],
					'onWatchLocalAction' => ['date' => [
							'loadchart' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => $loadChartLocalActionString]],
						 'synchrostart' => ['localActionStatus'=> ['triggers' => ['server' => true, 'user' => true], 'action' => $synchroStartLocalActionString('newValue', '#synchnextmonday'),]],
				]]]]], 
				'fromdate', 'todate'),
		];
	
		$subObjects = [
			'sptsessions' => [
				'atts' => [
					'title' => $this->tr('Sessions'), /*'storeType' => 'LazyMemoryTreeObjects',  */ 'allDescendants' => true, 'allowLocalFilters' => 'yes',
					'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
					'onChangeNotify' => [
						'calendar' => [
							'startdate' => 'startTime',  'duration' => 'duration',  'name' => 'summary', 'comments' => 'comments', 'intensity' => 'intensity', 'stress' => 'stress', 'sport' => 'sport', 'warmup' => 'warmup',
							'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown'
					]],
					'onDropMap' => [
                        'templates' => ['fields' => ['name' => 'name', 'comments' => 'comments', 'startdate' => 'startdate', 'duration' => 'duration'/*, 'enddate' => 'enddate'*/, 'intensity' => 'intensity', 'stress' => 'stress', 
                        	'sport' => 'sport', 'warmup' => 'warmup', 'mainactivity' => 'mainactivity', 'warmdown' => 'warmdown'
                        ]],
						'warmup' => ['mode' => 'update', 'fields' => ['warmup' => 'summary']],
						'mainactivity' => ['mode' => 'update', 'fields' => ['mainactivity' => 'summary']],
						'warmdown' => ['mode' => 'update', 'fields' => ['warmdown' => 'summary']],
					],
					'sort' => [['property' => 'startdate', 'descending' => false]],
					'onWatchLocalAction' => ['allowLocalFilters' => [
						'sptsessions' => $this->allowLocalFiltersChangeGridWidgetLocalAction
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

		$this->customize($customDataWidgets, $subObjects, [ 'grid' => ['calendar', 'displayeddate', 'loadchart'], 'get' => ['displayeddate', 'loadchart'], 'post' => ['displayeddate', 'loadchart']]);
	}	
}
?>
