<?php
/**
 * class for viewing methods and properties for the HostsDetails model object
 */
namespace TukosLib\Objects\Itm\HostsDetails; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){

        parent::__construct($objectName, $translator, 'Host', 'Description');
        $customDataWidgets = [
             'status'    => ViewUtils::storeSelect('status', $this, 'Status', null, ['atts' => ['edit' =>  ['disabled' => true]]]),
             'hostname'  => ViewUtils::textBox($this, 'Host name'    , ['atts' => ['edit' =>  ['disabled' => true]]]),
             'type'      => ViewUtils::textBox($this, 'Device type'  , ['atts' => ['edit' =>  ['disabled' => true]]]),
             'vendor'    => ViewUtils::textBox($this, 'OS Vendor'    , ['atts' => ['edit' =>  ['disabled' => true]]]),
             'osfamily'  => ViewUtils::textBox($this, 'OS family'    , ['atts' => ['edit' =>  ['disabled' => true]]]),
             'osgen'     => ViewUtils::textBox($this, 'OS generation', ['atts' => ['edit' =>  ['disabled' => true]]]),
             'accuracy'  => ['type' => 'numberTextBox', 'atts' => ['edit' =>  ['title' => $this->tr('Accuracy'), 'disabled' => true]]],
             'upsince'   => ViewUtils::timeStampDataWidget($this, 'Up since' , ['atts' => ['edit' => ['disabled' => true]]]),
             'timescanned'=>ViewUtils::timeStampDataWidget($this, 'Scan date', ['atts' => ['edit' => ['disabled' => true]]]),
            ];
        $this->customize($customDataWidgets);
    }    
}
?>
