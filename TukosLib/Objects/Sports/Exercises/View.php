<?php
namespace TukosLib\Objects\Sports\Exercises;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\Sports\Sports;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customDataWidgets = $this->customDataWidgets();
        $this->mustGetCols = array_merge($this->mustGetCols, ['level1', 'level2', 'level3', 'comments']);
        $this->customize($customDataWidgets);
    }
    function customDataWidgets(){
        return [
            'name'   => ['atts' => ['edit' =>  ['style' => ['width' => '40em']]],],
            'level1' => ViewUtils::objectSelect($this, 'Level1', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 1, 'parentid' => '@parentid']]]]),
            'level2' => ViewUtils::objectSelect($this, 'Level2', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 2, 'parentid' => '@level1']]]]),
            'level3' => ViewUtils::objectSelect($this, 'Level3', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 3]]]]),
            'stress' => ViewUtils::storeSelect('stress', $this, 'Plannedqsm', [true, 'ucfirst', true, false]),
            'series' => ViewUtils::numberTextBox($this, 'Series', ['atts' => ['edit' => ['style' => ['width' => '5em']]]]),
            'repeats'=>ViewUtils::numberUnitBox('repeats', $this, 'Repeatsorduration', ['atts' => [
                'edit' => ['unit' => ['placeHolder' => $this->tr('Select') . ' ...']],
            ]], [true, 'plural', false, false]),
            'extra'=>ViewUtils::numberUnitBox('extra', $this, 'Options1', ['atts' => [
                'edit' => ['noNumberUnit' => Sports::$noNumberUnitExtra, 'unit' => ['placeHolder' => $this->tr('Select') . ' ...']]
                //'storeedit' => ['formatType' => 'numberunit'],
                //'overview' => ['formatType' => 'numberunit'],
            ]]),
            'extra1'=>ViewUtils::numberUnitBox('extra1', $this, 'Options2', ['atts' => [
                'edit' => ['noNumberUnit' => Sports::$noNumberUnitExtra1, 'unit' => ['placeHolder' => $this->tr('Select') . ' ...']]
            ]]),
            'progression' => ViewUtils::lazyEditor($this, 'progression', ['atts' => ['edit' => ['height' => '300px']]]),
            'comments' => ['atts' => ['edit' => ['height' => '300px']]]
        ];
    }
}
?>

