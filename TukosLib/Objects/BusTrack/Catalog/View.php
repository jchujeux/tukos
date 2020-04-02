<?php
namespace TukosLib\Objects\BusTrack\Catalog;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\BusTrack\ViewActionStrings as VAS;

class View extends AbstractView {
    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');
        $customDataWidgets = [
            'category' => ViewUtils::ObjectSelect($this, 'Category', 'bustrackcategories', ['atts' => ['edit' => [
                'storeArgs' => ['cols' => ['vatfree']],
                'onWatchLocalAction' => ['value' => ['vatfree' => ['checked' => ['triggers' => ['user' => true, 'server' => false], 'action' => "return sWidget.getItem('vatfree') ? true : false;"]]]]
            ]]]),
            'vatfree' => ViewUtils::checkBox($this, 'vatfree', ['atts' => ['edit' => [
                'onWatchLocalAction' => ['checked' => ['vatfree' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => false], 'action' => VAS::vatfreeLocalAction(true)]]]]]]]),
            'vatrate' => ViewUtils::tukosNumberBox($this, 'VATRate', ['atts' => [
                'edit' => ['constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'],
                    'style' => ['width' => '5em'],
                    'onWatchLocalAction' => ['value' => ['vatrate' => ['localActionStatus' => ['triggers' => ['user' => true, 'server' => false], 'action' => VAS::vatRateLocalAction(true)]]]]
                ],
                'storeedit' => ['formatType' => 'percent', 'width' => 80],
                'overview' => ['formatType' => 'percent', 'width' => 80]
            ]]),
            'unitpricewot'  => ViewUtils::tukosCurrencyBox($this, 'Unitpricewot', ['atts' => [
            	'edit' =>  ['style' => ['width' => '5em'], 'onChangeLocalAction' => [
                	'unitpricewt'    => ['value' => "return newValue * (1+sWidget.valueOf('#vatrate'));" ]
               	]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80],
                'overview' => ['formatType' => 'currency', 'width' => 80]
        	]]),
        	'unitpricewt'  => ViewUtils::tukosCurrencyBox($this, 'Unitpricewt', ['atts' => [
                'edit' =>  ['style' => ['width' => '5em'], 'onChangeLocalAction' => [
                    'unitpricewot'    => ['value' => "return newValue / (1+sWidget.valueOf('#vatrate'));" ]
                ]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80],
                'overview' => ['formatType' => 'currency', 'width' => 80]
        	]]),
        		
        ];
        $this->customize($customDataWidgets);
    }    
}
?>
