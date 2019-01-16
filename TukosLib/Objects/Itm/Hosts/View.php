<?php
/**
 *
 * class for viewing methods and properties for the Connexions model object
 */
namespace TukosLib\Objects\Itm\Hosts; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;


class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Owner organization', 'Description');

        $customDataWidgets = [
            'osfamily'    => ViewUtils::storeSelect('osFamily', $this, 'OS family'),
            'lastinvscan' => ViewUtils::dateTimeBoxDataWidget($this, 'Last inventory scan'),
            'lastsecscan' => ViewUtils::dateTimeBoxDataWidget($this, 'Last security scan'),
            'trust'       => ViewUtils::storeSelect('trust', $this, 'Trust level'),  
            'reason'      => ViewUtils::textBox($this, 'Reason', ['atts' => ['edit' =>  ['style' => ['width' => '20em']]]]),
        ];

        $subObjects['hostsdetails']    = ['atts' => ['title' => $this->tr('Host details (nmap)')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['servicesdetails'] = ['atts' => ['title' => $this->tr('services details')]   , 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['connexions']      = ['atts' => ['title' => $this->tr('connexions details')] , 'filters'   => ['hostid'   => '@id'], 'allDescendants' => true];

        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
