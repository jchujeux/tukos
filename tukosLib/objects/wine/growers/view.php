<?php
namespace TukosLib\Objects\Wine\Growers;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent organization', 'Organization name');

        $customDataWidgets = [
            'segment' => ViewUtils::storeSelect('segment', $this, 'Segment'),
            'logo'    => ViewUtils::textBox($this, 'Logo'),
        ];
        $subObjects = [
        	'wines' => ['atts' => ['title' => $this->tr('Wines')], 'filters' => ['bottledby' => '@id'], 'allDescendants' => true],
        	'winestock'=> [
        		'atts' => ['title' => $this->tr('Wineinstock')], 
        		'filters' => [['col' => 'parentid', 'opr' => 'IN SELECT', 'values' => ['where' => ['bottledby' => '@id'], 'table' => 'wines', 'cols' => ['id']]], ['col' => 'quantity', 'opr' => '>', 'values' => 0]],
        		'allDescendants' => true
        	],
        	'wineinputs' => [
        		'atts' => ['title' => $this->tr('Wineinputs')], 
        		'filters' => [['col' => 'winesid', 'opr' => 'IN SELECT', 'values' => ['where' => ['bottledby' => '@id'], 'table' => 'wines', 'cols' => ['id']]]],
        		'allDescendants' => true
        	]

        ];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
