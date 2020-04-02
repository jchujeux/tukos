<?php

namespace TukosLib\Objects\Physio\Protocols;

use TukosLib\Objects\Collab\Calendars\View as CalendarsView;
use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;

class View extends CalendarsView {

    function __construct($objectName, $translator=null){
        AbstractView::__construct($objectName, $translator, 'Prescription', 'Description');
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

        $customDataWidgets = $this->customDataWidgets();
        $subObjects = $this->subObjects();
		$entriesToSessions = Utl::identityMapping(
			['id', 'parentid', 'name', 'comments', 'contextid', 'permission', 'grade', 'updated', 'updator', 'created', 'creator', 'startdatetime', 'duration', 'enddatetime', 'googlecalid', 'rogooglecalid', 'allday', 'backgroundcolor']
		);
        $subObjects['calendarsentries']['atts']['onChangeNotify']['sessionsentries'] = $entriesToSessions;
        $subObjects['calendarsentries']['atts']['onChangeNotifyDirectives']['sessionsentries'] = ['create' => ['targetFilter' => true], 'add' => ['targetFilter' => true, 'forceNotify' => true], 'update' => ['targetFilter' => true]];
        $subObjects['sessionsentries'] = [
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
        ];
        
        $this->customize($customDataWidgets, $subObjects, ['grid' => ['calendar', 'sources', 'displayeddate'/*, 'sessions'*/], 'get' => ['displayeddate'], 'post' => ['displayeddate', 'sessionsentries']], ['sources' => []/*, 'sessions' => []*/]);
    }    
}
?>
