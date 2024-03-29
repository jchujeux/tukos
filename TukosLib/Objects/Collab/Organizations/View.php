<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Collab\Organizations;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent organization', 'Organization name');

        $customDataWidgets = $this->customDataWidgets();
        $subObjects['people']        = ['atts' => ['title' => $this->tr('People')]           , 'filters' => ['parentid' => '@id'], 'allDescendants' => true];
        $subObjects['organizations'] = ['atts' => ['title' => $this->tr('sub-organizations')], 'filters' => ['parentid' => '@id'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
    }
    function customDataWidgets(){
        return [
            'comments' => ['atts' => ['edit' => ['height' => '150px']]],
            'segment' => ViewUtils::storeSelect('segment', $this, 'Segment'),
            'weburl' => ViewUtils::textBox($this, 'Weburl'),
            'trigram' => ViewUtils::textBox($this, 'Trigram', ['atts' => ['edit' => ['style' => ['width' => '3em']]]]),
            'logo'    => ViewUtils::lazyEditor($this, 'Logo', ['atts' => ['edit' => ['height' => '60px']]]),
            'defaultfooter' => ViewUtils::lazyEditor($this, 'Defaultfooter', ['atts' => ['edit' => ['height' => '60px']]]),
            'headofficeaddress' => ViewUtils::textArea($this, 'HeadOfficeAddress'),
            'invoicingaddress' => ViewUtils::textArea($this, 'InvoicingAddress'),
            'vatid' => ViewUtils::textBox($this, 'Vatid'),
            'vatmode' => ViewUtils::storeSelect('vatmode', $this, 'Vatmode'),
            'legalid' => ViewUtils::textBox($this, 'LegalId'),
            'judicialform' => ViewUtils::textBox($this, 'JudicialForm'),
            'sharecapital' => ViewUtils::textBox($this, 'ShareCapital')
        ];
    }
}
?>
