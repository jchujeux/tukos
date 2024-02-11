<?php
namespace TukosLib\Objects\Sports\Sessions;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    use TemplatesViewMixin;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $tr = $this->tr;
        $isMobile = Tfk::$registry->isMobile;
        $leftRightTdStyle = [/*'whiteSpace' => 'nowrap', */'verticalAlign' => 'top', 'paddingTop' => '7px', 'fontSize' => '12px', 'fontFamily' => 'Arial, Helvetica, sans-serif', 'width' => $isMobile ? '80px' : '200px', 'wordWrap' => 'break-word'];
        $gaugeAtts = ['indicatorColor' => 'black', 'height' => 30, 'minimum' => 1, 'maximum' => 10, 'minorTicksEnabled' => false, 'majorTickInterval' => 3, 'showValue' => true, 'tickLabel' => '',
            'gradient' => [0, '#B22222', 0.5, '#FF8C00', 1, '#7FFFD4'], 'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false];
        $reversedGaugeAtts = ['indicatorColor' => 'black', 'height' => 30, 'minimum' => 1, 'maximum' => 10, 'minorTicksEnabled' => false, 'majorTickInterval' => 3, 'showValue' => true, 'tickLabel' => '',
            'gradient' => [0, '#7FFFD4', 0.5, '#FF8C00', 1, '#B22222'], 'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false];
        $gaugeStyle = ['height' => '150px', 'width' => 'auto'];
        $gaugeTableStyle = ['tableLayout' => 'fixed', 'width' => '100%'];
        $gaugeDivStyle = ['width' => '100%'];
        $customDataWidgets = array_merge([
            'name'      => ['atts' => ['edit' =>  ['label' =>$this->tr('Theme'), 'style' => ['width' => '30em']]],],
            'parentid' => ['atts' => ['edit' =>  ['onChangeLocalAction' => ['sessionid' => ['localActionStatus' => $this->adjustSessionIdLocalAction('parentid')]]]]],
            'sportsman' => ViewUtils::objectSelect($this, 'Sportsman', 'people', ['atts' => ['edit' => ['onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgHr()]]]]]),
            'startdate' => ViewUtils::tukosDateBox($this, 'date', ['atts' => [
                'edit' => ['onChangeLocalAction' => ['sessionid' => ['localActionStatus' => $this->adjustSessionIdLocalAction('startdate')]]],
                'storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
            'sessionid' => ViewUtils::storeSelect('sessionid', $this, 'Sessionid', [true, 'ucfirst', false, true, false]),
            'duration'  => ViewUtils::minutesTextBox($this, 'duration', ['atts' => [
                'edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm', 'clickableIncrement' => 'T00:10', 'visibleRange' => 'T01:00']/*, 'style' => ['width' => '6em']*/,
                'onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgHr()]]],
            ]]),
            'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'constraints' => $isMobile ? ['pattern' => '#000.0'] : ['pattern' => '#.##']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevationgain', ['atts' => ['edit' => ['label' => $this->tr('Elevationgain') . ' (m)', 'constraints' => $isMobile ? ['pattern' => '#0000.'] : ['pattern' => '#.#']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'intensity'     => ViewUtils::storeSelect('intensity', $this, 'Plannedintensity', [true, 'ucfirst', true, true, false]),
            'sport'         => ViewUtils::storeSelect('sport', $this, 'Sport', null, ['atts' => ['edit' => [
                    'onWatchLocalAction' => ['value' => [
                        'intensity' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                        'stress' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                    ]],
            ]]]),
            'stress'        => ViewUtils::storeSelect('stress', $this, 'Plannedqsm', [true, 'ucfirst', true, true, false]),
            'warmup'    => ViewUtils::lazyEditor($this, 'warmup', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'mainactivity'    => ViewUtils::lazyEditor($this, 'mainactivity', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'warmdown'    => ViewUtils::lazyEditor($this, 'warmdown', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'warmupdetails'    => ViewUtils::lazyEditor($this, 'warmupdetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'mainactivitydetails'    => ViewUtils::lazyEditor($this, 'mainactivitydetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'warmdowndetails'    => ViewUtils::lazyEditor($this, 'warmdowndetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'googleid' => ViewUtils::textBox($this, 'Googleid'),
            'mode' => ViewUtils::storeSelect('mode', $this, 'Mode', [true, 'ucfirst', false, false, false], ['atts' => ['edit' =>  ['onChangeLocalAction' => [
                'sessionid' => ['localActionStatus' => $this->adjustSessionIdLocalAction('mode')],
                'id' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => $this->modeChangeLocalAction()]]/* if 'mode' rather than 'id' is replaced with cellChartChangeLocalAction in sptprograms*/
            ]]]]),
            'sensations' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('sensations'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr(Sports::$sensationsOptions[1]), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr(Sports::$sensationsOptions[10]),
                    'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle, 'gaugeAtts' => $gaugeAtts
            ]]],
            //'sensations' => ViewUtils::storeSelect('sensations', $this, 'sensations', [true, 'ucfirst', true, true, false], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'perceivedeffort' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Perceivedintensity'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr(Sports::$perceivedEffortOptions[1]), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr(Sports::$perceivedEffortOptions[10]),
                    'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle, 'gaugeAtts' => $gaugeAtts
            ]]],
            'perceivedmechload' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Perceivedstress'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr(Sports::$perceivedMechLoadOptions[1]), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr(Sports::$perceivedMechLoadOptions[10]),
                    'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle, 'gaugeAtts' => $gaugeAtts
            ]]],
            //'perceivedeffort' => ViewUtils::storeSelect('perceivedEffort', $this, 'Perceivedeffort', [true, 'ucfirst', true, true, false], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'mood' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Mood'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr(Sports::$moodOptions[1]), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr(Sports::$moodOptions[10]),
                    'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle, 'gaugeAtts' => $gaugeAtts
            ]]],
            //'mood' => ViewUtils::storeSelect('mood', $this, 'Mood', [true, 'ucfirst', false, true, false], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'athletecomments' => ViewUtils::lazyEditor($this, 'AthleteComments', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]),
            'coachcomments' => ViewUtils::lazyEditor($this, 'CoachSessionComments', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]),
            'timemoving' => ViewUtils::minutesTextBox($this, 'Timemoving'),
            'avghr' => ViewUtils::numberTextBox($this,'Average Heart Rate', ['atts' => ['edit' => ['onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgHr()]]]]]),
            'avgpw' => ViewUtils::numberTextBox($this, 'Average Power', ['atts' => ['edit' => ['onChangeLocalAction' => ['trimpavghr' => ['localActionStatus' => $this->updatetrimpAvgPw()]]]]]),
            'hr95' => ViewUtils::numberTextBox($this, '95%_Heartrate'),
            'trimphr' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Heart_rate'),
            'trimpavghr' => ViewUtils::numberTextBox($this, 'Heartrate_avgload', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'trimppw' => ViewUtils::numberTextBox($this, 'Tukos_TRIMP_Power'),
            'trimpavgpw' => ViewUtils::numberTextBox($this, 'Power_avgload', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'avgcadence' => ViewUtils::numberTextBox($this, 'Avgcadence', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'mechload' => ViewUtils::numberTextBox($this, 'Tukos_Mechanical_Load'),
            'h4time' => ViewUtils::secondsTextBox($this, 'H4_Time_in_Zone'),
            'h5time' => ViewUtils::secondsTextBox($this, 'H5_Time_in_Zone'),
            'lts' => ViewUtils::tukosNumberBox($this, 'lts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'hracwr' => ViewUtils::tukosNumberBox($this, 'hracwr', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'sts' => ViewUtils::tukosNumberBox($this, 'sts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
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
    function modeChangeLocalAction(){
        $plannedCols = json_encode($this->model->plannedCols);
        $performedCols = json_encode($this->model->performedCols);
        return <<<EOT
const form = sWidget.form;
let plannedHiddenState, performedHiddenState;
switch(newValue){
    case 'planned': 
        plannedHiddenState = false;
        performedHiddenState = true;
        break;
    case 'performed':
        plannedHiddenState = true;
        performedHiddenState = false;
        break;
    default:
        plannedHiddenState = false;
        performedHiddenState = false;
        break;
};
$plannedCols.forEach(function(col){
    const widget = form.getWidget(col), userHidden = widget && wutils.customizedAttOf(widget, 'hidden');
    if (userHidden === undefined){
        widget && widget.set('hidden', plannedHiddenState);
    }
});
$performedCols.forEach(function(col){
    const widget = form.getWidget(col), userHidden = widget && wutils.customizedAttOf(widget, 'hidden');
    if (userHidden === undefined){
        widget && widget.set('hidden', performedHiddenState);
    }
});
form.resize();
form.resize();//needed twice for edit in a dialog window, or else the horizontal gauges don't show-up when moving from planned to performed
EOT
        ;
    }
    function updateTrimpAvgHr(){
        return <<<EOT
const sportsman = sWidget.valueOf('#sportsman'), timemoving = sWidget.valueOf('#timemoving'), avghr = sWidget.valueOf('#avghr');
if (sportsman && timemoving && avghr){
    Pmg.serverDialog({action: 'Process', object: "sptsessions", view: 'edit', query: {id: sWidget.valueOf('#id'), sportsman: sportsman, timemoving: timemoving, avghr: avghr, params: {process: 'updateTrimpAvgHr', noget: true}}}, {data: {}}).then(
            function(response){
                response.data.value && sWidget.setValueOf('#trimpavghr', response.data.value.trimpavghr);
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
const sportsman = sWidget.valueOf('#sportsman'), timemoving = sWidget.valueOf('#timemoving'), avgpw = sWidget.valueOf('#avgpw');
if (sportsman && timemoving && avgpw){
    Pmg.serverDialog({action: 'Process', object: "sptsessions", view: 'edit', query: {id: sWidget.valueOf('#id'), sportsman: sportsman, timemoving: timemoving, avgpw: avgpw, params: {process: 'updateTrimpAvgPw', noget: true}}}, {data: {}}).then(
            function(response){
                response.data.value && sWidget.setValueOf('#trimpavgpw', response.data.value.trimpavgpw);
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

