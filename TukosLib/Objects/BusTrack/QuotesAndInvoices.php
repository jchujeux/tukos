<?php
namespace TukosLib\Objects\BusTrack;

use TukosLib\Objects\ViewUtils;

trait QuotesAndInvoices {

	protected function items($labels){
	  return ViewUtils::JsonGrid($this, 'Details', [
		'rowId' => ['field' => 'rowId', 'label' => '', 'width' => 40, 'className' => 'dgrid-header-col', 'hidden' => true],
		'catalogid'    => ViewUtils::objectSelect($this, $labels['catalogid'], 'bustrackcatalog', ['atts' => ['storeedit' => ['width' => 100]]]),
		'name'  => ViewUtils::textBox($this, $labels['name']),
		'comments' => ViewUtils::editor($this, $labels['comments']),
		'quantity'  => ViewUtils::textBox($this, $labels['quantity'], ['atts' => [
				'edit' =>  ['style' => ['width' => '4em'], 'onChangeLocalAction' => ['quantity' => ['localActionStatus' =>
						"var reduction = 1 - sWidget.valueOf('#discount'), priceWot = newValue * sWidget.valueOf('#unitpricewot') * reduction, priceWt = newValue * sWidget.valueOf('#unitpricewt') * reduction;\n" .
						"sWidget.setValueOf('#pricewot', priceWot);\n" .
						"sWidget.setValueOf('#pricewt', priceWt);\n" .
						"return true;\n"
				]]],
				'storeedit' => ['width' => 80]
		]]),
		'unitpricewot'  => ViewUtils::tukosCurrencyBox($this, $labels['unitpricewot'], ['atts' => [
				'edit' =>  ['style' => ['width' => '4em'], 'onChangeLocalAction' => ['unitpricewot' => ['localActionStatus' =>
						"var reduction = 1 -  sWidget.valueOf('#discount'), quantity = sWidget.valueOf('#quantity'), vatFactor = 1+sWidget.valueOf('#vatrate'), newUnitPriceWt = newValue * vatFactor;\n" .
						"sWidget.setValueOf('#unitpricewt', newUnitPriceWt);\n" .
						"sWidget.setValueOf('#pricewot', quantity *  newValue * reduction);\n" .
						"sWidget.setValueOf('#pricewt', quantity *  newUnitPriceWt * reduction);\n" .
						"return true;\n"
				]]],
				'storeedit' => ['formatType' => 'currency', 'width' => 80]
		]]),
		'unitpricewt'  => ViewUtils::tukosCurrencyBox($this, $labels['unitpricewt'], ['atts' => [
				'edit' =>  ['style' => ['width' => '4em'], 'onChangeLocalAction' => ['unitpricewot' => ['localActionStatus' =>
						"var reduction = 1 -  sWidget.valueOf('#discount'), quantity = sWidget.valueOf('#quantity'), vatFactor = 1+sWidget.valueOf('#vatrate'), newUnitPriceWot = newValue / vatFactor;\n" .
						"sWidget.setValueOf('#unitpricewot', newUnitPriceWot);\n" .
						"sWidget.setValueOf('#pricewot', quantity *  newUnitPriceWot * reduction);\n" .
						"sWidget.setValueOf('#pricewt', quantity *  newValue * reduction);\n" .
						"return true;\n"
				]]],
				'storeedit' => ['formatType' => 'currency', 'width' => 80]
		]]),
		'discount' => ViewUtils::tukosNumberBox($this, $labels['discount'], ['atts' => [
				'edit' => ['title' => $this->tr($labels['discount']) . ' %', 'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'],
						'onChangeLocalAction' => ['discount' => ['localActionStatus' =>
								"var reduction = 1 -  newValue, quantity = sWidget.valueOf('#quantity'), unitPriceWot = sWidget.valueOf('#unitpricewot'), unitPriceWt = sWidget.valueOf('#unitpricewt');\n" .
								"sWidget.setValueOf('#pricewot', quantity *  unitPriceWot * reduction);\n" .
								"sWidget.setValueOf('#pricewt', quantity *  unitPriceWt * reduction);\n" .
								"return true;\n"
						]]],
				'storeedit' => ['formatType' => 'percent', 'width' => 80]
		]]),
		'pricewot'  => ViewUtils::tukosCurrencyBox($this, $labels['pricewot'], ['atts' => [
				'edit' => ['onChangeLocalAction' => ['pricewot' => ['localActionStatus' =>
						"var quantity = sWidget.valueOf('#quantity'), unitPriceWot = sWidget.valueOf('#unitpricewot');\n" .
						"sWidget.setValueOf('discount', (quantity && unitPriceWot) ? 1 - newValue / quantity / unitPriceWot : '');\n" .
						"sWidget.setValueOf('pricewt', newValue * (1 + sWidget.valueOf('#vatrate')));\n" .
						"return true;\n"
				]]],
				'storeedit' => ['formatType' => 'currency', 'width' => 80]]]),
		'vatrate' => ViewUtils::tukosNumberBox($this, $labels['vatrate'], ['atts' => [
				'edit' => ['title' => $this->tr($labels['vatrate']) . ' %', 'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'],
						'onChangeLocalAction' => [
								'pricewt'  => ['value' => "return sWidget.valueOf('#pricewot') *  (1 + newValue);" ],
								'unitpricewt'  => ['value' => "return sWidget.valueOf('#unitpricewot') *  (1 + newValue);" ]
						]],
				'storeedit' => ['formatType' => 'percent', 'width' => 80]
		]]),

		'pricewt'  => ViewUtils::tukosCurrencyBox($this, $labels['pricewt'], ['atts' => [
				'edit' => ['onChangeLocalAction' => ['pricewt' => ['localActionStatus' =>
						"var quantity = sWidget.valueOf('#quantity'), unitPriceWt = sWidget.valueOf('#unitpricewt'), vatFactor = 1 + sWidget.valueOf('#vatrate');\n" .
						"sWidget.setValueOf('discount', (quantity && unitPriceWt) ? 1 - newValue / quantity / unitPriceWt : '');\n" .
						"sWidget.setValueOf('pricewot', newValue / vatFactor);\n" .
						"return true;\n"
				]]],
				'storeedit' => ['formatType' => 'currency', 'width' => 80]]])
		],
		['atts' => ['edit' => [
				'objectIdCols' => ['catalogid'], 'sort' => [['property' => 'rowId', 'descending' => false]],
				'summaryRow' => ['cols' => [
						'name' => ['content' =>  ['Total']],
						'pricewot' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#pricewot#);"]]],
						'pricewt' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#pricewt#);"]]]
				]],
				'dndParams' => ['selfAccept' => false, 'copyOnly' => true],
				'onDropMap' => [
						'catalog' => ['fields' => ['catalogid' => 'id', 'name' => 'name', 'comments' => 'comments', 'unitpricewot' => 'unitpricewot', 'vatrate' => 'vatrate', 'unitpricewt' => 'unitpricewt']],
				],
				'onWatchLocalAction' => ['summary' => ['items' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' =>
						//"var discountPc = sWidget.form.valueOf('discountpc');" .
						"var discountWt = sWidget.form.valueOf('discountwt'), priceWt = sWidget.summary.pricewt - discountWt, discountPc = discountWt / sWidget.summary.pricewt;\n" .
						"sWidget.form.setValueOf('pricewt', priceWt);\n" .
						"sWidget.form.setValueOf('pricewot', sWidget.summary.pricewot * (1 - discountPc));\n" .
						"sWidget.form.setValueOf('discountpc', discountWt === '' ? '' : discountPc);\n" .
						"return true;"
				]]]],
		]]]);
	}

	protected function discountPc($labels){
		return ViewUtils::tukosNumberBox($this, 'Globaldiscountpc', ['atts' => [
				'edit' => ['title' => $this->tr($labels['discount']) . ' %', 'constraints' => ['type' => 'percent', 'pattern' => '#.####%'], 'editOptions' => ['pattern' => '#.####%'], 'onChangeLocalAction' => [
						'discountpc'  => ['localActionStatus' =>
								"var form = sWidget.form, itemsW = form.getWidget('items'), newVal = isNaN(newValue) ? '' : newValue;\n" .
								"if (itemsW && itemsW.summary){\n" .
								"form.setValueOf('pricewot', itemsW.summary.pricewot * (1 -  newVal));\n" .
								"form.setValueOf('pricewt' , itemsW.summary.pricewt * (1 -  newVal));\n" .
								"form.setValueOf('discountwt', newVal == '' ? '' : itemsW.summary.pricewt * newVal);\n" .
								"}\n" .
								"return true;"
						],
				]],
				'storeedit' => ['formatType' => 'percent', 'width' => 80]
		]]);
	}
	protected function discountWt(){
		return ViewUtils::tukosCurrencyBox($this, 'Globaldiscountwt', ['atts' => [
				'edit' => ['style' => ['width' => '5em'], 'onChangeLocalAction' => [
						'discountwt'  => ['localActionStatus' =>
								"var form = sWidget.form, itemsW = form.getWidget('items'), newVal = isNaN(newValue) ? '' : newValue;\n" .
								"if (itemsW && itemsW.summary){\n" .
									"var newDiscount = newVal / itemsW.summary.pricewt;\n" .
									"form.setValueOf('discountpc', newVal == '' ? '' : newDiscount);\n" .
									"form.setValueOf('pricewt' , itemsW.summary.pricewt  -  newVal);\n" .
									"form.setValueOf('pricewot', itemsW.summary.pricewot * (1 -  newDiscount));\n" .
								"}\n" .
								"return true;"
						],
				]],
				'storeedit' => ['formatType' => 'currency', 'width' => 80]
		]]);
	}
	protected function priceWot(){
		return ViewUtils::tukosCurrencyBox($this, 'Totalwot', ['atts' => [
				'edit' => ['onChangeLocalAction' => [
						'pricewot'  => ['localActionStatus' =>
								"var form = sWidget.form, itemsW = form.getWidget('items'), newVal = isNaN(newValue) ? '' : newValue;\n" .
								"if (itemsW && itemsW.summary){\n" .
								"var newDiscount = 1 - newVal / itemsW.summary.pricewot;\n" .
								"form.setValueOf('discountpc', newVal == '' ? '' : newDiscount);\n" .
								"form.setValueOf('discountwt' , itemsW.summary.pricewt * newDiscount);\n" .
								"form.setValueOf('pricewt', itemsW.summary.pricewt * (1 -  newDiscount));\n" .
								"}\n" .
								"return true;"
						],
				]],
		]]);
	}
	protected function priceWt(){return ViewUtils::tukosCurrencyBox($this, 'Totalwt', ['atts' => [
				'edit' => ['onChangeLocalAction' => [
						'pricewt'  => ['localActionStatus' =>
								"var form = sWidget.form, itemsW = form.getWidget('items'), newVal = isNaN(newValue) ? '' : newValue;\n" .
								"if (itemsW && itemsW.summary){\n" .
								"var newDiscount = 1 - newVal / itemsW.summary.pricewt;\n" .
								"form.setValueOf('discountpc', newVal == '' ? '' : newDiscount);\n" .
								"form.setValueOf('discountwt' , itemsW.summary.pricewt  -  newVal);\n" .
								"form.setValueOf('pricewot', itemsW.summary.pricewot * (1 -  newDiscount));\n" .
								"}\n" .
								"return true;"
						],
				]],
		]]);
	}
}
?>