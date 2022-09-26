<?php
namespace TukosLib\Objects\Physio\WoundTrack\GameTracks;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Physio\WoundTrack\IndicatorsView;
use TukosLib\Objects\Physio\WoundTrack\GameTracks\TrendChartView;
use TukosLib\Objects\Physio\WoundTrack\GameTracks\AccordionGridUtilities as Agu;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {
    
    use IndicatorsView, trendChartView;
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'PhysioGamePlan', 'Description');
        $tr = $this->tr;
        $this->addToTranslate(['addrecord', 'actualizerecord']);
        $isMobile = Tfk::$registry->isMobile;
        $leftRightTdStyle = [/*'whiteSpace' => 'nowrap', */'verticalAlign' => 'top', 'paddingTop' => '7px', 'fontSize' => 'smaller', 'fontFamily' => 'Arial, Helvetica, sans-serif', 'width' => '70px', 'wordWrap' => 'break-word'];
        $gaugeAtts = ['indicatorColor' => 'black', 'height' => 30, 'minimum' => 0, 'maximum' => 10, 'minorTicksEnabled' => false, 'majorTickInterval' => 10, 'showValue' => true, 'tickLabel' => '',
            'gradient' => [0, '#B22222', 0.5, '#FF8C00', 1, '#7FFFD4'], 'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false];
        $gaugeStyle = ['height' => '100px'];
        $gaugeTableStyle = ['tableLayout' => 'fixed', 'width' => 'auto'];
        $gaugeDivStyle = ['width' => 'auto'];
        $detailsAtts = ['atts' => ['edit' => ['height' => '2.5em', 'editorType' => 'simple', 'style' => ['minHeight' => '2em'], 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']]]]];
        $planDataWidgets = [
            'patientid' => ViewUtils::objectSelect($this, 'Patient', 'physiopatients', ['atts' => ['edit' => ['placeHolder' => '', 'disabled' => true]]]),
            'dateupdated' => ViewUtils::tukosDateBox($this, 'Dateupdated', ['atts' => ['edit' => ['disabled' => true], 'overview' => ['hidden' => true]]]),
            'diagnostic' => ViewUtils::htmlContent($this, 'ClinicalDiagnostic', ['atts' => ['edit' => ['height' => '50px'], 'overview' => ['hidden' => true]]]),
            'pathologyof' => ViewUtils::storeSelect('pathologyOf', $this, 'Pathologyof', [true, 'ucfirst', true, false], ['atts' => ['edit' => ['disabled' => 'true'], 'overview' => ['hidden' => true]]]),
            'woundstartdate' => ViewUtils::tukosDateBox($this, 'Woundstartdate', ['atts' => ['edit' => ['disabled' => true], 'overview' => ['hidden' => true]]]),
            'treatmentstartdate' => ViewUtils::tukosDateBox($this, 'Treatmentstartdate', ['atts' => ['edit' => ['disabled' => true], 'overview' => ['hidden' => true]]]),
            'training' => ViewUtils::htmlContent($this, 'Training', ['atts' => ['edit' => ['height' => '150px'], 'overview' => ['hidden' => true]]]),
            'pain' => ViewUtils::htmlContent($this, 'Pain', ['atts' => ['edit' => ['height' => '150px'], 'overview' => ['hidden' => true]]]),
            'exercises' => ViewUtils::htmlContent($this, 'Exercises', ['atts' => ['edit' => ['height' => '150px'], 'overview' => ['hidden' => true]]]),
            'biomechanics' => ViewUtils::htmlContent($this, 'Runningbiomechanics', ['atts' => ['edit' => ['height' => '150px'], 'overview' => ['hidden' => true]]]),
            'notes' => ViewUtils::htmlContent($this, 'Notes', ['atts' => ['edit' => ['height' => '150px'], 'overview' => ['hidden' => true]]]),
            'planindicatorscache' => ViewUtils::textBox($this, 'indicators', ['atts' => ['overview' => ['hidden' => true]]]),
        ];
        $recordsDataWidgets = [
            'rowId' => ViewUtils::textBox($this, 'rowId', ['atts' => ['edit' => ['hidden' => true, 'style' => ['width' => '3em'], 'onChangeLocalAction' => ['rowId' => ['localActionStatus' => '(sWidget.form.form || sWidget.form).localActions.rowIdChangeAction(newValue)']]]]]),
            'recordtype' => ViewUtils::storeSelect('recordtype', $this, 'Recordtype', [true, 'ucfirst', true, false], ['atts' => ['edit' => ['onChangeLocalAction' => ['recordtype' => ['localActionStatus' => 
                'Pmg.isMobile() ? sWidget.form.form.localActions.accordionRecordTypeChangeAction(newValue, sWidget.form) : (sWidget.form.form || sWidget.form).localActions.recordTypeChangeAction(newValue, sWidget.form);']]]]]),
            'recorddate' => ViewUtils::tukosDateBox($this, 'Recorddate', ['atts' => ['edit' => ['onChangeLocalAction' => ['recorddate' => ['localActionStatus' => 'if (!Pmg.isMobile){sWidget.form.localActions.recordDateChangeLocalAction(newValue);}']]]]]),
            'duration'  => ViewUtils::minutesTextBox($this, 'duration', ['atts' => ['edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm', 'clickableIncrement' => 'T00:10', 'visibleRange' => 'T01:00'], 'style' => $isMobile ? [] : ['width' => '4em'],
                'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;'], 'perceivedload' => ['value' =>
                    "return (sWidget.valueOf('perceivedintensity') || 0) * dutils.timeToSeconds(typeof newValue === 'object' ? dojo.date.stamp.toISOString(newValue, {selector: 'time'}) : newValue) / 36 / 8;"]]]]]),
            'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'constraints' => $isMobile ? ['pattern' => '#000.0'] : ['pattern' => '#.##'], 'style' => ['width' => '2em'],
                'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']]],
                'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevation', ['atts' => ['edit' => ['label' => $this->tr('Elevation') . ' (m)', 'constraints' => $isMobile ? ['pattern' => '#0000.'] : ['pattern' => '#.#'], 'style' => ['width' => '2.5em'],
                'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']]],
                'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'perceivedload' => ViewUtils::tukosNumberBox($this, 'Perceivedphysioload', ['atts' => ['edit' => ['label' => $this->tr('Perceivedload'), 'disabled' => true, 'constraints' => $isMobile ? ['pattern' => '#0000.'] : ['pattern' => '#.#'], 'style' => ['width' => '2em'],
                'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']]],
                'storeedit' => ['formatType' => 'number', 'formatOptions' => ['pattern' => '#.#']]]]),
            'perceivedintensity' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Perceivedintensity'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr('Extremelylow'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Extremelyhigh'), 'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle,
                'gaugeAtts' => $gaugeAtts, 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;'], 'perceivedload' => ['value' => "return (dutils.timeToSeconds(sWidget.valueOf('duration')) || 0) * newValue / 36 / 8;"]]
            ]]],
            'intensitydetails' => ViewUtils::lazyEditor($this, 'detailssmall', $detailsAtts),
            'activitydetails' => ViewUtils::lazyEditor($this, 'Activitydetails', Utl::array_merge_recursive_replace($detailsAtts, ['atts' => ['edit' => ['height' => '5em']]])),
            'perceivedstress' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Perceivedstress'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr('Insufficient'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('excessive'), 'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle,
                'gaugeAtts' => ['indicatorColor' => 'black', 'height' => 30, 'minimum' => -5, 'maximum' => 20, 'minorTicksEnabled' => false, 'majorTickInterval' => 5, 'showValue' => true, 'tickLabel' => '', 'gradient' => [0, '#B22222', 0.15, '#B22222', 0.25, '#FF8C00', 0.37, '#7FFFD4', 0.5, '#7FFFD4', 0.7, '#FFFFFF', 1, '#FFFFFF'],
                    'style' => ['margin' => '0px 0px 0px 0px', 'height' => '50px'], 'useTooltip' => false], 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']],
                'checkboxes' => [['title' => $this->tr('Painduring'), 'id' => 'painduring'], ['title' => $this->tr('Painafter'), 'id' => 'painafter'], ['title' => $this->tr('Symptomsincrease'), 'id' => 'symptomsincrease'], ['title' => $this->tr('Other'), 'id' => 'other']]
            ]]],
            'stressdetails' => ViewUtils::lazyEditor($this, 'detailssmall', $detailsAtts),
            'mentaldifficulty' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Mentaldifficulty'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr('none'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('maximal'), 'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle,
                'gaugeAtts' => $gaugeAtts, 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']]
            ]]],
            'mentaldifficultydetails' => ViewUtils::lazyEditor($this, 'detailssmall', $detailsAtts),
            'globalsensation' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Globalsensation'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr('Verygoode'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Verybade'), 'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle, 'gaugeDivStyle' => $gaugeDivStyle,
                'gaugeAtts' => $gaugeAtts, 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']],
                'checkboxes' => [['title' => $this->tr('Sleep'), 'id' => 'sleep'], ['title' => $this->tr('Welfare'), 'id' => 'welfare'], ['title' => $this->tr('Stress'), 'id' => 'stress'], ['title' => $this->tr('Healthillness'), 'id' => 'healthillness'],
                    ['title' => $this->tr('Energylevel'), 'id' => 'energylevel'], ['title' => $this->tr('Other'), 'id' => 'other']]
            ]]],
            'globalsensationdetails' => ViewUtils::lazyEditor($this, 'detailssmall', $detailsAtts),
            'environment' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Environment'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr('Favorable'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Unfavorable'), 'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle,
                'gaugeAtts' => $gaugeAtts, 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']],
                'checkboxes' => [['title' => $this->tr('Professional'), 'id' => 'professional'], ['title' => $this->tr('Personal'), 'id' => 'personal'], ['title' => $this->tr('Family'), 'id' => 'family'], ['title' => $this->tr('Sports'), 'id' => 'sports'], ['title' => $this->tr('other'), 'id' => 'other']]
            ]]],
            'environmentdetails' => ViewUtils::lazyEditor($this, 'detailssmall', $detailsAtts),
            'recovery' => ['type' => 'horizontalLinearGauge', 'atts' => ['edit' => [
                'label' => $tr('Recovery'), 'style' => $gaugeStyle, 'leftTd' => ['innerHTML' => $tr('Totally'), 'style' => $leftRightTdStyle], 'rightTd' => ['innerHTML' => $tr('Notatall'), 'style' => $leftRightTdStyle], 'gaugeTableStyle' => $gaugeTableStyle, 'gaugeDivStyle' => $gaugeDivStyle,
                'gaugeAtts' => $gaugeAtts, 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']],
                'checkboxes' => [['title' => $this->tr('Aches'), 'id' => 'aches'], ['title' => $this->tr('Pain'), 'id' => 'pain'], ['title' => $this->tr('Energy'), 'id' => 'energy'], ['title' => $this->tr('Psychofatigue'), 'id' => 'psychofatigue'], ['title' => $this->tr('Other'), 'id' => 'other']]
            ]]],
            'recoverydetails' => ViewUtils::lazyEditor($this, 'detailssmall', $detailsAtts),
            'notecomments' => ViewUtils::lazyEditor($this, 'Notecomments', ['atts' => ['edit' => ['style' => ['width' => '30em', 'height' => '8em'], 'editorType' => 'simple', 'onChangeLocalAction' => ['actualize' => ['hidden' => 'return false;']]]]]),
            'indicatorscache' => ViewUtils::textBox($this, 'indicators')
        ];
        $customDataWidgets = [
            'parentid' =>  $this->user->rights() === 'RESTRICTEDUSER'
                ? ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '25em', 'maxWidth' => '25em']]]]
            : ['atts' => ['edit' => ['style' => ['width' => '25em', 'maxWidth' => '25em'], 'onChangeLocalAction' => ['parentid' => ['localActionStatus' =>$this->relatedPlanAction()]]]]],
            'name' => ['atts' => ['edit' => ['hidden' => true], 'overview' => ['hidden' => true]]],
            'comments' => ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple', 'hidden' => true], 'overview' => ['hidden' => true]]],
            'records' => ViewUtils::JsonGrid($this, 'Records', array_merge(['rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true]], $recordsDataWidgets), ['type' => 'accordionGrid', 'atts' => ['edit' => [
                'disabled' => true, 'noDataMessage' => '', 'storeArgs' => ['idProperty' => 'rowId'], 'noSendOnSave' => [], 'onWatchLocalAction' => [], 'initialRowValue' => ['recorddate' => date('Y-m-d')],
                'mobileWidgetType' => 'MobileAccordionGrid',
                'accordionAtts' => ['orientation' => 'vert', 'getRowLabelAction' => $this->getRowLabelAction(), 'addRowLabel' => $this->tr('newrecord'), 'newRowLabel' => $this->tr('Newrecord') . ' <span style="font-size: 12px;">' . $this->tr('clickheretocloseopensessioneditor') . '</span>',
                    'deleteRowLabel' => $this->tr('deleterecord'), 'actualizeRowLabel' => $this->tr('actualizerecord'), 'desktopRowLayout' => $isMobile ? null : Agu::desktopRowLayout($tr)],
                    'title' => $this->tr('Records'), 'allDescendants' => true/*, 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdate',*/
                ]]])
        ];
        $noGridNoGetNoPostDataWidgets = array_merge($planDataWidgets, $recordsDataWidgets);
        $customDataWidgets = array_merge($customDataWidgets, $noGridNoGetNoPostDataWidgets);        
        $noGridNoGetNoPostWidgetsName = array_keys($noGridNoGetNoPostDataWidgets);
        $this->customize($customDataWidgets, [], ['grid' => $noGridNoGetNoPostWidgetsName, 'get' => $noGridNoGetNoPostWidgetsName, 'post' => $noGridNoGetNoPostWidgetsName], ['records' => []]);
    }
     public static function relatedPlanAction(){
         return <<<EOT
const form = sWidget.form, acl = form.getWidget('acl'), planColsToUpdate = form.planColsToUpdate, planToTrack = form.planToTrack;
;
if (newValue){
    Pmg.serverDialog({object: 'physiogameplans', view: 'Edit', action: 'GetItem', query: {id: newValue, storeatts: JSON.stringify({cols: planColsToUpdate})}}).then(
        function(response){
            const item = response.data.value;
            delete item.id;
            utils.forEach(item, function(value, planName){
                form.setValueOf(planToTrack[planName] || planName, value);
            });
        	return Pmg.serverDialog({object: 'users', view: 'Edit', action: 'GetItem', query: {parentid: item.parentid, storeatts: JSON.stringify({cols: []})}}).then(
            	function (response){
                    acl.set('value', '');
                    acl.addRow(null, {rowId:1,userid: response.data.value.id,permission:"2"});
    			}
    		);	
        }
    );
}else{
    planColsToUpdate.forEach(function(planName){
        form.setValueOf(planToTrack[planName] || planName, '');
    });
    acl.deleteRows(acl.store.fetchSync(), true);
}
return true;
EOT;
     }
     public function preMergeCustomizationAction($response, $customMode){
         $response =  $this->gameTracksIndicatorsPreMerge($response, $customMode);
         return $this->trendChartPreMergeCustomizationAction($response, $customMode);
     }
     public function getRowLabelAction(){
         return <<<EOT
const item = kwArgs.item, grid = kwArgs.grid, columns = grid.columns;
let i = 1, indicatorColumn, indicatorsHtml = '';
while (indicatorColumn = columns['trackindicator' + i]){
	const atts = indicatorColumn.editorArgs.gaugeAtts;
    indicatorsHtml += '<span style="background-color: ' + utils.valueToGradientColor((item['trackindicator' + i] - atts.minimum) / (atts.maximum - atts.minimum), atts.gradient) + ';">{$this->tr("indicator")} ' + i + '</span> ';
	i += 1;
}
let additionalHtml = '', atts, pValue;
switch (Number(item.recordtype)){
    case 1: 
	    atts = columns.perceivedstress.editorArgs.gaugeAtts;//item.perceivedstress ? JSON.parse(item.perceivedstress).gauge || item.perceivedstress : 0
        indicatorsHtml +=  '<span style="background-color: ' + utils.valueToGradientColor(((item.perceivedstress ? JSON.parse(item.perceivedstress).gauge || item.perceivedstress : 0) - atts.minimum) / (atts.maximum - atts.minimum), atts.gradient) + ';">{$this->tr("stress")} ' + '</span> ';
	    atts = columns.perceivedintensity.editorArgs.gaugeAtts;
        indicatorsHtml +=  '<span style="background-color: ' + utils.valueToGradientColor(((item.perceivedintensity ? JSON.parse(item.perceivedintensity).gauge || item.perceivedintensity : 0) - atts.minimum) / (atts.maximum - atts.minimum), atts.gradient) + ';">{$this->tr("intensity")} ' + '</span> ';
        additionalHtml += ' dist: ' + item.distance + 'kms ' + ' Deniv: ' + item.elevationgain + 'm ';
        break;
    case 2:
	    atts = columns.perceivedstress.editorArgs.gaugeAtts;
        indicatorsHtml +=  '<span style="background-color: ' + utils.valueToGradientColor(((item.perceivedintensity ? JSON.parse(item.perceivedintensity).gauge || item.perceivedintensity : 0) - atts.minimum) / (atts.maximum - atts.minimum), atts.gradient) + ';">{$this->tr("intensity")} ' + '</span> ';
        break;
    default:
}
var returnedValue =
    '<span style="font-size: 12;">' + item.recorddate + ' ' + indicatorsHtml + ' '  + columns.recordtype.editorArgs.storeArgs.data[item.recordtype].name +  additionalHtml + '</span>';
return returnedValue;
EOT
         ;}
}
?>
