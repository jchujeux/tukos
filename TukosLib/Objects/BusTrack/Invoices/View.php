<?php
namespace TukosLib\Objects\BusTrack\Invoices;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\BusTrack\QuotesAndInvoices;
use TukosLib\Objects\ViewUtils;


class View extends AbstractView {

    use QuotesAndInvoices;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Customer', 'Description');
		$tr = $this->tr;
        $labels = $this->model->itemsLabels;
        //$this->sendOnSave = array_merge($this->sendOnSave, ['organization']);
		$customDataWidgets = [
		    'comments' => ['atts' => ['edit' => ['height' => '150px']]],
		    'organization' => ViewUtils::objectSelect($this, 'Invoicingorganization', 'organizations'),
		    'reference' =>  ViewUtils::textBox($this, 'Reference', ['atts' => ['edit' => ['disabled' => true]]]),
            'relatedquote' => ViewUtils::objectSelect($this, 'Relatedquote', 'bustrackquotes', [
            	'atts' => ['edit' => ['onChangeServerAction' => [
            		'inputWidgets' => ['relatedquote'],
            		'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getQuoteChanged'])]],
            ]]]]),
        	'invoicedate' => ViewUtils::tukosDateBox($this, 'Invoicedate'),
            //'items'  => $this->items($labels),
			'discountpc' => $this->discountPc($labels),
        	'discountwt' => $this->discountWt(),
			'pricewot' => $this->priceWot(),
			'pricewt' => $this->priceWt(true),
		    'todeduce' => ViewUtils::tukosCurrencyBox($this, 'Todeduce', ['atts' => ['edit' => ['disabled' => true]]]),
		    'lefttopay' => ViewUtils::tukosCurrencyBox($this, 'LeftToPay', ['atts' => ['edit' => ['disabled' => true]]]),
            'status'   => ViewUtils::storeSelect('status', $this, 'Status'),
        ];

        $subObjects = [
            'catalog' => [
                'object' => 'bustrackcatalog', 'filters' => [],
                'atts' => ['title' => $tr('Bustrackcatalog'), 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false]],
                'allDescendants' => true],
            'items' => [
                'object' => 'bustrackinvoicesitems', 'filters' => ['parentid' => '@id'],
                'atts' => ['title' => $tr('bustrackinvoicesitems'), 'dndParams' => [ 'copyOnly' => true, 'selfAccept' => false], 'newRowPrefix' => 'new',
                    'colsDescription' => ['parentid' => ['atts' => ['editorArgs' => ['storeArgs' => ['storeDgrid' => 'payments']]]]],
                    'summaryRow' => ['cols' => [
                        'name' => ['content' =>  ['Total']],
                        'pricewot' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#pricewot#);"]]],
                        'pricewt' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#pricewt#);"]]]
                    ]],
                    'onDropMap' => [
                        'catalog' => ['fields' => [
                            'catalogid' => 'id', 'name' => 'name', 'comments' => 'comments', 'unitpricewot' => 'unitpricewot', 'vatrate' => 'vatrate', 'unitpricewt' => 'unitpricewt', 'category' => 'category', 'vatfree' => 'vatfree']]
                    ],
                    'onWatchLocalAction' => ['summary' => ['items' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' =>
                        "var discountWt = sWidget.form.valueOf('discountwt'), priceWt = sWidget.summary.pricewt - discountWt, discountPc = discountWt / sWidget.summary.pricewt, paymentsItems = sWidget.form.getWidget('paymentsitems');\n" .
                        "sWidget.form.setValueOf('pricewt', priceWt);\n" .
                        "sWidget.form.setValueOf('pricewot', sWidget.summary.pricewot * (1 - discountPc));\n" .
                        "sWidget.form.setValueOf('discountpc', discountWt === '' ? '' : discountPc);\n" .
                        "if (paymentsItems.summary){sWidget.form.setValueOf('lefttopay', priceWt - paymentsItems.summary.amount);}\n" .
                        "return true;"
                    ]]]],
                ],
                'allDescendants' => true,
                'sendOnHidden' => ['catalogid', 'quantity', 'vatrate', 'discount', 'pricewot', 'pricewt', 'category', 'vatfree', 'unitpricewot', 'unitpricewt']
            ],
            'payments' => [
                'object' => 'bustrackpayments',
                'atts'  => ['title' => $tr('bustrackpayments'), 'newRowPrefix' => 'new'],
                'filters' => [['tukosJoin' =>
                    ['inner', '(`tukos`  as `t0`, `bustrackpaymentsitems`)', '`t0`.`parentid` = `bustrackpayments`.`id` AND `bustrackpaymentsitems`.`invoiceid` = @id AND `t0`.`id` = `bustrackpaymentsitems`.`id`']]],
                'allDescendants' => true,
            ],
            'paymentsitems' => [
                'object' => 'bustrackpaymentsitems', 'filters' => ['invoiceid' => '@id'],
                'atts' => ['title' => $this->tr('bustrackpaymentsitems'),
                    'colsDescription' => [
                        'parentid' => ['atts' => ['storeedit' => ['editorArgs' => ['storeArgs' => ['storeDgrid' => 'payments']]]]], 'invoiceitemid' => ['atts' => ['storeedit' => ['editorArgs' => ['storeArgs' => ['storeDgrid' => 'items']]]]]],
                    'summaryRow' => ['cols' => [
                        'name' => ['content' =>  ['Total']],
                        'amount' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#amount#);"]]]
                    ]],
                    'onWatchLocalAction' => ['summary' => ['paymentsitems' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' =>
                        "sWidget.form.setValueOf('todeduce', sWidget.summary.amount);\n" .
                        "sWidget.form.setValueOf('lefttopay', sWidget.form.valueOf('pricewt') - sWidget.summary.amount);\n" .
                        "return true;"
                    ]]]],
                ],
                'allDescendants' => true,
            ],
        ];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
