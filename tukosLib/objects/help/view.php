<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Help;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parenthelp', 'Description');
        $customDataWidgets = [
            'language'  => ViewUtils::storeSelect('language', $this, 'Language'),
            ];
        $subObjects['help'] = ['atts' => ['title'     => $this->tr('Relatedhelp')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
