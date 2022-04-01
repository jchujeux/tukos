<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    use TemplatesViewMixin;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $isMobile = Tfk::$registry->isMobile;
        $customDataWidgets = array_merge([
            'name'      => ['atts' => ['edit' =>  ['label' =>$this->tr('Theme'), 'style' => ['width' => '30em']]],],
            'parentid' => ['atts' => ['edit' =>  ['onChangeLocalAction' => ['sessionid' => ['localActionStatus' => $this->adjustSessionIdLocalAction('parentid')]]]]],
            'sportsman' => ViewUtils::objectSelect($this, 'Sportsman', 'people', ['atts' => ['edit' => ['onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgHr()]]]]]),
            'startdate' => ViewUtils::tukosDateBox($this, 'date', ['atts' => [
                'edit' => ['onChangeLocalAction' => ['sessionid' => ['localActionStatus' => $this->adjustSessionIdLocalAction('startdate')]]],
                'storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
            'sessionid' => ViewUtils::storeSelect('sessionid', $this, 'Sessionid', [true, 'ucfirst', true]),
            'duration'  => ViewUtils::minutesTextBox($this, 'duration', ['atts' => [
                'edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm', 'clickableIncrement' => 'T00:10', 'visibleRange' => 'T01:00']/*, 'style' => ['width' => '6em']*/,
                'onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgHr()]]],
            ]]),
            'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'constraints' => $isMobile ? ['pattern' => '#000.0'] : ['places' => 1]]]]),
            'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevationgain', ['atts' => ['edit' => ['label' => $this->tr('Elevationgain') . ' (m)', 'constraints' => $isMobile ? ['pattern' => '#0000.'] : []]]]),
            'intensity'     => ViewUtils::storeSelect('intensity', $this, 'Intensity', [true, 'ucfirst', true]),
            'sport'         => ViewUtils::storeSelect('sport', $this, 'Sport', null, ['atts' => ['edit' => [
                    'onWatchLocalAction' => ['value' => [
                        'intensity' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                        'stress' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                    ]],
            ]]]),
            'stress'        => ViewUtils::storeSelect('stress', $this, 'Mechanical stress', [true, 'ucfirst', true]),
            'warmup'    => ViewUtils::lazyEditor($this, 'warmup', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'mainactivity'    => ViewUtils::lazyEditor($this, 'mainactivity', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'warmdown'    => ViewUtils::lazyEditor($this, 'warmdown', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'difficulty'     => ViewUtils::storeSelect('intensity', $this, 'Difficulty', [true, 'ucfirst', true]),
            'warmupdetails'    => ViewUtils::lazyEditor($this, 'warmupdetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'mainactivitydetails'    => ViewUtils::lazyEditor($this, 'mainactivitydetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'warmdowndetails'    => ViewUtils::lazyEditor($this, 'warmdowndetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'googleid' => ViewUtils::textBox($this, 'Googleid'),
            'mode' => ViewUtils::storeSelect('mode', $this, 'Mode', [true, 'ucfirst', false], ['atts' => ['edit' =>  ['onChangeLocalAction' => ['sessionid' => ['localActionStatus' => $this->adjustSessionIdLocalAction('mode')]]]]]),
            'sensations' => ViewUtils::storeSelect('sensations', $this, 'sensations', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'perceivedeffort' => ViewUtils::storeSelect('perceivedEffort', $this, 'Perceivedeffort', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'mood' => ViewUtils::storeSelect('mood', $this, 'Mood', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'athletecomments' => ViewUtils::textArea($this, 'AthleteComments', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]),
            'coachcomments' => ViewUtils::lazyEditor($this, 'CoachSessionComments', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]),
            'timemoving' => ViewUtils::minutesTextBox($this, 'Time Riding'),
            'avghr' => ViewUtils::numberTextBox($this,'Average Heart Rate', ['atts' => ['edit' => ['onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgHr()]]]]]),
            'avgpw' => ViewUtils::numberTextBox($this, 'Average Power', ['atts' => ['edit' => ['onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgPw()]]]]]),
            'hr95' => ViewUtils::numberTextBox($this, '95%_Heartrate'),
            'trimphr' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Heart_rate'),
            'trimpavghr' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Avg_Heart_rate', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'trimppw' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Power'),
            'trimpavgpw' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Avg_Power', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'mechload' => ViewUtils::numberTextBox($this, 'Tukos_Mechanical_Load'),
            'h4time' => ViewUtils::secondsTextBox($this, 'H4_Time_in_Zone'),
            'h5time' => ViewUtils::secondsTextBox($this, 'H5_Time_in_Zone'),
            'sts' => ViewUtils::tukosNumberBox($this, 'sts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'lts' => ViewUtils::tukosNumberBox($this, 'lts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'tsb' => ViewUtils::tukosNumberBox($this, 'tsb', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'stravaid' => ViewUtils::htmlContent($this, 'Stravaid', ['atts' => ['edit' => ['disabled' => true], 'storeedit' => ['hidden' => true]],
                'objToEdit' => ['stravaLink' => ['class' => $this]], 'objToStoreEdit' => ['stravaLink' => ['class' => $this]], 'objToOverview' => ['stravaLink' => ['class' => $this]]]),
        ],
        	$this->filterWidgets()
        );

        //$this->mustGetCols = array_merge($this->mustGetCols, ['name', 'duration', 'intensity', 'stress', 'sport','warmup', 'mainactivity', 'warmdown', 'comments', 'mode', 'athleteweeklyfeeling', 'coachweeklycomments']);
        $this->mustGetCols = array_merge($this->mustGetCols, array_keys($customDataWidgets));
        
        $subObjects = $this->templatesSubObjects();

        $this->customize($customDataWidgets, $subObjects, Utl::array_merge_recursive_replace(['edit' => $this->model->streamCols, 'grid' => $this->model->streamCols, 'get' => $this->model->streamCols, 'post' => [$this->model->streamCols]], $this->filterWidgetsExceptionCols()));
    }
    function adjustSessionIdLocalAction($changedWidgetName){
        return <<<EOT
var form = sWidget.form, parentid = form.valueOf('parentid'), startdate = form.valueOf('startdate');
if (parentid && startdate){
    Pmg.serverDialog({action: 'Process', object: "sptsessions", view: 'edit', query: {id: form.valueOf('id'), parentid: parentid, startdate: startdate, mode: form.valueOf('mode'), sessionid: form.valueOf('sessionid'), params: {process: 'adjustSessionId', noget: true}}}, {data: {}}).then(
            function(response){
                form.setValueOf('sessionid', response.data.value.sessionid);
            },
            function(error){
                console.log('error');
            }
    );
};
return true;
EOT
        ;        
    }
    function updateTrimpAvgHr(){
        return <<<EOT
var form = sWidget.form, sportsman = form.valueOf('sportsman'), timemoving = form.valueOf('timemoving'), avghr = form.valueOf('avghr');
if (sportsman && timemoving && avghr){
    Pmg.serverDialog({action: 'Process', object: "sptsessions", view: 'edit', query: {id: form.valueOf('id'), sportsman: sportsman, timemoving: timemoving, avghr: avghr, params: {process: 'updateTrimpAvgHr', noget: true}}}, {data: {}}).then(
            function(response){
                response.data.value && form.setValueOf('trimpavghr', response.data.value.trimpavghr);
            },
            function(error){
                console.log('error');
            }
    );
}
return true;
EOT
        ;
    }
    function updateTrimpAvgPw(){
        return <<<EOT
var form = sWidget.form, sportsman = form.valueOf('sportsman'), timemoving = form.valueOf('timemoving'), avgpw = form.valueOf('avgpw');
if (sportsman && timemoving && avgpw){
    Pmg.serverDialog({action: 'Process', object: "sptsessions", view: 'edit', query: {id: form.valueOf('id'), sportsman: sportsman, timemoving: timemoving, avgpw: avgpw, params: {process: 'updateTrimpAvgPw', noget: true}}}, {data: {}}).then(
            function(response){
                response.data.value && form.setValueOf('trimpavgpw', response.data.value.trimpavgpw);
            },
            function(error){
                console.log('error');
            }
    );
}
return true;
EOT
        ;
    }
    function stravaLink($stravaId){
        if (!empty($stravaId)){
            return "<a href=\"https://www.strava.com/activities/$stravaId\" target=\"_blank\">$stravaId</a>";
        }
    }
}
?>

