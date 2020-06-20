<?php

namespace TukosLib\Objects\Collab\Calendars;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Collab\Calendars\CalendarsViewUtils;
//use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

	use CalendarsViewUtils;

    function __construct($objectName, $translator=null){

    	parent::__construct($objectName, $translator, 'Parent', 'Description');
		array_push($this->mustGetCols, 'sources', 'periodstart', 'periodend', 'displayeddate', 'calendarsentries');
    	$this->doNotEmpty = ['displayeddate'];
    	$this->setGridWidget('calendarsentries');

    	$this->dataWidgets['parentid']['atts']['edit']['onChangeLocalAction'] = ['parentid' => ['localActionStatus' =>
    			"var changedSessionsEntries = sWidget.form.hasChanged('sessionsentries');\n" .
    			"if (changedSessionsEntries){\n" .
    			"	Pmg.setFeedback('" . $this->tr('Unsavedsessionsentries') . "', '', '', true);\n" .
    			"	return false;\n" .
    			"}else{\n" .
    			"	var sessionsEntries = sWidget.form.getWidget('sessionsentries'), calendarsEntries = sWidget.form.getWidget('calendarsentries'), store = calendarsEntries.store, filter = new store.Filter();\n" .
    			"	sessionsEntries.set('value', '');\n" .
    			"	var newSessionsEntriesCollection = store.filter(filter.eq('parentid', newValue));\n" .
    			"	sessionsEntries.noNotifyWidgets = true;\n" .
    			"	newSessionsEntriesCollection.forEach(function(item){\n" .
    			"		lang.setObject('connectedIds.sessionsentries', item.idg, item);\n" .
    			"		sessionsEntries.set('notify', {action: 'create', item: item, sourceWidget: calendarsEntries});\n" .
    			"	});\n" .
    			"	sessionsEntries.noNotifyWidgets = false;\n"	.
    			"	return true;\n" .
    			"}"
    	]];
    	 
        $this->customize($this->customDataWidgets(), $this->subObjects(), ['grid' => ['calendar', 'sources', 'displayeddate'], 'get' => ['displayeddate'], 'post' => ['displayeddate', 'sessionsentries']], ['sources' => []]);
    }    

    protected function subObjects(){
		$entriesToSessions = Utl::identityMapping(
			['id', 'parentid', 'name', 'comments', 'contextid', 'permission', 'grade', 'updated', 'updator', 'created', 'creator', 'startdatetime', 'duration', 'enddatetime', 'googlecalid', 'rogooglecalid', 'allday', 'backgroundcolor']
		);
    	return [
    			'calendarsentries' => [
    					'atts' => [
    							'title' => $this->tr('Appointments'), 'maxHeight' => '300px', 'storeType' => 'LazyMemoryTreeObjects', 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdatetime', 'endDateTimeCol' => 'enddatetime',
    							'dndParams' => ['copyOnly' => true, 'selfAccept' => false],
    							'onChangeNotify' => [
    									'calendar' => ['startdatetime' => 'startTime', 'duration' => 'duration',  'enddatetime' => 'endTime',  'allday' => 'allDay', 'name' => 'summary', 'id' => 'objectId', 'comments' => 'comments',
    											'backgroundcolor' => 'backgroundcolor', 'parentid' => 'parentid', 'googlecalid' => 'googlecalid' ],
    									'sessionsentries' => $entriesToSessions,
    							],
    							'onChangeNotifyDirectives' => [
    									'sessionsentries' => ['create' => ['targetFilter' => true], 'add' => ['targetFilter' => true, 'forceNotify' => true], 'update' => ['targetFilter' => true]]
    							],
    							'onDropMap' => [
    									'templates' => ['fields' => [/*'parentid' => 'parentid', */'allday' => 'allday', 'name' => 'name', 'comments' => 'comments', 'startdatetime' => 'startdatetime', 'duration' => 'duration', 'enddatetime' => 'enddatetime',
    											'periodicity' => 'periodicity', 'lasteststartdatetime' => 'lasteststartdatetime', 'backgroundcolor' => 'backgroundcolor']],
    							],
    							'onWatchLocalAction' => ['allowApplicationFilter' => ['calendarsentries' => $this->dateChangeGridLocalAction("tWidget.valueOf('displayeddate')", 'tWidget', 'newValue')]],
    					],
    					'initialRowValue' => ['duration' => '[1, "hour"]'],
    					'filters' => ['#sources' => '@sources', [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]],
    							'enddatetime' => ['>', '@periodstart'], 'startdatetime' =>  ['<', '@periodend'],

    							'&initSource' =>
	    							"var sources = grid.form.getWidget('sources'), collection = sources.collection, idp = collection.idProperty, dirty = sources.dirty;\n" .
	    							"console.log('in filter for initializing source');\n" .
	    							"collection.fetchSync().some(function(sourceItem){\n" .
	    								"var idv = sourceItem[idp], dirtyItem = dirty[idv] || {};\n" .
	    								"if (dirtyItem.hasOwnProperty('selected') ? dirtyItem.selected : sourceItem.selected){\n" .
	    									"if((dirtyItem.source || sourceItem.source)=== 'tukos'){\n" .
	    										"item.parentid = dirtyItem.hasOwnProperty('tukosparent') ? dirtyItem.tukosparent : sourceItem.tukosparent;\n" .
	    									"}else{\n" .
	    										"item.googlecalid = dirtyItem.hasOwnProperty('googleid') ? dirtyItem.googleid : sourceItem.googleid;\n" .
	    									"}\n" .
	    									"return true;\n" .
	    								"}\n" .
	    							"});\n;"

    					]
    			],
    			
        		'sessionsentries' => [
        			'object' => 'calendarsentries',
	        		'atts' => [
	        				'title' => $this->tr('Sessions'), 'maxHeight' => '700px', 'storeType' => 'LazyMemoryTreeObjects',
	        				'dndParams' => ['copyOnly' => true, 'selfAccept' => false],
	        				'onChangeNotify' => [
	        						'calendarsentries' => $entriesToSessions,
	        				],
	        				'onDropMap' => [
	        						'templates' => ['fields' => Utl::identityMapping(['name', 'comments', 'startdatetime', 'duration', 'backgroundcolor'])],
	        				],
	        				'onDropCondition' =>
	        				"(function(sGrid, tGrid){\n" .
	        					"if (tGrid.form.valueOf('parentid')){\n" .
	        						"return true;\n" .
	        					"}else{\n" .
	        						"Pmg.setFeedback('" . $this->tr('Needprescription') . "', '', '', true);\n" .
	        						"return false;\n" .
	        					"}\n" .
	        				"})",
	        		],
	        		'filters' => ['parentid' => '@parentid', [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]],],
	        		'noServerGet' => true,
	        	],
    			
    			'templates' => [
					'object' => 'calendarsentries',
					'atts' => [
						'title' => $this->tr('Templates'), 'storeType' => 'LazyMemoryTreeObjects',  'colspan' => 1, 'noSendOnSave' => ['selected'],
						'dndParams' => ['copyOnly' => true, 'selfAccept' => false], 
				        'columns' => ['selected' => Widgets::description(viewUtils::checkBox($this, 'Selected', ['atts' => ['edit' => [
							'onChangeLocalAction' => ['selected' => ['localActionStatus' => [
								"if (newValue){\n" .
									"var grid = sWidget.grid, collection = grid.collection, idp = collection.idProperty, dirty = grid.dirty, rowValues = grid.clickedRowValues(), calendarEntries = grid.form.getWidget('calendarsentries');\n" .
									"console.log('newValue is true: ' + newValue);\n" .
									"collection.fetchSync().forEach(function(item){\n" .
										"var idv = item[idp], dirtyItem = dirty[idv];\n" .
										"if ((dirtyItem && dirtyItem.hasOwnProperty('selected') && dirtyItem.selected) || item.selected){\n" .
											"grid.updateDirty(idv, 'selected', false);\n" .
										"}\n" .
									"})\n;" .
									"calendarEntries.set('initialRowValue', {duration: rowValues.duration, backgroundcolor: rowValues.backgroundcolor})\n;" .
								"}\n" .
								"return true;\n"
						]]]]]]), false),]],
					'filters' => ['grade' => 'TEMPLATE'],
					'sendOnHidden' => ['contextid']
    			],
    		];
    }
	protected function customDataWidgets(){
		return [
				'sources' => ViewUtils::jsonGrid($this, 'Calendars', [
						'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
						'selected' => viewUtils::checkBox($this, 'Selected', ['atts' => [/*'storeedit' => ['editOn' => 'click'], */'edit' => [
								'onChangeLocalAction' => ['selected' => ['localActionStatus' => [
										"if (newValue){\n" .
										"var grid = sWidget.grid, collection = grid.collection, idp = collection.idProperty, dirty = grid.dirty;\n" .
										"console.log('newValue is true: ' + newValue);\n" .
										"collection.fetchSync().forEach(function(item){\n" .
										"var idv = item[idp], dirtyItem = dirty[idv];\n" .
										"if ((dirtyItem && dirtyItem.hasOwnProperty('selected') && dirtyItem.selected) || item.selected){\n" .
										"grid.updateDirty(idv, 'selected', false);\n" .
										"}\n" .
										"})\n;" .
										"}\n" .
										"return true;\n"
								]]],
						]]]),
						'visible' => viewUtils::checkBox($this, 'Visible'),
						
						'source'    => ViewUtils::storeSelect('source', $this, 'Source', null, ['atts' => ['storeedit' => ['width' => 100]]]),
						'tukosparent'  => ViewUtils::objectSelectMulti($this->model->idColsObjects['parentid'], $this, 'Tukosparent', ['atts' => [
								'edit' => ['onChangeLocalAction' => [
										'source' => ['value' => "return newValue ? 'tukos' : '';" ],
										'googleid' => ['value' => "return '';"],
										'backgroundColor' => ['value' => "return '';"
										]]],
								'storeedit' => ['width' => 200]
						]]),
						'googleid' => ViewUtils::restSelect($this, 'Googlecalendarid', 'calendars', ['atts' => [
								'edit' => [
										'storeArgs' => ['params' => ['getOne' => 'calendarSelect', 'getAll' => 'calendarsSelect']],
										'onChangeServerAction' =>[
												'inputWidgets' => ['sources', 'calendarentries'],
												'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getGoogleCalendarIdChanged'])]]
										],
										'onChangeLocalAction' => [
												'source' => ['value' => "return newValue ? 'google' : '';" ],
												'tukosparent' => ['value' => "return '';"],
												'backgroundColor' => ['value' => "return '';"
												]],
								],
								'storeedit' => ['width' => 400]
						]]),
						'backgroundcolor' => viewUtils::colorPickerTextBox($this, 'BackgroundColor'),
					],
					['atts' => ['edit' => ['sort' => [['property' => 'rowId', 'descending' => false]]]]]
				),
				'periodstart' => ViewUtils::tukosDateBox($this, 'Periodstart', ['atts' => ['edit' => ['onWatchLocalAction' => ['value' => [
                        'weeksbefore' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]],
                ]]]]]),
				'periodend' => ViewUtils::tukosDateBox($this, 'Periodend', ['atts' => ['edit' => ['onWatchLocalAction' => ['value' => [
                        'weeksafter' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return '';" ]],
                ]]]]]),
				'weeksbefore' => ViewUtils::tukosNumberBox($this, 'Weeksbefore', ['atts' => ['edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => [
                        'periodstart' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(1, new Date()), 'week', -newValue));" ]],
                ]]]]]),
				'weeksafter' => ViewUtils::tukosNumberBox($this, 'Weeksafter', ['atts' => ['edit' => ['style' => ['width' => '3em'], 'onWatchLocalAction' => ['value' => [
                        'periodend' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "return dutils.formatDate(dutils.dateAdd(dutils.getDayOfWeek(7, new Date()), 'week', newValue));" ]],
                ]]]]]),
				'displayeddate' => $this->displayedDateDescription(),
				'calendar' => $this->calendarWidgetDescription(['atts' => ['edit' => [
					'customization' => [
						'calendars' => ['sources' => 'sources', 'style' => ['backgroundColor' => ['field' => 'backgroundcolor']]],
						'items' => ['style' => ['backgroundColor' => ['field' => 'backgroundcolor', 'defaultValue' => 'calendars']]],
					],
				]]]),
				//'worksheet' => ['atts' => ['edit' => ['dndParams' => ['accept' => ['dgrid-row', 'quarterhour']]]]],
		];
	}
}
?>
