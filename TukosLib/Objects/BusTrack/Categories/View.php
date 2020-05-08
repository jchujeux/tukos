<?php
namespace TukosLib\Objects\BusTrack\Categories;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;

class View extends AbstractView {
    
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customDataWidgets = [
            'vatfree' => ViewUtils::checkBox($this, 'vatfree', ['atts' => ['edit' => [
                'onWatchLocalAction' => ['checked' => ['vatfree' => ['value' => ['triggers' => ['user' => true, 'server' => true], 'action' => "return newValue ? 'vatfree' : '';"]]]]]]]),
            'vatrate' => ViewUtils::tukosNumberBox($this, 'Vatrate', ['atts' => [
                'edit' => ['title' => $this->tr('Vatrate') . ' %', 'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%']],
                'storeedit' => ['formatType' => 'percent', 'width' => 80],
                'overview' => ['formatType' => 'percent', 'width' => 80]
            ]]),
            'criteria' => ViewUtils::JsonGrid($this, 'Filtercriteria', [
                'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
                'customertype'    => ViewUtils::storeSelect('customerType', $this, 'Customertype'),                'attribute'  => ViewUtils::storeSelect('attributeType', $this, 'Filterattribute'),
                'value'  => ViewUtils::textBox($this, 'Filtervalue', ['atts' => ['storeedit' => ['alternateEditors' => ['segment' => Widgets::description(ViewUtils::storeSelect('segment', $this, 'Segment'), true)], 
                    'editorSelector' => $this->editorSelector()]]]),
            ]),
         ];
        $subObjects['bustrackcategories'] = ['atts' => ['title' => $this->tr('Categories')], 'filters' => ['parentid' => '@parentid'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects, ['grid' => ['criteria']], ['criteria' => []]);
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
