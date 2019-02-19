<?php
namespace TukosLib\Objects\Admin\Users\CustomViews;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'User', 'View Name');
        $customDataWidgets = [
            'vobject'     => ViewUtils::storeSelect('vobject', $this, 'Object'),
            'view'       => ViewUtils::storeSelect('view', $this, 'View'),
            'panemode'       => ViewUtils::storeSelect('panemode', $this, 'Pane mode'),
        	'customization' => ['type' => 'textArea',     'atts' => ['edit' =>  ['title' => $this->tr('Customization'), 'colspan' => '6' ]]],
       ];

        $this->customize($customDataWidgets);

    }    
}
?>
