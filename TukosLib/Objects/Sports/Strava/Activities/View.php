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
            'stravaid' => ViewUtils::htmlContent($this, 'Stravaid', ['atts' => ['edit' => ['disabled' => true], 'storeedit' => ['hidden' => true]],
                'objToEdit' => ['stravaLink' => ['class' => $this]], 'objToStoreEdit' => ['stravaLink' => ['class' => $this]], 'objToOverview' => ['stravaLink' => ['class' => $this]]]),
        ];
        $this->customize($customDataWidgets);
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

