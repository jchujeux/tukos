<?php
namespace TukosLib\Objects\BusTrack\Categories;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\BusTrack\BusTrack;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customDataWidgets = [
            'vatfree' => ViewUtils::checkBox($this, 'vatfree', ['atts' => ['edit' => [
                'onWatchLocalAction' => ['checked' => ['vatfree' => ['value' => ['triggers' => ['user' => true, 'server' => true], 'action' => "return newValue ? 'vatfree' : '';"]]]]]]]),
            ];
        $subObjects['bustrackcategories'] = ['atts' => ['title' => $this->tr('Categories')], 'filters' => ['parentid' => '@parentid'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }
}
?>
