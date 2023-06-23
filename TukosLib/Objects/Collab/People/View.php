<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Collab\People;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Lastname');

        $customDataWidgets = $this->customDataWidgets();
        /*$subObjects['tasks'] = [
            'atts'  => ['title' => $this->tr('Assigned tasks'),],
            'filters' => ['responsible' => '@id', 'completed' => ['<', 1],//['col' => 'completed', 'opr' => '<', 'values' => 1],
            ],
            'allDescendants' => true,
            //'allDescendants' => 'hasChildrenOnly',
        ];*/
        $subObjects['notes'] = [
            'atts' => ['title' => $this->tr('Notes'), 'storeType' => 'LazyMemoryTreeObjects'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => 'hasChildrenOnly'
        ];
        $this->customize($customDataWidgets, $subObjects);
    }
    function customDataWidgets(){
        return [
            'comments' => ['atts' => ['edit' => ['height' => '150px']]],
            'firstname'  => ViewUtils::textBox($this, 'Firstname'),
            'middlename' => ViewUtils::textBox($this, 'Middlename'),
            'title'      => ViewUtils::textBox($this, 'Title'),
            'email'      => ViewUtils::textBox($this, 'email', ['atts' => ['edit' =>  ['placeHolder' => 'xxx@yyy']]]),
            'teloffice'  => ViewUtils::textBox($this, 'Teloffice'),
            'telhome'    => ViewUtils::textBox($this, 'Telhome'),
            'telmobile'  => ViewUtils::textBox($this, 'Telmobile'),
            'picture'    => ViewUtils::textBox($this, 'Picture'),
            'street'     => ViewUtils::textBox($this, 'Streetaddress'),
            'postalcode' => ViewUtils::textBox($this, 'Postalcode'),
            'city'       => ViewUtils::textBox($this, 'City'),
            'country'    => ViewUtils::storeSelect('country', $this, 'Country'),
            'postaladdress'     => ViewUtils::textArea($this, 'Postaladdress', ['atts' => ['edit' =>  ['onChangeLocalAction' => ['invoicingaddress'    => ['localActionStatus' => <<<EOT
var invoicingAddress = tWidget.get('value');
if (!invoicingAddress || oldValue === invoicingAddress){
    tWidget.set('value', newValue);
}
EOT
            ]]]]]),
            'invoicingaddress'  => ViewUtils::textArea($this, 'Invoicingaddress', ['atts' => ['edit' =>  ['onChangeLocalAction' => ['postaladdress'    => ['localActionStatus' => <<<EOT
if (!tWidget.get('value')){
    tWidget.set('value', newValue);
}
EOT
            ]]]]]),
            'birthdate'  => ViewUtils::tukosDateBox($this, 'Birthdate'),
        ];
    }
}
?>
