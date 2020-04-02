<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Collab\Calendars\Entries;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {//dateTimeString(fromDateString, durationString, toDateString){

	function __construct($objectName, $translator=null){
		parent::__construct($objectName, $translator, 'Parent', 'Description');
		$this->sendOnSave = $this->sendOnDelete = ['updated', 'googlecalid', 'rogooglecalid'];
		array_push($this->mustGetCols, 'startdatetime', 'duration', 'enddatetime');
		$customDataWidgets = [
            'id' => ['atts' => ['storeedit' => ['onClickFilter' => ['id', 'googlecalid']], 'overview'  => ['onClickFilter' => ['id', 'googlecalid']]]],
			'name' => ['atts' => ['edit' =>  ['style' => ['width' => '20em;']]],],
            'googlecalid' => ViewUtils::textBox($this, 'Googlecalid'),
            'rogooglecalid' => ViewUtils::textBox($this, 'Originalgooglecalid', ['atts' => ['edit' => ['disabled' => true, 'hidden' => true], 'storeedit' => ['disabled' => true, 'hidden' => true]]]),
			'startdatetime' => ViewUtils::ISODateTimeBoxDataWidget($this, 'Begins at', ['atts' => [
						'edit' => [
							'TZArgs' => false,
							'onChangeLocalAction' => [
								'enddatetime'  => ['value' => "if (!newValue){return '';}else{return dutils.dateTimeString(newValue, sWidget.valueOf('#duration'), sWidget.valueOf('#enddatetime'))}" ]
							],
						],
						'storeedit' => ['formatType' => 'datetime'],
						'overview' => ['formatType' => 'datetime'],
					],
				]	
			),
			'duration'		  =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration', ['atts' => [
						'edit' => [
							'onChangeLocalAction' => [
								'enddatetime'  => ['value' => "if (!newValue){return '';}else{return dutils.dateTimeString(sWidget.valueOf('#startdatetime'), newValue, sWidget.valueOf('#enddatetime'))}" ]
							],
						],
						 'storeedit' => ['formatType' => 'numberunit'],
						'overview' => ['formatType' => 'numberunit'],
					],
				]
			),
			'enddatetime'   => ViewUtils::ISODateTimeBoxDataWidget($this, 'Ends at', ['atts' => [
						'edit' => [
								'TZArgs' => false,
								'onChangeLocalAction' => [
									'duration'  => ['value' => "if (!newValue){return '';}else{return dutils.durationString(sWidget.valueOf('#startdatetime'), newValue, sWidget.valueOf('#duration'))}" ]
								],
							],
						'storeedit' => ['formatType' => 'datetime'],
						'overview' => ['formatType' => 'datetime'],
					],
				]
			),
			'periodicity' => ViewUtils::numberUnitBox('timeInterval', $this, 'Periodicity'),
			'lateststartdatetime'   => ViewUtils::ISODateTimeBoxDataWidget($this, 'Latest occurrence start', ['atts' => ['edit' => ['TZArgs' => true]]]),
				'allday' => viewUtils::storeSelect('allday', $this, 'Allday'),
			'participants' => ViewUtils::JsonGrid($this, 'Participants', [
					'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
					'participants' => ViewUtils::objectSelect($this, 'participants', 'people'),
				],
				['atts' => ['edit' => ['sort' => [['property' => 'rowId', 'descending' => false]],]]]
			),
			'backgroundcolor' => viewUtils::colorPickerTextBox($this, 'BackgroundColor'),
		];
		$this->customize($customDataWidgets, [], ['grid' => ['participants'], 'post' => ['selected']], ['participants' => []]);
	}	
}
?>

