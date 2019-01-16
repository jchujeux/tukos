<?php
namespace TukosLib\Objects\Physio\Templates;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '20em;']]],],
            'templatetype'     => ViewUtils::storeSelect('templateType', $this, 'TemplateType'),
        ];

        $subObjects['physiotemplates'] = [
            'atts'           => ['title' => $this->tr('physiotemplates')],
            'filters'        => ['parentid' => '@id'],
            'allDescendants' => true,
        ];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>

