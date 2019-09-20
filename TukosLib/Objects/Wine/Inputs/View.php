<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Wine\Inputs;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Cellar', 'Description');

        $customDataWidgets = [
            'winesid'   => ViewUtils::objectSelectMulti('winesid', $this, 'Wine', ['atts' => ['edit' => [
                            'onChangeLocalAction' => [
                                'name'  => ['value' =>
                                    "return Pmg.itemName(newValue) + '-' + sWidget.valueOf('#vintage') + '-' + sWidget.valueOf('#format');" 
                                ],
                            ],
                        ],
                    ],
                ]
            ),
            'entrydate' => ['type' => 'tukosDateBox',  'atts' => ['edit' =>  ['title' => $this->tr('Input date'), 'style' => ['width' => '6em']]]], 
            'status'    => ViewUtils::storeSelect('status', $this, 'Status'),
            'vintage'   => [
                'type' => 'storeSelect',
                'atts' => ['edit' =>  [
                        'storeArgs' => ['data' => Utl::yearsStore(['prepend' => [['id' => 0, 'name' => $this->tr('novintage')]]])], 'title' => $this->tr('Vintage'),
                        'onChangeLocalAction' => [
                                'name'  => ['value' =>
                                    "return Pmg.itemName(sWidget.valueOf('#winesid')) + '-' + newValue + '-' + sWidget.valueOf('#format');" 
                                ],
                            ],
                    ]
                ]
            ],
            'cost'      => ViewUtils::tukosCurrencyBox($this, 'Cost'),
            'whereobtained' => ViewUtils::storeSelect('whereObtained', $this, 'Obtained at'),
            'format'        => ViewUtils::storeSelect('format', $this, 'Format', null, ['atts' => ['edit' => [
                            'onChangeLocalAction' => [
                                'name'  => ['value' =>
                                    "return Pmg.itemName(sWidget.valueOf('#winesid')) + '-' + sWidget.valueOf('#vintage') + '-' + newValue;" 
                                ],
                            ],
                        ],
                    ],
                ]
            ),
            'quantity'      => ViewUtils::textBox($this, 'Quantity', ['atts' => ['edit' =>  ['style' => ['width' => '4em']]]]),
        ];
        $this->customize($customDataWidgets);
    }    

    function overviewDescription($custom = ['actions' => ['donothing', 'delete', 'duplicate', 'modify', 'process']]){
        return parent::overviewDescription($custom);
    }
}
?>
