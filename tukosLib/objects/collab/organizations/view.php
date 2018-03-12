<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Collab\Organizations;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent organization', 'Organization name');

        $customDataWidgets = [
            'segment' => ViewUtils::storeSelect('segment', $this, 'Segment'),
            'logo'    => ViewUtils::textBox($this, 'Logo'),
        ];
        $subObjects['people']        = ['atts' => ['title' => $this->tr('People')]           , 'filters' => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['organizations'] = ['atts' => ['title' => $this->tr('sub-organizations')], 'filters' => ['parentid' => '@id'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
