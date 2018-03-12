<?php
/**
 *
 * 
 */
namespace TukosLib\Objects\Wine\Regions;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'User', 'Region name');
        $customDataWidgets = [
            'parentid' => ['atts' => ['edit' => ['hidden' => true]]], 
            'country'  => ViewUtils::textBox($this, 'Country'),
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
