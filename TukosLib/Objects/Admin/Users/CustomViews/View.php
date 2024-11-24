<?php
namespace TukosLib\Objects\Admin\Users\CustomViews;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'View Name');
        $untranslator = new ObjectTranslator($objectName, null, 'untranslator');
        $customDataWidgets = [
            //'parentid' => ['atts' => ['edit' => ['hidden' => true]]],
            'comments' => ['atts' => ['edit' => ['height' => '150px']]],
            'vobject'     => ViewUtils::storeSelect('vobject', $this, 'Object', null, ['objToEdit' => ['strtolower' => []]]),
            'view'       => ViewUtils::storeSelect('view', $this, 'View'),
            'panemode'       => ViewUtils::storeSelect('panemode', $this, 'Pane mode'),
            'customization' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('Customization'), 'keyToHtml' => 'capitalToBlank', 'hasCheckboxes' => true, 'isEditTabWidget' => true, 'checkedServerValue' => '~delete', 'onCheckMessage' => $this->tr('checkedleaveswillbedeletedonsave'),
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto']]],
                'objToEdit' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
                'editToObj' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $untranslator->tr]],
            ],
        ];

        $this->customize($customDataWidgets);

    }    
}
?>
