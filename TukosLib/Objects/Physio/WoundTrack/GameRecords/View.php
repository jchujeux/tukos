<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameRecords;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Physio\WoundTrack\IndicatorsView;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    use IndicatorsView;
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'GameTrack', 'Description');
        $tr = $this->tr;
        $isMobile = Tfk::$registry->isMobile;
        $leftRightTdStyle = ['whiteSpace' => 'nowrap', 'verticalAlign' => 'top', 'paddingTop' => '7px', 'fontSize' => 'smaller', 'fontFamily' => 'Arial, Helvetica', 'sans-serif'];
        $gaugeAtts = ['indicatorColor' => 'black', 'height' => 30, 'maximum' => 10, 'minorTicksEnabled' => false, 'majorTickInterval' => 10, 'showValue' => true, 'gradient' => [0, '#B22222', 0.5, '#FF8C00', 1, '#7FFFD4'], 'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false];
        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => '100%', 'height' => 'auto']]]],
            'recordtype' => ViewUtils::storeSelect('recordtype', $this, 'Recordtype', [true, 'ucfirst', true, false]),
            'recorddate' => ViewUtils::tukosDateBox($this, 'Recorddate'),
            'globalsensation' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Globalsensation'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('Verygood'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Verybad'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => $gaugeAtts,
                'checkboxes' => [['title' => $this->tr('Sleep'), 'id' => 'sleep'], ['title' => $this->tr('Wellfare'), 'id' => 'wellfare'], ['title' => $this->tr('Stress'), 'id' => 'stress'], ['title' => $this->tr('Healthillness'), 'id' => 'healthillness'],
                    ['title' => $this->tr('Energylevel'), 'id' => 'energylevel'], ['title' => $this->tr('Other'), 'id' => 'other']]
            ]]],
            'environment' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Externalenvironment'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('Favorable'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Unfavorable'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => $gaugeAtts,
                'checkboxes' => [['title' => $this->tr('Professional'), 'id' => 'professional'], ['title' => $this->tr('Personal'), 'id' => 'personal'], ['title' => $this->tr('Family'), 'id' => 'family'], ['title' => $this->tr('Sports'), 'id' => 'sports'], ['title' => $this->tr('other'), 'id' => 'other']]
            ]]],
            'recovery' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Recovery'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('Totally'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Notatall'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => $gaugeAtts,
                'checkboxes' => [['title' => $this->tr('Aches'), 'id' => 'aches'], ['title' => $this->tr('Pain'), 'id' => 'pain'], ['title' => $this->tr('Energy'), 'id' => 'energy'], ['title' => $this->tr('Psychofatigue'), 'id' => 'psychofatigue'], ['title' => $this->tr('Other'), 'id' => 'other']]
            ]]],
            'previoussensation' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Previoussensation'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('Easy'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Difficult'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => $gaugeAtts,
                'checkboxes' => [['title' => $this->tr('Sleep'), 'id' => 'sleep'], ['title' => $this->tr('Wellfare'), 'id' => 'wellfare'], ['title' => $this->tr('Stress'), 'id' => 'stress'], ['title' => $this->tr('Healthillness'), 'id' => 'healthillness'],
                    ['title' => $this->tr('Energylevel'), 'id' => 'energylevel'], ['title' => $this->tr('Other'), 'id' => 'other']]
            ]]],
            'duration'  => ViewUtils::minutesTextBox($this, 'duration', ['atts' => ['edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm', 'clickableIncrement' => 'T00:10', 'visibleRange' => 'T01:00']]]]),
            'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'constraints' => $isMobile ? ['pattern' => '#000.0'] : ['pattern' => '#.##']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevationgain', ['atts' => ['edit' => ['label' => $this->tr('Elevationgain') . ' (m)', 'constraints' => $isMobile ? ['pattern' => '#0000.'] : ['pattern' => '#.#']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'elevationloss' => ViewUtils::tukosNumberBox($this, 'Elevationloss', ['atts' => ['edit' => ['label' => $this->tr('Elevationloss') . ' (m)', 'constraints' => $isMobile ? ['pattern' => '#0000.'] : ['pattern' => '#.#']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'perceivedintensity' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Globalsensation'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('Extremelylow'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Extremelyhigh'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => $gaugeAtts
            ]]],
            'intensitydetails' => ViewUtils::lazyEditor($this, 'Intensitydetails', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'perceivedstress' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Globalsensation'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('Verygood'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Verybad'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => ['indicatorColor' => 'black', 'height' => 30, 'minimum' => -5, 'maximum' => 20, 'minorTicksEnabled' => false, 'majorTickInterval' => 5, 'showValue' => true, 'gradient' => [0, '#B22222', 0.5, '#FF8C00', 1, '#7FFFD4'],
                    'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false],
                'checkboxes' => [['title' => $this->tr('Painduring'), 'id' => 'painduring'], ['title' => $this->tr('Painafter'), 'id' => 'painafter'], ['title' => $this->tr('Symptomsincrease'), 'id' => 'symptomsincrease'], ['title' => $this->tr('Other'), 'id' => 'other']]
            ]]],
            'stressdetails' => ViewUtils::lazyEditor($this, 'Stressdetails', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'sessionrpe' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Sessionrpe'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('rest'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('maximal'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => $gaugeAtts
            ]]],
            'sessionrpedetails' => ViewUtils::lazyEditor($this, 'Sessionrpedetails', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'mentaldifficulty' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Mentaldifficulty'), 'style' => ['height' => '100px', 'width' => 'auto', 'maxWidth' => '800px'], 'leftTd' => ['innerHTML' => $tr('rest'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('maximal'), 'style' => $leftRightTdStyle],
                'gaugeAtts' => $gaugeAtts
            ]]],
            'mentaldifficultydetails' => ViewUtils::lazyEditor($this, 'Mentaldifficultydetails', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'indicatorscache' => ViewUtils::textBox($this, 'indicators')
        ];
        $this->customize($customDataWidgets, [], [], []);
    }
}
?>
