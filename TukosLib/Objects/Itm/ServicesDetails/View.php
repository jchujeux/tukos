<?php
namespace TukosLib\Objects\Itm\ServicesDetails; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Host', 'Desription');
        $customDataWidgets = [
            'name'      => ['atts' => ['edit' =>  ['title' =>$this->tr('Description'), 'disabled' => true]]],
            'port'      => ['type' => 'numberTextBox', 'atts' => ['edit' =>  ['title' => $this->tr('Port'), 'disabled' => true]]],
            'protocol'  => ViewUtils::textBox($this, 'Protocol', ['atts' => ['edit' =>  ['disabled' => true]]]),
            'product'   => ViewUtils::textBox($this, 'Product' , ['atts' => ['edit' =>  ['disabled' => true]]]),
            'version'   => ViewUtils::textBox($this, 'Version' , ['atts' => ['edit' =>  ['disabled' => true]]]),
            'timescanned'=>ViewUtils::timeStampDataWidget($this, 'Time scanned', ['atts' => ['edit' => ['disabled' => true]]]),
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
