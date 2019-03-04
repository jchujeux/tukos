<?php
namespace TukosLib\Objects\Sports\Exercises;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '40em']]],],
            'level1'    => ViewUtils::storeSelect('level1', $this, 'Level1'),
            'level2'    => ViewUtils::storeSelect('level2', $this, 'Level2', true, ['atts' => ['edit' => ['dropdownFilters' => ['level1' => '@level1']]]]),
            'level3'    => ViewUtils::storeSelect('level3', $this, 'Level3', true, ['atts' => ['edit' => ['dropdownFilters' => ['level1' => '@level1']]]]),
            'visual'  => ViewUtils::lazyEditor($this, 'Visual'),
            'protocol'  => ViewUtils::lazyEditor($this, 'Protocol'),
        ];
        $this->mustGetCols = array_merge($this->mustGetCols, ['level1', 'level2', 'level3', 'comments']);
        $this->customize($customDataWidgets);
    }    
}
?>

