<?php
/**
 *
 * class for viewing methods and properties for the Connexions model object
 */
namespace TukosLib\Objects\ITM\Connexions; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Networks', 'Description');
        $customDataWidgets = [
            'parentid'  => ['atts' => ['edit' => ['disabled' => true]]],
            'hostid'        => ViewUtils::objectSelectMulti('hostid', $this, 'Host'),
            'macid'          => ViewUtils::objectSelectMulti('macid' , $this, 'Mac address'),
            'ip'             => ViewUtils::textBox($this, 'IP address', ['atts' => ['edit' =>  ['style' => ['width' => '7em'], 'disabled' => true]]]),
            'firstconnect'   => ViewUtils::dateTimeBoxDataWidget($this, 'First connexion', ['atts' => ['edit' => ['disabled' => true]]]),
            'lastconnect'    => ViewUtils::dateTimeBoxDataWidget($this, 'Last connexion' , ['atts' => ['edit' => ['disabled' => true]]]),
            'trust'          => ViewUtils::storeSelect('trust', $this, 'Trust level'),  
            'reason'         => ViewUtils::textBox($this, 'Reason', ['atts' => ['edit' =>  ['style' => ['width' => '20em']]]]),
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
