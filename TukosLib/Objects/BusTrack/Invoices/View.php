<?php
namespace TukosLib\Objects\BusTrack\Invoices;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\BusTrack\QuotesAndInvoices;
use TukosLib\Objects\BusTrack\ViewActionStrings as VAS;
use TukosLib\Objects\ViewUtils;


class View extends AbstractView {

    use QuotesAndInvoices;
    
	function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Customer', 'Description');
        $tr = $this->tr;
        $labels = $this->model->itemsLabels;
        //$this->sendOnSave = array_merge($this->sendOnSave, ['organization']);
		$this->allowedNestedWatchActions = 0;
		$customDataWidgets = [
		    'comments' => ['atts' => ['edit' => ['height' => '150px']]],
		    'organization' => ViewUtils::objectSelect($this, 'Invoicingorganization', 'organizations'),
		    'contact' => ViewUtils::objectSelect($this, 'Invoicingcontact', 'people'),
		    'reference' =>  ViewUtils::textBox($this, 'Reference', ['atts' => ['edit' => ['disabled' => true]]]),
            'relatedquote' => ViewUtils::objectSelect($this, 'Relatedquote', 'bustrackquotes', [
            	'atts' => ['edit' => [
            	    //'onChangeServerAction' => ['inputWidgets' => ['relatedquote'], 'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getQuoteChanged'])]]],
            	    'onChangeLocalAction' => ['items' => ['localActionStatus' => Vas::relatedQuoteAction()]]
            ]]]),
        	'invoicedate' => ViewUtils::tukosDateBox($this, 'Invoicedate'),
			'discountpc' => $this->discountPc($labels),
        	'discountwt' => $this->discountWt(),
			'pricewot' => $this->priceWot(),
			'pricewt' => $this->priceWt(true),
		    'todeduce' => ViewUtils::tukosCurrencyBox($this, 'Todeduce', ['atts' => [
		        'edit' => ['disabled' => true],
		        'overview' => ['hidden' => true]
		    ]]),
		    'lefttopay' => ViewUtils::tukosCurrencyBox($this, 'LeftToPay', ['atts' => [
		        'edit' => ['disabled' => true],
		        'overview' => ['formatType' => 'currency', 'width' => 80]
		    ]]),
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
                    'colsDescription' => ['parentid' => ['atts' => ['editorArgs' => ['storeArgs' => ['storeDgrid' => 'payments']]]], 'catalogid' => ['atts' => ['editorArgs' => ['storeArgs' => ['storeDgrid' => 'catalog']]]]],
                    'summaryRow' => ['cols' => [
                        'name' => ['content' =>  ['Total']],
                        'pricewot' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#pricewot#);"]]],
                        'pricewt' => ['atts' => ['formatType' => 'currency'], 'content' => [['rhs' => "return Number(#pricewt#);"]]]
                    ]],
                    'onDropMap' => [
                        'catalog' => ['fields' => [
                            'catalogid' => 'id', 'name' => 'name', 'comments' => 'comments', 'unitpricewot' => 'unitpricewot', 'vatrate' => 'vatrate', 'unitpricewt' => 'unitpricewt', 'category' => 'category', 'vatfree' => 'vatfree']]
                    ],
                    'allowedNestedRowWatchActions' => 0,
                    'onWatchLocalAction' => ['summary' => ['items' => ['localActionStatus' => ['triggers' => ['server' => false, 'user' => true], 'action' => <<<EOT
var discountWt = sWidget.form.valueOf('discountwt'), priceWt = sWidget.summary.pricewt - discountWt, discountPc = discountWt / sWidget.summary.pricewt, paymentsItems = sWidget.form.getWidget('paymentsitems');
sWidget.form.setValueOf('pricewt', priceWt);
sWidget.form.setValueOf('pricewot', sWidget.summary.pricewot * (1 - discountPc));
sWidget.form.setValueOf('discountpc', discountWt === '' ? '' : discountPc);
sWidget.form.setValueOf('lefttopay', priceWt - utils.drillDown(paymentsItems, ['summary', 'amount'], 0));
return true;
EOT
                    ]]]],
                ],
                'allDescendants' => true,
                'sendOnHidden' => ['catalogid', 'quantity', 'vatrate', 'discount', 'pricewot', 'pricewt', 'category', 'vatfree', 'unitpricewot', 'unitpricewt']
            ],
            'payments' => [
                'object' => 'bustrackpayments',
                'atts'  => ['title' => $tr('bustrackpayments'), 'newRowPrefix' => 'new'],
                'filters' => [
                    ['tukosJoin' => ['inner', '(`tukos`  as `t0`, `bustrackpaymentsitems`)', '`t0`.`parentid` = `bustrackpayments`.`id` AND `bustrackpaymentsitems`.`invoiceid` = @id AND `t0`.`id` = `bustrackpaymentsitems`.`id`']],
                    ['groupBy' => ['tukos.id']],
                ],
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
                    'onWatchLocalAction' => ['summary' => ['paymentsitems' => ['localActionStatus' => ['triggers' => ['server' => true, 'user' => true], 'action' =>
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
