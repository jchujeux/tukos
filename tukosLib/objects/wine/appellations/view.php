<?php
/**
 *
 * class for viewing methods and properties for the $wines model object
 */
namespace TukosLib\Objects\Wine\Appellations;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Region', 'Appellation');
        $customDataWidgets = [
             //'region'    => ViewUtils::textBox($this, 'Region'),
            'subdivision'=> ViewUtils::textBox($this, 'Subdivision'),
             //'country'   => ViewUtils::textBox($this, 'Country'),
            ];
        $subObjects = [
	        'wines'=> [
	            'atts' => [
	                'title'     => $this->tr('Winesknown'),
	            ],
	            'filters'   => ['parentid' => '@id'],
	            'allDescendants' => false
	        ],
        	'winestock'=> [
        		'atts' => ['title' => $this->tr('Wineinstock')], 
        		'filters' => [['col' => 'parentid', 'opr' => 'IN SELECT', 'values' => ['where' => ['parentid' => '@id'], 'table' => 'wines', 'cols' => ['id']]], ['col' => 'quantity', 'opr' => '>', 'values' => 0]],
        		'allDescendants' => true
        	],
        	'wineinputs' => [
        		'atts' => ['title' => $this->tr('Wineinputs')], 
        		'filters' => [['col' => 'winesid', 'opr' => 'IN SELECT', 'values' => ['where' => ['parentid' => '@id'], 'table' => 'wines', 'cols' => ['id']]]],
        		'allDescendants' => true
        	]
	    ];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
