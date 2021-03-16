<?php
namespace TukosLib\Objects\Physio\PersoTrack\Sessions;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Treatment', 'Description');
        $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        $customDataWidgets = Utl::array_merge_recursive_replace(array_merge([
                'parentid' => ['atts' => ['edit' => ['onChangeLocalAction' => ['parentid' => ['localActionStatus' =>$this->relatedTreatmentAction()]]]]],
                'exercises' => $this->exercises(),
                'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '30em']]],],
                'startdate' => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['storeedit' => ['formatType' => 'date'], 'overview' => ['formatType' => 'date']]]),
                'sessionid' => ViewUtils::storeSelect('sessionid', $this, 'Sessionid', [true, 'lowercase', true]),
                'exerciseid' => ['type' => 'storeSelect', 'atts' => ['edit' =>  ['storeArgs' => ['data' => []], 'label' => $this->tr('ExerciseId'),
                    'onChangeLocalAction' => ['exerciseid' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => false], 'action' => $this->exerciseIdLocalAction()]]]]]],
                'duration'  => ViewUtils::minutesTextBox($this, 'duration', ['atts' => [
                    'edit' => ['label' => $this->tr('Duration') . ' (hh:mn)', 'constraints' => ['timePattern' => 'HH:mm:ss', 'clickableIncrement' => 'T00:15:00', 'visibleRange' => 'T01:00:00']],
                ]]),
            ], array_intersect_key($exercisesView->dataWidgets(), [/*'name' => true, */'stress' => true, 'series' => true, 'repeats' => true, 'extra' => true, 'extra1' => true]), [
                'stress'        => ViewUtils::storeSelect('stress', $this, 'Mechanical stress', [true, 'ucfirst', true]),
                'painduring' => ViewUtils::storeSelect('pain', $this, 'Painduring', [true, 'ucfirst', true], ['atts' => ['edit' => [ 'style' => ['width' => '100%', 'maxWidth' => '30em'],
                    'onWatchLocalAction' => $this->painOnWatchLocalAction('painduring')]]]),
                'painafter' => ViewUtils::storeSelect('pain', $this, 'Painafter', [true, 'ucfirst', true], ['atts' => ['edit' => ['style' => ['width' => '100%', 'maxWidth' => '30em'],
                    'onWatchLocalAction' => $this->painOnWatchLocalAction('painafter')]]]),
            ]), [
                'series' => ['atts' => ['edit' => ['onChangeLocalAction' => ['series' => ['localActionStatus' => $this->exerciseChangeLocalAction()]]]]],
                'repeats' => ['atts' => ['edit' => ['onChangeLocalAction' => ['repeats' => ['localActionStatus' => $this->exerciseChangeLocalAction()]]]]],
                'extra' => ['atts' => ['edit' => ['onChangeLocalAction' => ['extra' => ['localActionStatus' => $this->exerciseChangeLocalAction()]]]]],
                'extra1' => ['atts' => ['edit' => ['onChangeLocalAction' => ['extra1' => ['localActionStatus' => $this->exerciseChangeLocalAction()]]]]],
            ]
        );
        $this->mustGetCols = array_merge($this->mustGetCols, array_keys($customDataWidgets));
        
        $this->customize($customDataWidgets, [], ['grid' => ['exercises']]);
    }
    function exerciseIdLocalAction(){
        return <<<EOT
var pane = sWidget.form, form = pane.form || pane, exercises = form.getWidget('exercises'), exercise = exercises ? exercises.get('collection').getSync(newValue) : sWidget.getItem();
['stress', 'series', 'repeats', 'extra', 'extra1'].forEach(function(widgetName){
    sWidget.setValueOf(widgetName, exercise[widgetName]);
});
EOT
        . $this->exerciseChangeLocalAction();
    }
    function exerciseChangeLocalAction(){
        return <<<EOT

sWidget.setValueOf('name', sWidget.valueOf('exerciseid', true) + ' ' + sWidget.valueOf('sessionid', true) + ': ' + sWidget.valueOf('series', true) + '*' + utils.transform(sWidget.valueOf('repeats'), 'numberunit', null, Pmg) + ' ' + 
    sWidget.valueOf('extra', true) + ' ' + sWidget.displayedValueOf('extra1'));
return true;
EOT;
    }
    function exercises(){
        $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        return ViewUtils::JsonGrid($this, 'ExercisesList', array_merge(
            ['rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true]],
            array_intersect_key($exercisesView->dataWidgets(), ['name' => true, 'stress' => true, 'series' => true, 'repeats' => true, 'extra' => true, 'progression' => true, 'comments' => true])),
            ['atts' => ['edit' => [
                'sort' => [['property' => 'stress', 'descending' => false]], 'disabled' => true, 'hidden' => true, 'dndParams' => ['copyOnly' => true, 'selfAccept' => false],
                'onWatchLocalAction' => ['collection' => ['exerciseid' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => true], 'action' => $this->exercisesLocalAction()]]]]
            ]]]);
    }
    public static function relatedTreatmentAction(){
        return <<<EOT
var cols = ['name', 'parentid', 'objective', 'exercises', 'protocol', 'torespect'];
Pmg.serverDialog({object: 'physiopersotreatments', view: 'Edit', action: 'GetItem', query: {id: newValue, storeatts: JSON.stringify({cols: ['exercises']})}}).then(
    function(response){
        var form = sWidget.form, setValueOf = lang.hitch(form, form.setValueOf), item = response.data.value, items;
        delete item.id;
        utils.forEach(item, function(value, widgetName){
            setValueOf(widgetName === 'parentid' ? 'patient' : widgetName, value);
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
tWidget.store.setData(data);
console.log('I am in exercisesLocalAction value: ', tWidget.get('value'));
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

