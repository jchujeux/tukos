<?php
namespace TukosLib\Objects\Sports\Workouts;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    use TemplatesViewMixin;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $tr = $this->tr;
        $untranslator = new ObjectTranslator($objectName, null, 'untranslator');
        $isMobile = Tfk::$registry->isMobile;
        $leftRightTdStyle = [/*'whiteSpace' => 'nowrap', */'verticalAlign' => 'top', 'paddingTop' => '7px', 'fontSize' => '12px', 'fontFamily' => 'Arial, Helvetica, sans-serif', 'width' => $isMobile ? '80px' : '200px', 'wordWrap' => 'break-word'];
        $gaugeAtts = ['indicatorColor' => 'black', 'height' => 30, 'minimum' => 1, 'maximum' => 10, 'minorTicksEnabled' => false, 'majorTickInterval' => 3, 'showValue' => true, 'tickLabel' => '',
            'gradient' => [0, '#B22222', 0.5, '#FF8C00', 1, '#7FFFD4'], 'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false];
        /*$reversedGaugeAtts = ['indicatorColor' => 'black', 'height' => 30, 'minimum' => 1, 'maximum' => 10, 'minorTicksEnabled' => false, 'majorTickInterval' => 3, 'showValue' => true, 'tickLabel' => '',
            'gradient' => [0, '#7FFFD4', 0.5, '#FF8C00', 1, '#B22222'], 'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false];*/
        $gaugeStyle = ['height' => '150px', 'width' => 'auto'];
        $gaugeTableStyle = ['tableLayout' => 'fixed', 'width' => '100%'];
        $gaugeDivStyle = ['width' => '100%'];
        $customDataWidgets = array_merge([
            'name'      => ['atts' => ['edit' =>  ['label' =>$this->tr('Theme'), 'style' => ['width' => '30em']]],],
            'sportsman' => ViewUtils::objectSelect($this, 'Sportsman', 'people', ['atts' => ['edit' => ['onChangeLocalAction' => ['heartrate_avgload' => ['localActionStatus' => $this->updateHeartrate_AvgLoad()]]]]]),
            'startdate' => ViewUtils::tukosDateBox($this, 'Startdate', ['atts' => [
                'storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
            'starttime' => ViewUtils::dateTimeBoxDataWidget($this, 'time', ['atts' => ['edit' => ['dateArgs' => false]]]),
            'duration'  => ViewUtils::secondsTextBox($this, 'duration', ['atts' => [
                'edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm', 'clickableIncrement' => 'T00:10', 'visibleRange' => 'T01:00']/*, 'style' => ['width' => '6em']*/,
                'onChangeLocalAction' => ['heartrate_avgload' => ['localActionStatus' => $this->updateHeartrate_AvgLoad()]]],
            ]]),
            'timemoving' => ViewUtils::secondsTextBox($this, 'Timemoving'),
            'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'constraints' => $isMobile ? ['pattern' => '#000.0'] : ['pattern' => '#.##']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevationgain', ['atts' => ['edit' => ['label' => $this->tr('Elevationgain') . ' (m)', 'constraints' => $isMobile ? ['pattern' => '#0000.'] : ['pattern' => '#.#']], 'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'intensity'     => ViewUtils::storeSelect('intensity', $this, 'Plannedintensity', [true, 'ucfirst', true, true, false]),
            'sport'         => ViewUtils::storeSelect('sport', $this, 'Sport', null, ['atts' => ['edit' => [
                    'onWatchLocalAction' => ['value' => [
                        'intensity' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                        'stress' => ['value' => ['triggers' => ['server' => false, 'user' => true], 'action' => "if (newValue === 'rest'){return '';}else{return undefined;}"]],
                    ]],
            ]]]),
            'equipmentid' => ViewUtils::objectSelect($this, 'Equipment', 'sptequipments', ['atts' => [
                'edit' => ['storeArgs' => ['cols' => ['extraweight', 'frictioncoef', 'dragcoef']], 'onChangeLocalAction' => ['equipmentid' => ['localActionStatus' => $this->updateEquipment()]]]]]),
            'extraweight' => ViewUtils::tukosNumberBox($this, 'Extraweight', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.0'], 'onChangeLocalAction' => ['extraweight' => ['localActionStatus' => $this->removeEstimatedPowerKpis()]]]]]),
            'frictioncoef' => ViewUtils::tukosNumberBox($this, 'Frictioncoef', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.0000'], 'onChangeLocalAction' => ['frictioncoef' => ['localActionStatus' => $this->removeEstimatedPowerKpis()]]],
                'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.####']], 'overview' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.####']]]]),
            'dragcoef' => ViewUtils::tukosNumberBox($this, 'Dragcoef', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.00'], 'onChangeLocalAction' => ['dragcoef' => ['localActionStatus' => $this->removeEstimatedPowerKpis()]]]]]),
            'windvelocity' => ViewUtils::tukosNumberBox($this, 'Windvelocity', ['atts' => ['edit' => ['style' => ['width' => '5em'], 'constraints' => ['pattern' => '#0.00'], 'onChangeLocalAction' => ['windvelocity' => ['localActionStatus' => $this->removeEstimatedPowerKpis()]]]]]),
            'winddirection' => ViewUtils::storeSelect('direction', $this, 'Winddirection', [true, 'ucfirst', false, true, false], ['atts' => ['edit' => ['onChangeLocalAction' => ['winddirection' => ['localActionStatus' => $this->removeEstimatedPowerKpis()]]]]]),
            'stress'        => ViewUtils::storeSelect('stress', $this, 'Plannedqsm', [true, 'ucfirst', true, true, false]),
            'warmup'    => ViewUtils::lazyEditor($this, 'warmup', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'mainactivity'    => ViewUtils::lazyEditor($this, 'mainactivity', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'warmdown'    => ViewUtils::lazyEditor($this, 'warmdown', ['atts' => ['edit' => ['onDropMap' => ['column' => 'summary'], 'style' => ['minHeight' => '1em']]]]),
            'warmupdetails'    => ViewUtils::lazyEditor($this, 'warmupdetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'mainactivitydetails'    => ViewUtils::lazyEditor($this, 'mainactivitydetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'warmdowndetails'    => ViewUtils::lazyEditor($this, 'warmdowndetails', ['atts' => ['edit' => ['onDropMap' => ['column' => 'details']]]]),
            'googleid' => ViewUtils::textBox($this, 'Googleid'),
            'mode' => ViewUtils::storeSelect('mode', $this, 'Mode', [true, 'ucfirst', false, false, false], ['atts' => ['edit' =>  ['onChangeLocalAction' => [
                'id' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => $this->modeChangeLocalAction()]]/* if 'mode' rather than 'id' is replaced with cellChartChangeLocalAction in sptplans*/
            ]]]]),
            'sensations' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('sensations'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr(Sports::$sensationsOptions[1]), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr(Sports::$sensationsOptions[10]),
                    'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle, 'gaugeAtts' => $gaugeAtts
            ]]],
            //'sensations' => ViewUtils::storeSelect('sensations', $this, 'sensations', [true, 'ucfirst', true, true, false], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'perceivedeffort' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Perceivedeffort'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr(Sports::$perceivedEffortOptions[1]), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr(Sports::$perceivedEffortOptions[10]),
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
            'avghr' => ViewUtils::numberTextBox($this,'Average Heart Rate', ['atts' => ['edit' => ['onChangeLocalAction' => ['heartrate_avgload' => ['localActionStatus' => $this->updateHeartrate_AvgLoad()]]]]]),
            'avgpw' => ViewUtils::numberTextBox($this, 'Average Power', ['atts' => ['edit' => ['onChangeLocalAction' => ['power_avgload' => ['localActionStatus' => $this->updatePower_AvgLoad()]]]]]),
            'heartrate_load' => ViewUtils::numberTextBox($this, 'Heartrate_load'),
            'heartrate_avgload' => ViewUtils::numberTextBox($this, 'Heartrate_avgload', ['atts' => ['edit' => [/*'disabled' => true, */'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'power_load' => ViewUtils::numberTextBox($this, 'Power_load'),
            'power_avgload' => ViewUtils::numberTextBox($this, 'Power_avgload', ['atts' => ['edit' => [/*'disabled' => true, */'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'avgcadence' => ViewUtils::numberTextBox($this, 'Avgcadence', ['atts' => ['edit' => [/*'disabled' => true, */'style' => ['width' => '5em'], 'constraints' => ['pattern' => '##0.']]]]),
            'mechload' => ViewUtils::numberTextBox($this, 'Mechload'),
            'heartrate_timeabove_threshold_90' => ViewUtils::secondsTextBox($this, 'Heartrate_timeabove_threshold_90'),
            'heartrate_timeabove_threshold' => ViewUtils::secondsTextBox($this, 'Heartrate_timeabove_threshold'),
            'heartrate_timeabove_threshold_110' => ViewUtils::secondsTextBox($this, 'Heartrate_timeabove_threshold_110'),
            'lts' => ViewUtils::tukosNumberBox($this, 'lts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'hracwr' => ViewUtils::tukosNumberBox($this, 'hracwr', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'sts' => ViewUtils::tukosNumberBox($this, 'sts', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'tsb' => ViewUtils::tukosNumberBox($this, 'tsb', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.0']]]]),
            'stravaid' => ViewUtils::htmlContent($this, 'Stravaid', ['atts' => ['edit' => ['disabled' => true], 'storeedit' => ['hidden' => true, 'renderCell' => 'renderStravaLink'], 'overview' => ['hidden' => true, 'renderCell' => 'renderStravaLink']]]),
            'kpiscache' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('KpisCache'), 'keyToHtml' => 'capitalToBlank', 'hasCheckboxes' => true, 'isEditTabWidget' => true, 'checkedServerValue' => '~delete', 'onCheckMessage' => $this->tr('checkedleaveswillbedeletedonsave'),
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto'], 'maxColWidth' => '1200px']],
                'objToEdit' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
                'editToObj' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $untranslator->tr]],
            ],
        ],
        	$this->filterWidgets()
        );
        $this->mustGetCols = ['kpiscache'];
        
        $subObjects = $this->templatesSubObjects();

        $this->customize($customDataWidgets, $subObjects, $this->filterWidgetsExceptionCols());
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
    function updateEquipment(){
        return <<<EOT
if (newValue){
    const equipmentProperties = sWidget.getItem();
    ['extraweight', 'frictioncoef', 'dragcoef'].forEach(function(name){
        sWidget.setValueOf(name, equipmentProperties[name]);
    });
}
EOT
        ;
    }
    function removeEstimatedPowerKpis(){
        return <<<EOT
const kpisToHandle = ['estimatedrawpowerstream'/*, 'estimatedpowerstream', 'estimatedpower_wattsstream'*/, 'estimatedpower_rawwattsstream'];
if ((sWidget.parent || {}).widgetName === 'sptworkouts'){
    const grid = sWidget.parent;
    utils.forEach(grid.clickedRow.data, function(value, name){
        kpisToHandle.forEach(function(kpi){
            if (name.includes(kpi)){
                grid.updateDirty(grid.clickedRow.data.idg, name, undefined);
            }
        });
    }, true);
}else{
    const kpisCacheWidget = sWidget.form.getWidget('kpiscache'), kpisCacheValue = kpisCacheWidget.get('value'), leavesToAdd = {};
    utils.forEach(kpisCacheValue, function(value, name){
        kpisToHandle.forEach(function(kpi){
            if (name.includes(kpi)){
                leavesToAdd[name] = '~delete';
            }
        });
    });
    if (!utils.empty(leavesToAdd)){
        kpisCacheWidget.addSelectedLeaves(leavesToAdd);
    };
}
EOT
        ;
    }
    function updateHeartrate_AvgLoad(){
        return <<<EOT
const sportsman = sWidget.valueOf('#sportsman'), timemoving = sWidget.valueOf('#timemoving'), avghr = sWidget.valueOf('#avghr');
if (sportsman && timemoving && avghr){
    Pmg.serverDialog({action: 'Process', object: "sptworkouts", view: 'edit', query: {id: sWidget.valueOf('#id'), sportsman: sportsman, timemoving: timemoving, avghr: avghr, params: {process: 'updateHeartrate_AvgLoad', noget: true}}}, {data: {}}).then(
            function(response){
                response.data.value && sWidget.setValueOf('#heartrate_avgload', response.data.value.heartrate_avgload);
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
    function updatePower_AvgLoad(){
        return <<<EOT
const sportsman = sWidget.valueOf('#sportsman'), timemoving = sWidget.valueOf('#timemoving'), avgpw = sWidget.valueOf('#avgpw');
if (sportsman && timemoving && avgpw){
    Pmg.serverDialog({action: 'Process', object: "sptworkouts", view: 'edit', query: {id: sWidget.valueOf('#id'), sportsman: sportsman, timemoving: timemoving, avgpw: avgpw, params: {process: 'updatePower_AvgLoad', noget: true}}}, {data: {}}).then(
            function(response){
                response.data.value && sWidget.setValueOf('#power_avgload', response.data.value.power_avgload);
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

