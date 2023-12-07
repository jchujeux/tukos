<?php
namespace TukosLib\Objects\Sports\Strava\Activities;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Athlete', 'Description');
        $isMobile = Tfk::$registry->isMobile;
        $customDataWidgets = [
            'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '30em']]],],
            'startdate' => ViewUtils::tukosDateBox($this, 'date'),
            'starttime' => ViewUtils::dateTimeBoxDataWidget($this, 'time', ['atts' => ['edit' => ['dateArgs' => false]]]),
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
            'trimpavghr' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Avg_Heart_rate', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'trimpavgpw' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Avg_Power', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'heartrate_avgload' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Avg_Heart_rate', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'power_avgload' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Avg_Power', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'stravaid' => ViewUtils::htmlContent($this, 'Stravaid', ['atts' => ['edit' => ['disabled' => true], 'storeedit' => ['hidden' => true]],
                'objToEdit' => ['stravaLink' => ['class' => $this]], 'objToStoreEdit' => ['stravaLink' => ['class' => $this]], 'objToOverview' => ['stravaLink' => ['class' => $this]]]),
        ];
        $this->customize($customDataWidgets, []/*, ['edit' => $this->model->streamCols, 'grid' => $this->model->streamCols, 'get' => $this->model->streamCols, 'post' => [$this->model->streamCols]]*/);
    }
    function stravaLink($stravaId){
        if (!empty($stravaId)){
            return "<a href=\"https://www.strava.com/activities/$stravaId\" target=\"_blank\">$stravaId</a>";
        }
    }
}
?>

