<?php
namespace TukosLib\Objects\Physio\PersoTrack\DailyAssesments;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Treatment', 'Summary');
        //$exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        $customDataWidgets = [
            'name' => ViewUtils::lazyEditor($this, 'Summary', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),
            'comments' => ['atts' => ['edit' => ['height' => '100px']]],
            'parentid' => ['atts' => ['edit' => ['onChangeLocalAction' => ['parentid' => ['localActionStatus' =>$this->relatedTreatmentAction()]]]]],
            'exercises' => $this->exercises(),
            'startdate' => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
            'painduring' => ViewUtils::storeSelect('pain', $this, 'Painduring', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em'],
                'onWatchLocalAction' => $this->painOnWatchLocalAction('painnextday')]]]),
            'painafter' => ViewUtils::storeSelect('pain', $this, 'Painafter', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em'],
                'onWatchLocalAction' => $this->painOnWatchLocalAction('painnextday')]]]),
            'painnextday' => ViewUtils::storeSelect('pain', $this, 'Painnextday', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em'],
                'onWatchLocalAction' => $this->painOnWatchLocalAction('painnextday')]]]),
            'mood' => ViewUtils::storeSelect('stress', $this, 'Stress', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'fatigue' => ViewUtils::storeSelect('stress', $this, 'Fatigue', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em']]]]),
            'otherexceptional' => ViewUtils::LazyEditor($this, 'MoodFatigue', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),
        ];
        $this->mustGetCols = array_merge($this->mustGetCols, array_keys($customDataWidgets));
        $subObjects = [
            'physiopersosessions' => [
                'atts' => [
                    'title' => $this->tr('Sessions'), 'allDescendants' => true, 'allowApplicationFilter' => 'yes', 'startDateTimeCol' => 'startdate',
                    'endDateTimeCol' => 'startdate',
                    'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
                    //'onChangeNotify' => ['calendar' => ['startdate' => 'startTime',  'duration' => 'duration',  'name' => 'summary', 'comments' => 'comments', 'stress' => 'stress', 'series' => 'series', 'repeats' => 'repeats', 'extra' => 'extra']],
                    'showFooter' => false,
                    'summaryRow' => ['cols' => [
                        'name' => ['content' =>  [['rhs' => "return (res ? res + '<br>' : '') + #name#;"]]],
                        'painduring' => ['content' => [['rhs' => "var pain = #painduring#; return Math.max(pain, res);"]]],
                        'painafter' => ['content' => [['rhs' => "var pain = #painafter#; return Math.max(pain, res);"]]],
                    ]],
                    'onWatchLocalAction' => ['summary' => ['physiopersosessions' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => <<<EOT
var form = sWidget.form, summary = sWidget.summary;
(['name', 'painduring', 'painafter']).forEach(function(widgetName){
form.setValueOf(widgetName, summary[widgetName]);
});
EOT
                    ]]]],
                    'onDropMap' => ['exercises' => ['fields' => ['name' => 'name', 'startdate' => 'startdate', 'comments' => 'comments', 'stress' => 'stress', 'series' => 'series', 'repeats' => 'repeats', 'extra' => 'extra']]],
                    'sort' => [['property' => 'startdate', 'descending' => false]],
                    'renderCallback' => "if (column.field in  {painduring: true, painafter: true, painnextday: true}){var newColor = {1: 'LIGHTGREEN', 2: 'ORANGE', 3: 'RED', 4: 'RED'}[rowData[column.field]];domstyle.set(tdCell, 'backgroundColor', newColor);domstyle.set(node, 'backgroundColor', newColor);}",
                ],
                'filters' => ['parentid' => '@parentid', 'startdate' => '@startdate',
                    [['col' => 'grade',  'opr' => '<>', 'values' => 'TEMPLATE'], ['col' => 'grade', 'opr' => 'IS NULL', 'values' => null, 'or' => true]]],
            ],
        ];
        $this->customize($customDataWidgets, $subObjects, ['grid' => ['exercises']]);
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
    public static function relatedTreatmentAction(){
        return <<<EOT
var cols = ['exercises'];
Pmg.serverDialog({object: 'physiopersotreatments', view: 'Edit', action: 'GetItem', query: {id: newValue, storeatts: JSON.stringify({cols: cols})}}).then(
    function(response){
        var form = sWidget.form, setValueOf = lang.hitch(form, form.setValueOf), item = response.data.value, items;
        delete item.id;
        cols.forEach(function(widgetName){
            setValueOf(widgetName === 'parentid' ? 'patient' : widgetName, item[widgetName]);
        });
        Pmg.setFeedback(Pmg.message('actionDone'));
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
if (tWidget.getEditorInstance('exerciseid')){
    when (tWidget.getEditorInstance('exerciseid'), function(editorInstance){
        editorInstance.store.setData(data);
    });
}
return true;
EOT;
    }
    public static function painOnWatchLocalAction($widgetName){
        return ['value' => [$widgetName => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => <<<EOT
sWidget.set('style', {backgroundColor: {1: 'LIGHTGREEN', 2: 'ORANGE', 3: 'RED', 4: 'RED'}[newValue]});
return true;  
EOT
        ]]]];
    }
}
?>

