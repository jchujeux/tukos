<?php
namespace TukosLib\Objects\BusTrack\Catalog;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Organization', 'Description');

        $customDataWidgets = [
        	'unitpricewot'  => ViewUtils::tukosCurrencyBox($this, 'Unitpricewot', ['atts' => [
            	'edit' =>  ['style' => ['width' => '5em'], 'onChangeLocalAction' => [
                	'unitpricewt'    => ['value' => "return newValue * (1+sWidget.valueOf('#vatrate'));" ]
               	]],
                'storeedit' => ['formatType' => 'currency', 'width' => 80],
                'overview' => ['formatType' => 'currency', 'width' => 80]
        	]]),
        	'vatrate' => ViewUtils::tukosNumberBox($this, 'VATRate', ['atts' => [
        		'edit' => ['constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'],
        			'style' => ['width' => '5em'],
        			'onChangeLocalAction' => ['unitpricewt'  => ['value' => "return sWidget.valueOf('#unitpricewot') *  (1 + newValue);" ]],
        		],
        		'storeedit' => ['formatType' => 'percent', 'width' => 80],
        		'overview' => ['formatType' => 'percent', 'width' => 80]
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
