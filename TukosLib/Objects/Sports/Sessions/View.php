<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    use TemplatesViewMixin;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = array_merge([
            'name'      => ['atts' => ['edit' =>  ['label' =>$this->tr('Theme'), 'style' => ['width' => '30em']]],],
            'startdate' => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
/*
            'duration'          =>ViewUtils::numberUnitBox('timeInterval', $this, 'Duration', ['atts' => [
                        'edit' => [],
                        'storeedit' => ['formatType' => 'numberunit'],
                        'overview' => ['formatType' => 'numberunit'],
                    ]
                ]
            ),
*/
            'duration'  => ViewUtils::minutesTextBox($this, 'duration', ['atts' => ['edit' => ['label' => $this->tr('Duration') . ' (hh:mn)']]]),
            'intensity'     => ViewUtils::storeSelect('intensity', $this, 'Intensity'),
            'sport'         => ViewUtils::storeSelect('sport', $this, 'Sport', null, ['atts' => ['edit' => [
                    'onWatchLocalAction' => ['value' => [
                        'intensity' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                        'stress' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                    ]],
            ]]]),
            'stress'        => ViewUtils::storeSelect('stress', $this, 'Mechanical stress'),
            'warmup'    => ViewUtils::lazyEditor($this, 'warmup', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'mainactivity'    => ViewUtils::lazyEditor($this, 'mainactivity', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'warmdown'    => ViewUtils::lazyEditor($this, 'warmdown', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'sessionid' => ViewUtils::textBox($this, 'Sessionid', ['atts' => ['edit' => [ 'style' => ['width' => '5em']]]]),
        	'sportsman' => ViewUtils::objectSelect($this, 'Sportsman', 'people'),
            'difficulty'     => ViewUtils::storeSelect('intensity', $this, 'Difficulty'),
        	'warmupdetails'    => ViewUtils::lazyEditor($this, 'warmupdetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'mainactivitydetails'    => ViewUtils::lazyEditor($this, 'mainactivitydetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'warmdowndetails'    => ViewUtils::lazyEditor($this, 'warmdowndetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'googleid' => ViewUtils::textBox($this, 'Googleid'),
            'mode' => ViewUtils::storeSelect('mode', $this, 'Mode'),
            //'distance' => ViewUtils::textBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'style' => ['width' => '8em']]]]),
            'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'style' => ['width' => '5em', 'constraints' => ['pattern' => '#00.']]]]]),
            'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevationgain', ['atts' => ['edit' => ['label' => $this->tr('Elevationgain') . ' (m)', 'style' => ['width' => '5em', 'constraints' => ['pattern' => '#00.']]]]]),
            //'feeling' => ViewUtils::textArea($this, 'Athletefeeling'),
            'feeling' => ViewUtils::storeSelect('feeling', $this, 'feeling', [true, 'ucfirst', true], ['atts' => ['edit' => ['noMobileKeyboard' => true, 'style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'sensations' => ViewUtils::storeSelect('sensations', $this, 'sensations', [true, 'ucfirst', true], ['atts' => ['edit' => ['noMobileKeyboard' => true, 'style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'perceivedeffort' => ViewUtils::storeSelect('perceivedEffort', $this, 'Perceivedeffort', [true, 'ucfirst', true], ['atts' => ['edit' => ['noMobileKeyboard' => true, 'style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'mood' => ViewUtils::storeSelect('mood', $this, 'Mood', [true, 'ucfirst', true], ['atts' => ['edit' => ['noMobileKeyboard' => true, 'style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'athletecomments' => ViewUtils::textArea($this, 'AthleteComments', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]),
            'athleteweeklyfeeling' => ViewUtils::textArea($this, 'Athleteweeklyfeeling', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]),
            'coachcomments' => ViewUtils::textArea($this, 'CoachSessionComments', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]),
            'coachweeklycomments' => ViewUtils::textArea($this, 'CoachWeeklyComments', ['atts' => ['edit' => ['style' => ['width' => '100%']]]])
        ],
        	$this->filterWidgets()
        );

        $this->mustGetCols = array_merge($this->mustGetCols, ['name', 'duration', 'intensity', 'stress', 'sport','warmup', 'mainactivity', 'warmdown', 'comments', 'mode']);

        $subObjects = $this->templatesSubObjects();

        $this->customize($customDataWidgets, $subObjects, $this->filterWidgetsExceptionCols());
    }    
}
?>

