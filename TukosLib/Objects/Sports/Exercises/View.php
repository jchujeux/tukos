<?php
namespace TukosLib\Objects\Sports\Exercises;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customDataWidgets = [
            'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '40em']]],],
            'level1'    => ViewUtils::objectSelect($this, 'Level1', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 1, 'parentid' => '@parentid']]]]),
            'level2'    => ViewUtils::objectSelect($this, 'Level2', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 2]]]]),
            'level3'    => ViewUtils::objectSelect($this, 'Level3', 'sptexerciseslevels', ['atts' => ['edit' => ['dropdownFilters' => ['level' => 3]]]]),
        ];
        $this->mustGetCols = array_merge($this->mustGetCols, ['level1', 'level2', 'level3', 'comments']);
        $this->customize($customDataWidgets);
    }    
}
?>

