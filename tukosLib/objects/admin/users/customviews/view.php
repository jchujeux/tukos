<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Users\CustomViews;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\UsersContexts\Object as UsersContexts;
use TukosLib\Objects\UsersContexts\View as UsersContextsView;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'User', 'View Name');
        $customDataWidgets = [
            'vobject'     => ViewUtils::storeSelect('vobject', $this, 'Object'),
            'view'       => ViewUtils::storeSelect('view', $this, 'View'),
            'customization' => ['type' => 'textArea',     'atts' => ['edit' =>  ['title' => $this->tr('Customization'), 'colspan' => '6' ]]],
       ];

        $this->customize($customDataWidgets);

    }    
}
?>
