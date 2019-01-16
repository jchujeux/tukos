<?php
/**
 *
 * class for viewing methods and properties for the $winestock model object
 */
namespace TukosLib\Objects\Wine\Stock;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Wine', 'Description');
        $customDataWidgets = [
            'cellarid' => ViewUtils::objectSelectMulti('cellarid', $this, 'Cellar'),
            'vintage'  => [
                'type' => 'storeSelect',   
                'atts' => ['edit' =>  ['storeArgs' => ['data' => Utl::yearsStore(['prepend' => [['id' => 0, 'name' => $this->tr('novintage')]]])], 'title' => $this->tr('Vintage')]],
            ],
            'laydown'  => ViewUtils::storeSelect('laydown', $this, 'Laydown'),
            'format'   => ViewUtils::storeSelect('format', $this, 'Format'),
            'quantity' => ViewUtils::textBox($this, 'Quantity', ['atts' => ['edit' =>  ['style' => ['width' => '6em']]]]),
        ];
        $this->customize($customDataWidgets);
    }   
}
?>
