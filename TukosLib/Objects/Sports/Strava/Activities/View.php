<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Athlete', 'Description');
        $isMobile = Tfk::$registry->isMobile;
        $untranslator = new ObjectTranslator($objectName, null, 'untranslator');
        $customDataWidgets = [
            'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '30em']]],],
            'startdate' => ViewUtils::tukosDateBox($this, 'date'),
            'starttime' => ViewUtils::dateTimeBoxDataWidget($this, 'time', ['atts' => ['edit' => ['dateArgs' => false]]]),
            'gearid' => ViewUtils::htmlContent($this, 'Gearid', ['atts' => ['edit' => ['disabled' => true], 'storeedit' => ['hidden' => true]],
                'objToEdit' => ['stravaGearLink' => ['class' => $this]], 'objToStoreEdit' => ['stravaGearLink' => ['class' => $this]], 'objToOverview' => ['stravaGearLink' => ['class' => $this]]]),
            'duration'  => ViewUtils::secondsTextBox($this, 'duration', ['atts' => [
                'edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm:ss', 'clickableIncrement' => 'T00:10', 'visibleRange' => 'T01:00']],
            ]]),
            'timemoving' => ViewUtils::secondsTextBox($this, 'Timemoving'),
            'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'constraints' => $isMobile ? ['pattern' => '#000.0'] : ['pattern' => '#.##']],
                'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevationgain', ['atts' => ['edit' => ['label' => $this->tr('Elevationgain') . ' (m)', 'constraints' => $isMobile ? ['pattern' => '#0000.'] : ['pattern' => '#.#']],
                'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'sport'         => ViewUtils::storeSelect('sport', $this, 'Sport'),
            'avghr' => ViewUtils::numberTextBox($this,'Average Heart Rate', ['atts' => ['edit' => []]]),
            'avgpw' => ViewUtils::numberTextBox($this, 'Average Power', ['atts' => []]),
            'trimpavghr' => ViewUtils::numberTextBox($this, 'Heartrate_avgload', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'trimpavgpw' => ViewUtils::numberTextBox($this, 'Power_avgload', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'heartrate_avgload' => ViewUtils::numberTextBox($this, 'Heartrate_avgload', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'power_avgload' => ViewUtils::numberTextBox($this, 'Power_avgload', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'stravaid' => ViewUtils::htmlContent($this, 'Stravaid', ['atts' => ['edit' => ['disabled' => true], 'storeedit' => ['hidden' => true]],
                'objToEdit' => ['stravaLink' => ['class' => $this]], 'objToStoreEdit' => ['stravaLink' => ['class' => $this]], 'objToOverview' => ['stravaLink' => ['class' => $this]]]),
            'kpiscache' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('KpisCache'), 'keyToHtml' => 'capitalToBlank', 'hasCheckboxes' => true, 'isEditTabWidget' => true, 'checkedServerValue' => '~delete', 'onCheckMessage' => $this->tr('checkedleaveswillbedeletedonsave'),
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto']]],
                'objToEdit' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
                'editToObj' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $untranslator->tr]],
            ],
        ];
        $this->customize($customDataWidgets, [], ['grid' => ['kpiscache']]);
    }
    function stravaLink($stravaId){
        if (!empty($stravaId)){
            return "<a href=\"https://www.strava.com/activities/$stravaId\" target=\"_blank\">$stravaId</a>";
        }
    }
    function stravaGearLink($stravaGearId){
        if (!empty($stravaGearId)){
            if ($stravaGearId[0] === 'b'){
                return "<a href=\"https://www.strava.com/bikes/" . substr($stravaGearId, 1) . "\" target=\"_blank\">$stravaGearId</a>";
            }else{
                return $stravaGearId;
            }
        }
    }
}
?>

