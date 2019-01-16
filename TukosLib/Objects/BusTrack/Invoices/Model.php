<?php
namespace TukosLib\Objects\BusTrack\Invoices;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    public $statusOptions = ['draft', 'waiting', 'paid', 'dispute', 'litigation', 'abandonned'];
    public $itemsLabels = ['rowId' => 'rowId', 'catalogid' => 'CatalogId', 'name' => 'Service', 'comments' => 'Details', 'quantity' => 'Quantity', 'unitpricewot' => 'Unitpricewot',  'unitpricewt' => 'Unitpricewt',
    		'discount' => 'Discount', 'pricewot' => 'Pricewot', 'vatrate' => 'VATRate', 'pricewt' => 'Pricewt'
    ];

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'reference' => 'VARCHAR(50)  DEFAULT NULL',
            'relatedquote' => 'INT(11) NULL DEFAULT NULL',
            'invoicedate' => 'date NULL DEFAULT NULL',
        	'items'  => 'longtext ',
        	'discountpc' => "DECIMAL (5, 4)",
        	'discountwt' => "DECIMAL (5, 2)",
        	'pricewot'   => "DECIMAL (5, 2)",
            'pricewt'   => "DECIMAL (5, 2)",
            'todeduce' => "DECIMAL (5, 2)",
            'status' =>  'VARCHAR(50)  DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'bustrackinvoices', ['parentid' => ['bustrackcustomers'], 'relatedquote' => ['bustrackquotes']], ['items'], $colsDefinition, '', ['status'], ['worksheet', 'custom', 'history'], ['name', 'parentid', 'reference']);
    }    

    function initialize($init=[]){
        return parent::initialize(array_merge(['reference' => 'ABCAAAAMMJJXX', 'invoicedate' => date('Y-m-d'), 'quantity' => 1, 'vatrate' => 0.085], $init));
    }
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
    	$paneMode = isset($this->paneMode) ? $this->paneMode : 'Tab';
    	$refPrefix = $this->user->getCustomView($this->objectName, 'edit', $paneMode, ['widgetsDescription', 'export', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'referenceprefix', 'atts', 'value']);
    	if (empty($refPrefix)){
    		$refPrefix = '';
    	}
    	return parent::insert($values, $init, $jsonFilter, ['dateCol' => 'invoicedate', 'referenceCol' => 'reference', 'prefix' => $refPrefix]);
    }

    public function invoiceTable($query, $valuesAndAtts = []){
        $dateFormat = $this->user->dateFormat();
        $atts = $valuesAndAtts['atts'];
        $invoice = $valuesAndAtts['values'];

        $oldInvoice = $this->getOne(['where' => $this->user->filter(['id' => $query['id']], $this->objectName),'cols' => ['items']], ['items' => []]);
		
        if (isset($oldInvoice['items'])){
        	if (!empty($invoice['items'])){
        		$invoice['items'] = Utl::toAssociative($invoice['items'], 'id');
        		$invoice['items'] = Utl::array_merge_recursive_replace($oldInvoice['items'], $invoice['items']);
        	}else{
        		$invoice['items'] = $oldInvoice['items'];
        	}
        }
        //$optionalCols = ['rowId' => 'string',  'catalogid' => 'string', 'comments' => 'string'];
        $colsFormatType = ['rowId' => 'string',  'catalogid' => 'string', 'name' => 'string', 'comments' => 'string', 'quantity' => 'string', 'unitpricewot' => 'currency', 'unitpricewt' => 'currency',
        	'discount' => 'percent', 'pricewot' => 'currency', 'vatrate' => 'percent', 'pricewt' => 'currency'];
        $optionalCols = ['rowId', 'catalogid', 'comments'];
        $absentOptionalCols = array_filter($optionalCols, function($col) use ($atts){
            return $atts[$col] !== 'on';
        });
        $selectedColsFormatType = array_diff_key($colsFormatType, array_flip($absentOptionalCols));
        $hasDiscountCol = false;
        foreach($invoice['items'] as $item){
        	if (isset($item['discount']) && $item['discount'] > 0){
        		$hasDiscountCol = true;
        		break;
        	}
        }
        if (!$hasDiscountCol){
        	unset($selectedColsFormatType['discount']);
        }
        $numberOfCols = count($selectedColsFormatType);

        $thAtts = 'style="border: 1px solid;border-collapse: collapse;" ';
        
        $thNameAtts = sprintf('style="border: 1px solid;border-collapse: collapse;width: %1$s" ', in_array('comments', $absentOptionalCols) ? '40%' : '20%');
        $tdAtts = 'style="border: 1px solid;border-collapse: collapse;" ';
        $tdAttsLeft = 'style="border: 1px solid;border-collapse: collapse;text-align: left;padding-left: 10px;" ';
        $tdNumberAtts = 'style="border: 1px solid;border-collapse: collapse;text-align: right;padding-right: 10px;" ';
        $getTdAtts = function ($formatType) use ($tdAtts, $tdNumberAtts){
        		return $formatType === 'string' ? $tdAtts : $tdNumberAtts;
        };
        $rowContent = [];
        array_walk($selectedColsFormatType, function($formatType, $col) use (&$rowContent, $thNameAtts, $thAtts){
            $rowContent[] = ['tag' => 'th', 'atts' => ($col === 'name' || $col === 'comments') ? $thNameAtts : $thAtts, 'content' => $this->tr($this->itemsLabels[$col])];
        });
        $rows = [['tag' => 'tr', 'content' => $rowContent]];
        foreach ($invoice['items'] as $item){
            $rowContent = [];
            array_walk($selectedColsFormatType, function($formatType, $col) use (&$rowContent, $getTdAtts, $item){
                $rowContent[] = ['tag' => 'td', 'atts' => $getTdAtts($formatType), 'content' => isset($item[$col]) ? Utl::format($item[$col], $formatType, $this->tr) : ''];
            });
            $rows[] = ['tag' => 'tr', 'content' => $rowContent];
        }
        $rows[] = ['tag' => 'tr', 'content' => [['tag' => 'td', 'atts' => 'colspan="' . $numberOfCols . '" style="border: 0px;"',  'content' => '&nbsp; ']]];
       	$dueDateContent = $atts['daysduedate'] > 0 ? $this->tr('invoiceduedate') . ': ' . date($dateFormat, time() + 24 * 60 * 60 * $atts['daysduedate']) : '' ;
        $toDeduceContent = $invoice['todeduce'] > 0 ? '<b>' . $this->tr('Todeduce') . '</b>: ' . Utl::format($invoice['todeduce'], 'currency') : '';
       	if ($invoice['discountwt'] > 0){
			$rows[] = ['tag' => 'tr', 'content' => [
				['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ' , 'content' => ''], 
				['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $this->tr('Globaldiscountwt')],
				['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format(-$invoice['discountwt'], 'currency')]]
			];
       	}
       	$rows[] = ['tag' => 'tr', 'content' => [
	        ['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ', 'content' => $dueDateContent],
			['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $this->tr('Totalwot')],
	    	['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($invoice['pricewot'], 'currency')]]
	    ];
       	$rows[] = ['tag' => 'tr', 'content' => [
            ['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ', 'content' => ""],
       		['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $this->tr('tax')], 
        	['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($invoice['pricewt'] - $invoice['pricewot'], 'currency')]]
        ];
        $rows[] = ['tag' => 'tr', 'content' => [
            ['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ', 'content' => ''],
        	['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => '<b>' . $this->tr('Totalwt') . '</b>'],
        	['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($invoice['pricewt'], 'currency')]]
        ];
        if ($invoice['todeduce'] > 0){
        	$rows[] = ['tag' => 'tr', 'content' => [
        			['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ' , 'content' => ''],
        			['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $this->tr('Todeduce')],
        			['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format(-$invoice['todeduce'], 'currency')]]
        	];
        	$rows[] = ['tag' => 'tr', 'content' => [
        			['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ' , 'content' => ''],
        			['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => '<b>' . $this->tr('Remainingbalance') . '</b>'],
        			['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($invoice['pricewt'] - $invoice['todeduce'], 'currency')]]
        	];
        }
        
        $atts['invoicetable'] = HUtl::buildHtml(['tag' => 'table', 'atts' => 'style="text-align:center; border: solid; border-collapse: collapse;width:100%;"', 'content' => $rows]);
        
        return ['data' => ['value' => $atts]];
    } 
    public function getQuoteChanged($atts){
        $quoteModel = Tfk::$registry->get('objectsStore')->objectModel('bustrackquotes');
        $invoice = $quoteModel->getOne(['where' => ['id' => $atts['where']['relatedquote']], 'cols' => ['parentid', 'name', 'items', 'discountpc', 'discountwt', 'pricewot', 'pricewt', 'downpay']], ['items' => []]);
        $invoice['todeduce'] = Utl::extractItem('downpay', $invoice);
        return $invoice;
	}
}
?>
