<?php
namespace TukosLib\Objects\Sports\Exercises\Levels;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'level' => ViewUtils::storeSelect('level', $this, 'Level', [true, 'ucfirst', true, false])
        ];
        $subObjects['sptexerciseslevels'] = ['atts' => ['title' => $this->tr('Sptexerciseslevels')], 'filters' => ['parentid' => '@parentid'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }
}
?>
