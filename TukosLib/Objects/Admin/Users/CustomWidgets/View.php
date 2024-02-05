<?php
namespace TukosLib\Objects\Admin\Users\CustomWidgets;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'CustomName');
        $customDataWidgets = [
            'parentid' => ['atts' => ['edit' => ['hidden' => true]]],
            'comments' => ['atts' => ['edit' => ['height' => '250px']]],
            'vobject'     => ViewUtils::storeSelect('vobject', $this, 'Object', null, ['objToEdit' => ['strtolower' => []]]),
            'widgettype'       => ViewUtils::textBox($this, 'Widgettype'),
            'customization' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('Customization'), 'keyToHtml' => 'capitalToBlank'/*, 'hasCheckboxes' => true*/, 'isEditTabWidget' => true,
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto']]],
                //'objToEdit' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
            ],
        ];
        $this->customize($customDataWidgets);

    }    
}
?>
