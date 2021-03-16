<?php
namespace TukosLib\Objects\Physio\PersoTrack\Plans;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Patient', 'Description');
        $customDataWidgets = [
            'diagnostic' => ViewUtils::lazyEditor($this, 'RecapDiagnostic', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),
            'symptomatology' => ViewUtils::lazyEditor($this, 'CurrentSymptomatology', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),//ViewUtils::textArea($this, 'CurrentSymptomatology'),
            'recentactivity' => ViewUtils::lazyEditor($this, 'Recentactivity', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),//ViewUtils::textArea($this, 'Recentactivity'),
            'objective' => ViewUtils::lazyEditor($this, 'SettingObjective', ['atts' => ['edit' => ['height' => '100px', 'editorType' => 'simple']]]),//ViewUtils::textArea($this, 'SettingObjective'),
            'exercises' => $this->exercises(),
            'protocol' => ViewUtils::lazyEditor($this, 'TherapeuticalProtocol', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),//ViewUtils::textArea($this, 'TherapeuticalProtocol'),
            'torespect' => ViewUtils::lazyEditor($this, 'QSMToRespect', ['atts' => ['edit' => ['height' => '150px', 'editorType' => 'simple']]]),//ViewUtils::textArea($this, 'QSMToRespect'),
            'comments' => ['atts' => ['edit' => ['height' => '150px']]]
        ];
        $this->customize($customDataWidgets, $this->subObjects(), ['grid' => ['exercises']], ['exercises' => []]);
    }
    function exercises(){
        $exercisesView = Tfk::$registry->get('objectsStore')->objectView('sptexercises');
        return ViewUtils::JsonGrid($this, 'ExercisesList', array_merge(
                ['rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true]], 
            array_intersect_key($exercisesView->dataWidgets(), ['name' => true, 'stress' => true, 'series' => true, 'repeats' => true, 'extra' => true, 'extra1' => true, 'progression' => true, 'comments' => true])),
            ['atts' => ['edit' => [
                'initialRowValue' => [], 'maxHeight' => '300px',
                'sort' => [['property' => 'stress', 'descending' => false]],
                'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
                'onDropMap' => [
                    'exercisescatalog' => ['fields' => [
                        'name' => 'name', 'comments' => 'comments', 'stress' => 'stress', 'series' => 'series', 'repeats' => 'repeats', 'extra' => 'extra', 'progression' => 'progression']],
                ]
            ]]]);
    }
    function subObjects(){
        return [
            'exercisescatalog' => [
                'object' => 'physiopersoexercises', 'filters' => [],
                'atts' => ['title' => $this->tr('Exercisestemplates'), 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false], 'maxHeight' => '300px'],
                'allDescendants' => true,
            ]
        ];
    }
}
?>
