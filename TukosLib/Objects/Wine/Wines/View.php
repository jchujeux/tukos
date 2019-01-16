<?php
/**
 *
 * class for viewing methods and properties for the $wines model object
 */
namespace TukosLib\Objects\Wine\Wines;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Appellation', 'Description');
        $customDataWidgets = [
             'cuvee'     => ViewUtils::textBox($this, 'Cuvée', ['atts' => ['edit' =>  ['style' => ['width' => '8em']]]]),
             'grading'   => ViewUtils::storeSelect('grading', $this, 'Grading'),
             'bottledby' => ViewUtils::objectSelectMulti('bottledby', $this, 'Producer'),
             'grape'     => ViewUtils::storeSelect('grape'   , $this, 'Grape'),
             'category'  => ViewUtils::storeSelect('category', $this, 'Category'),
             'color'     => ViewUtils::storeSelect('color'   , $this, 'Color'),
             'sugar'     => ViewUtils::storeSelect('sugar'   , $this, 'Sugar'),
            ];
        $subObjects = [
        	'winestock' => [
	            'atts' => [
	                'title'     => $this->tr('Wineinstock'),
	            ],
	            'filters'   => ['parentid' => '@id', ['col' => 'quantity', 'opr' => '>', 'values' => 0]],
	            'allDescendants' => false
        	],
        	'wineinputs' => [
        		'atts' => ['title' => $this->tr('Wineinputs')], 
        		'filters' => ['winesid' => '@id'],
        		'allDescendants' => true
        	]
        ];
        $this->customize($customDataWidgets, $subObjects);
    }    


}
?>
