<?php
namespace TukosLib\Objects\Admin\Users\CustomViews;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'View Name');
        $customDataWidgets = [
            'parentid' => ['atts' => ['edit' => ['hidden' => true]]],
            'comments' => ['atts' => ['edit' => ['height' => '250px']]],
            'vobject'     => ViewUtils::storeSelect('vobject', $this, 'Object', null, ['objToEdit' => ['strtolower' => []]]),
            'view'       => ViewUtils::storeSelect('view', $this, 'View'),
            'panemode'       => ViewUtils::storeSelect('panemode', $this, 'Pane mode'),
        	//'customization' => ['type' => 'textArea',     'atts' => ['edit' =>  ['title' => $this->tr('Customization'), 'colspan' => '6' ]]],
            'customization' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('Customization'), 'keyToHtml' => 'capitalToBlank'/*, 'hasCheckboxes' => true*/, 'isEditTabWidget' => true,
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto']]],
                'objToEdit' => [/*'jsonDecode' => ['class' => $utl],  */'map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
            ],
        ];

        $this->customize($customDataWidgets);

    }    
}
?>
