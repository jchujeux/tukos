<?php
namespace TukosLib\Objects\BusTrack\Categories;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customDataWidgets = [
            'name' => ['atts' => ['edit' => ['style' => ['width' => ['30em']]]]],
            'vatfree' => ViewUtils::checkBox($this, 'vatfree'/*, ['atts' => ['edit' => [
                'onWatchLocalAction' => ['checked' => ['vatfree' => ['value' => ['triggers' => ['user' => true, 'server' => true], 'action' => "return newValue ? 'vatfree' : '';"]]]]]]]*/),
            'vatrate' => ViewUtils::tukosNumberBox($this, 'Vatrate', ['atts' => [
                'edit' => ['title' => $this->tr('Vatrate') . ' %', 'style' => ['width' => '10em'], 'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%']],
                'storeedit' => ['formatType' => 'percent', 'width' => 80],
                'overview' => ['formatType' => 'percent', 'width' => 80]
            ]]),
            'applytocustomers' => ViewUtils::checkBox($this, 'Applytocustomers'),
            'applytosuppliers' => ViewUtils::checkBox($this, 'Applytosuppliers'),
            'filterpriority' => ViewUtils::numberTextBox($this, 'Filterpriority'),
            'criteria' => ViewUtils::JsonGrid($this, 'Filtercriteria', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'customertype'    => ViewUtils::storeSelect('customerType', $this, 'Customertype'),                'attribute'  => ViewUtils::storeSelect('attributeType', $this, 'Filterattribute'),
                'value'  => ViewUtils::textBox($this, 'Filtervalue', ['atts' => ['storeedit' => ['alternateEditors' => ['segment' => Widgets::description(ViewUtils::storeSelect('segment', $this, 'Segment'), true)], 
                    'editorSelector' => $this->editorSelector()]]]),
                'removematch' => ViewUtils::checkBox($this, 'Removematch', ['atts' => ['storeedit' => ['width' => 80]]]),
                'searchpayments' => ViewUtils::checkBox($this, 'SearchPayments', ['atts' => ['storeedit' => ['width' => 80]]]),
                'searchinvoices' => ViewUtils::checkBox($this, 'SearchInvoices', ['atts' => ['storeedit' => ['width' => 80]]]),
            ]),
         ];
        $subObjects['bustrackcategories'] = ['atts' => ['title' => $this->tr('Categories')], 'filters' => ['parentid' => '@parentid'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects, [], ['criteria' => []]);
    }
    function editorSelector(){
        return <<<EOT
    (function(grid){
        var data = grid.clickedCell.row.data;
        return (data || {}).attribute === 'segment' ? 'segment' : false;
    })
EOT;
    }
}
?>
