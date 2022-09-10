<?php
namespace TukosLib\Objects\Physio\WoundTrack\GamePlans;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Physio\WoundTrack\IndicatorsView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    use IndicatorsView;
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Patient', 'Description');
        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => '100%', 'height' => 'auto']]]],
            'physiotherapist' => ViewUtils::objectSelect($this, 'Physiotherapist', 'people', ['atts' => ['edit' => ['storeArgs' => ['cols' => ['parentid']], 'onChangeLocalAction' => ['organization' => ['value' => "return sWidget.getItemProperty('parentid')"]]]]]),
            'organization' => ViewUtils::objectSelect($this, 'Organization', 'organizations', ['atts' => ['edit' => ['hidden' => true]]]),
            'dateupdated' => ViewUtils::tukosDateBox($this, 'Dateupdated', ['atts' => ['edit' => ['onWatchLocalAction' => ['value' => [
                'dateupdated' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => $this->dateUpdatedChangeAction()]]
            ]]]]]),
               // ]['dateupdated' => ['localActionStatus' => $this->dateUpdatedChangeAction()]]]]]),
            'diagnostic' => ViewUtils::lazyEditor($this, 'ClinicalDiagnostic', ['atts' => ['edit' => ['height' => '50px', 'editorType' => 'simple']]]),
            'pathologyof' => ViewUtils::storeSelect('pathologyOf', $this, 'Pathologyof', [true, 'ucfirst', true, false], ['atts' => ['edit' => ['onChangeLocalAction' => ['pathologyoftriangle' => ['value' => 'return newValue;']]]]]),
            'pathologyoftriangle' => ['type' => 'abcTriangle', 'atts' => ['edit' => ['value' => '', 'title' => $this->tr('CRATriangle'), 'xTriangle' => 116, 'yTriangle' => 100, 'rCircle' => 15, 'tLabel' => $this->tr('LAR', 'uppercase')]]],
            'woundstartdate' => ViewUtils::tukosDateBox($this, 'Woundstartdate', ['atts' => ['edit' => ['onChangeLocalAction' => ['wounddatedifference' => ['value' => "return sWidget.setValueOf('wounddatedifference', dutils.yearsDaysMonthsDifference(newValue, sWidget.valueOf('dateupdated')));"]]]]]),
            'wounddatedifference' => ViewUtils::textBox($this, 'Wounddatedifference', ['atts' => ['edit' => ['disabled' => true]]]),
            'treatmentstartdate' => ViewUtils::tukosDateBox($this, 'Treatmentstartdate', ['atts' => ['edit' => ['onChangeLocalAction' =>  ['treatmentdatedifference' => ['value' => "return sWidget.setValueOf('treatmentdatedifference', dutils.yearsDaysMonthsDifference(newValue, sWidget.valueOf('dateupdated')));"]]]]]),
            'treatmentdatedifference' => ViewUtils::textBox($this, 'Treatmentdatedifference', ['atts' => ['edit' => ['disabled' => true]]]),
            'training' => ViewUtils::lazyEditor($this, 'Training', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'pain' => ViewUtils::lazyEditor($this, 'Pain', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'exercises' => ViewUtils::lazyEditor($this, 'Exercises', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'biomechanics' => ViewUtils::lazyEditor($this, 'Runningbiomechanics', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),
            'comments' => ['atts' => ['edit' => ['label' => 'Notes', 'height' => '150px']]],
            'indicatorscache' => ViewUtils::textBox($this, 'indicators')
        ];
        $this->customize($customDataWidgets, [], [], []);
    }
    function dateUpdatedChangeAction(){
        return <<<EOT
const woundStartDate = sWidget.valueOf('woundstartdate'), treatmentStartDate = sWidget.valueOf('treatmentstartdate');
if (woundStartDate){
    sWidget.setValueOf('wounddatedifference', dutils.yearsDaysMonthsDifference(woundStartDate, newValue));
}
if (treatmentStartDate){
    sWidget.setValueOf('treatmentdatedifference', dutils.yearsDaysMonthsDifference(treatmentStartDate, newValue));
}
return true;
EOT
        ;
    }
    public function preMergeCustomizationAction($response, $customMode){
        return $this->gamePlansPreMerge($response, $customMode);
    }
}
?>
