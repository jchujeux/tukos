<?php
/**
 *
 * class for viewing methods and properties for the MacAddresses model object
 */
namespace TukosLib\Objects\ITM\MacAddresses; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Host', 'Description');
        $customDataWidgets = [
            'mac'       => ViewUtils::textBox($this, 'Mac address'),
            'vendor'    => ViewUtils::textBox($this, 'Vendor'),
            'trust'     => ViewUtils::storeSelect('trust', $this, 'Trust level'),  
            'reason'    => ViewUtils::textBox($this, 'Reason', ['atts' => ['edit' =>  ['style' => ['width' => '20em']]]]),
        ];

        $subObjects['connexions'] =   ['atts' => ['title'    => $this->tr('Connexions')], 'filters'   => ['macid' => '@id'], 'allDescendants' => true];

        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
