<?php
namespace TukosLib\Objects\Physio\PersoTrack\DailyAssesments;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Objects\Physio\Physio;
use TukosLib\Objects\Sports\GoldenCheetah as GC;

class View extends AbstractView {

    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Treatment', 'Summary');
        $customDataWidgets = array_merge([
                'name' => ViewUtils::lazyEditor($this, 'Summary', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),
                'comments' => ['atts' => ['edit' => ['height' => '100px']]],
                'parentid' => ['atts' => ['edit' => ['onWatchLocalAction' => ['value' => ['parentid' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => $this->relatedTreatmentAction()]]]]]]],
                'exercises' => $this->exercises(),
                'startdate' => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['edit' => ['onChangeLocalAction' => ['startdate' => ['localActionStatus' => "return sWidget.form.localActions.dateChangeLocalAction(sWidget, tWidget, newValue, oldValue);"]]],
                    'storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
                'painduring' => ViewUtils::storeSelect('pain', $this, 'Painduring', [true, 'ucfirst', true], ['atts' => ['edit' => ['backgroundColors' => Physio::$painColors, 'style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
                'painafter' => ViewUtils::storeSelect('pain', $this, 'Painafter', [true, 'ucfirst', true], ['atts' => ['edit' => ['backgroundColors' => Physio::$painColors, 'style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
                'painnextday' => ViewUtils::storeSelect('pain', $this, 'Painnextday', [true, 'ucfirst', true], ['atts' => ['edit' => ['backgroundColors' => Physio::$painColors, 'style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
                'otherexceptional' => ViewUtils::LazyEditor($this, 'MoodFatigue', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),
                'duration'  => ViewUtils::minutesTextBox($this, 'duration', ['atts' => [
                    'edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm:ss', 'clickableIncrement' => 'T00:15:00', 'visibleRange' => 'T01:00:00']],
                ]]),
                'intensity'     => ViewUtils::storeSelect('intensity', $this, 'Intensity', [true, 'ucfirst', true]),
                'distance' => ViewUtils::tukosNumberBox($this, 'Distance', ['atts' => ['edit' => ['label' => $this->tr('Distance') . ' (km)', 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#00.']]]]),
                'elevationgain' => ViewUtils::tukosNumberBox($this, 'Elevationgain', ['atts' => ['edit' => ['label' => $this->tr('Elevationgain') . ' (m)', 'style' => ['width' => '5em'], 'constraints' => ['pattern' => '#000.']]]]),
            ],
            GC::widgetsDescription($this, ['gcmechload'])
            
        );
        $this->mustGetCols = array_merge($this->mustGetCols, array_keys($customDataWidgets));
        $subObjects = [
            'physiopersosessions' => [
                'atts' => [
                    'mobileWidgetType' => 'MobileAccordionGrid',
                    'accordionAtts' => ['getRowLabelAction' => $this->getRowLabelAction(), 'addRowLabel' => $this->tr('addasession'), 'newRowLabel' => $this->tr('Newsession') . ' <span style="font-size: 12px;">' . $this->tr('clickheretocloseopensessioneditor') . '</span>', 
                                        'deleteRowLabel' => $this->tr('deletethissession')],
                    'title' => $this->tr('Sessions'), 'allDescendants' => true, 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdate',
                    'endDateTimeCol' => 'startdate',
                    'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
                    'showFooter' => false,
                    'summaryRow' => ['cols' => [
                        'name' => ['content' =>  [['rhs' => "return (res ? res + '<br>' : '') + #name#;"]]],
                        'painduring' => ['content' => [['rhs' => "var pain = #painduring#; return Math.max(pain, res);"]]],
                        'painafter' => ['content' => [['rhs' => "var pain = #painafter#; return Math.max(pain, res);"]]],
                        'duration' => ['content' => [['rhs' => $this->durationSummaryAction()]]],
                        'distance' => ['content' => [['rhs' => "return res + #distance#;"]]],
                        'elevationgain' => ['content' => [['rhs' => "return res + #elevationgain#;"]]],
                        'gcmechload' => ['content' => [['rhs' => "return res + #gcmechload#;"]]],
                    ]],
                    'onWatchLocalAction' => ['summary' => ['physiopersosessions' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' => <<<EOT
var form = sWidget.form, summary = sWidget.summary;
(['name', 'painduring', 'painafter', 'duration', 'distance', 'elevationgain', 'gcmechload']).forEach(function(widgetName){
    var widget = form.getWidget(widgetName);
    if (summary[widgetName] !== "0"){
        widget.set('disabled', true);
        widget.set('value', summary[widgetName]);
    }else{
        widget.set('disabled', false);
        widget.set('value', '');
    }
});
EOT
                    ]]]],
                    'onDropMap' => ['exercises' => ['fields' => ['name' => 'name', 'startdate' => 'startdate', 'comments' => 'comments', 'stress' => 'stress', 'series' => 'series', 'repeats' => 'repeats', 'extra' => 'extra']]],
                    'sort' => [['property' => 'startdate', 'descending' => false]],
                    'renderCallback' => "if (column.field in  {painduring: true, painafter: true, painnextday: true}){var newColor = {1: 'LIGHTGREEN', 2: 'ORANGE', 3: 'RED', 4: 'RED'}[rowData[column.field]];domstyle.set(tdCell, 'backgroundColor', newColor);domstyle.set(node, 'backgroundColor', newColor);}",
                    'aroundActions' => Tfk::$registry->isMobile ? ['buildAccordion' => $this->buildAccordionAroundAction()] : []
                ],
                'filters' => ['parentid' => '@parentid', 'startdate' => '@startdate',
                    [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
            ],
        ];
        $this->customize($customDataWidgets, $subObjects, ['post' => ['exercises'], 'grid' => ['exercises']]);
    }
    function exercises(){
        $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        return ViewUtils::JsonGrid($this, 'ExercisesList', array_merge(
            ['rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true]],
            array_intersect_key($exercisesView->dataWidgets(), ['name' => true, 'stress' => true, 'series' => true, 'repeats' => true, 'extra' => true, 'progression' => true, 'comments' => true])),
            ['atts' => ['edit' => [
                'sort' => [['property' => 'stress', 'descending' => false]], 'disabled' => true, 'hidden' => true, 'dndParams' => ['copyOnly' => true, 'selfAccept' => false],
                'onWatchLocalAction' => ['collection' => ['physiopersosessions' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => $this->exercisesLocalAction()]]]]
            ]]]);
    }
    function OpenEditAction(){
        return <<<EOT
var form = this;
require (["tukos/objects/physio/persoTrack/dailyAssesments/LocalActions"], function(LocalActions){
    form.localActions = new LocalActions({form: form});
});
EOT
        ;
    }
    public static function relatedTreatmentAction(){
        return <<<EOT
var cols = ['exercises'], form = sWidget.form;
form.widgetsBeingSet.exercises = true;
Pmg.serverDialog({object: 'physiopersotreatments', view: 'Edit', action: 'GetItem', query: {id: newValue, storeatts: JSON.stringify({cols: cols})}}).then(
    function(response){
        var setValueOf = lang.hitch(form, form.setValueOf), item = response.data.value;
        delete item.id;
        form.markIfChanged = false;
        cols.forEach(function(widgetName){
            setValueOf(widgetName, item[widgetName]);
        });
        Pmg.setFeedback(Pmg.message('actionDone'));
        delete form.widgetsBeingSet.exercises;
        form.markIfChanged = true;
    }
);
return true;
EOT;
    }
    public static function exercisesLocalAction(){
        return <<<EOT
var form = sWidget.form, exercises = sWidget.collection.fetchSync(), data = [{id: '', name: ''}];
exercises.forEach(function(exercise){
    data.push({id: exercise.idg, name: exercise.name});
});
tWidget.columns.exerciseid.editorArgs.storeArgs.data = data;
tWidget.columns.exerciseid.storeCache = {};
tWidget.resize();
if ( tWidget.getEditorInstance && tWidget.getEditorInstance('exerciseid')){
    when (tWidget.getEditorInstance('exerciseid'), function(editorInstance){
        editorInstance.store.setData(data);
    });
}
return true;
EOT;
    }
    public function getRowLabelAction(){
        return <<<EOT
var item = kwArgs.item, grid = kwArgs.grid, columns = grid.columns, colors = {'': '', 1: 'LIGHTGREEN', 2: 'ORANGE', 3: 'RED', 4: 'RED'};
var returnedValue =  
    '<span style="font-size: 12;"><span style="background-color: ' + colors[item.painduring] + ';"> {$this->tr('during')} </span><span style="background-color: ' + colors[item.painafter] + ';"> {$this->tr('after')} </span>' + item.name + '</span>';
    //(item.exerciseid ? utils.findReplace(columns.exerciseid.editorArgs.storeArgs.data, 'id', item.exerciseid === '0' ? '' : item.exerciseid, 'name', columns.exerciseid.storeCache || (columns.exerciseid.storeCache = {})) : item.name) + '</span>';
return returnedValue;
EOT
    ;}
    public function buildAccordionAroundAction(){
        return <<<EOT
var self = this, form = this.form, buildAccordion = lang.hitch(this, originalName);
return function(method){
    when(form.setAttrCompleted('exercises'), buildAccordion);
    }
EOT
    ;}
    public function durationSummaryAction(){
        return <<<EOT
var duration = #duration#;
    if (typeof duration === 'string' && duration.length >= 1){
    if (res === 0){
        return duration;
    }else{    
        var resValues = res.substring(1).split(':').slice(0,2), durationValues = duration.substring(1).split(':').slice(0,2), resValue;
    	resValues[0] = parseInt(resValues[0]); durationValues[0] = parseInt(durationValues[0]);
        resValue  = (parseInt(resValues[0]) + parseInt(durationValues[0])) * 60 + parseInt(resValues[1]) + parseInt(durationValues[1]);
        resValues = [Math.floor(resValue / 60), resValue % 60];
        return 'T' + [resValues[0] ? utils.pad(resValues[0], 2) : '00', resValues[1] || '00'].join(':') + ':00';
    }
}
EOT
        ;
    }
}
?>

