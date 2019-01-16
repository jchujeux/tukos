<?php
namespace TukosLib\Objects\BusTrack\Quotes;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;

class Model extends AbstractModel {
    public $statusOptions = ['draft', 'waiting', 'rejected', 'abandonned', 'accepted', 'indelivery', 'delivered'];
    public $itemsLabels = ['rowId' => 'rowId', 'catalogid' => 'CatalogId', 'name' => 'Service', 'comments' => 'Details', 'quantity' => 'Quantity', 'unitpricewot' => 'Unitpricewot',  'unitpricewt' => 'Unitpricewt', 
    		'discount' => 'Discount', 'pricewot' => 'Pricewot', 'vatrate' => 'VATRate', 'pricewt' => 'Pricewt'
    ];

    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'reference' => 'VARCHAR(50)  DEFAULT NULL',
            'quotedate' => 'date NULL DEFAULT NULL',
            'items'  => 'longtext ',
        	'discountpc' => "DECIMAL (5, 4)",
        	'discountwt' => "DECIMAL (5, 2)",
        	'pricewot'   => "DECIMAL (5, 2)",
            'pricewt'   => "DECIMAL (5, 2)",
            'downpay' => "DECIMAL (5, 2)",
            'status' =>  'VARCHAR(50)  DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, 'bustrackquotes', ['parentid' => ['bustrackcustomers']], ['items'], $colsDefinition, '', ['status'], ['worksheet', 'custom', 'history'], ['name', 'parentid', 'reference']);
    }    

    function initialize($init=[]){
        return parent::initialize(array_merge(['reference' => 'ABCAAAAMMJJXX', 'quotedate' => date('Y-m-d')], $init));
    }
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
    	$paneMode = isset($this->paneMode) ? $this->paneMode : 'Tab';
    	$refPrefix = $this->user->getCustomView($this->objectName, 'edit', $paneMode, ['widgetsDescription', 'export', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'referenceprefix', 'atts', 'value']);
    	if (empty($refPrefix)){
    		$refPrefix = '';
    	}
    	return parent::insert($values, $init, $jsonFilter, ['dateCol' => 'quotedate', 'referenceCol' => 'reference', 'prefix' => $refPrefix]);
    }

    public function quoteTable($query, $valuesAndAtts = []){
        $dateFormat = $this->user->dateFormat();
        $atts = $valuesAndAtts['atts'];
        $quote = $valuesAndAtts['values'];

        $oldQuote = $this->getOne(['where' => $this->user->filter(['id' => $query['id']], $this->objectName),'cols' => ['items']], ['items' => []]);
		
        if (isset($oldQuote['items'])){
        	if (!empty($quote['items'])){
        		$quote['items'] = Utl::toAssociative($quote['items'], 'id');
        		$quote['items'] = Utl::array_merge_recursive_replace($oldQuote['items'], $quote['items']);
        	}else{
        		$quote['items'] = $oldQuote['items'];
        	}
        }
        //$optionalCols = ['rowId' => 'string',  'catalogid' => 'string', 'comments' => 'string'];
        $colsFormatType = ['rowId' => 'string',  'catalogid' => 'string', 'name' => 'string', 'comments' => 'string', 'quantity' => 'string', 'unitpricewot' => 'currency', 'unitpricewt' => 'currency',
        	'discount' => 'currency', 'pricewot' => 'currency', 'vatrate' => 'percent', 'pricewt' => 'currency'];
        $optionalCols = ['rowId', 'catalogid', 'comments'];
        $absentOptionalCols = array_filter($optionalCols, function($col) use ($atts){
            return $atts[$col] !== 'on';
        });
        $selectedColsFormatType = array_diff_key($colsFormatType, array_flip($absentOptionalCols));
        $hasDiscountCol = false;
        foreach($quote['items'] as $item){
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
        foreach ($quote['items'] as $item){
            $rowContent = [];
            array_walk($selectedColsFormatType, function($formatType, $col) use (&$rowContent, $getTdAtts, $item){
                $rowContent[] = ['tag' => 'td', 'atts' => $getTdAtts($formatType), 'content' => isset($item[$col]) ? Utl::format($item[$col], $formatType, $this->tr) : ''];
            });
            $rows[] = ['tag' => 'tr', 'content' => $rowContent];
        }
        $rows[] = ['tag' => 'tr', 'content' => [['tag' => 'td', 'atts' => 'colspan="' . $numberOfCols . '" style="border: 0px;"',  'content' => '&nbsp; ']]];
       	$daysValidContent = $atts['daysvalid'] > 0 ? $this->tr('quotevalidfor') . ' ' . $atts['daysvalid'] . ' ' . $this->tr('daysuntil') . ' ' . date($dateFormat, time() + 24 * 60 * 60 * $atts['daysvalid']) : '' ;
       	$downPayContent = $quote['downpay'] > 0 ? '<b>' . $this->tr('Downpaywt') . '</b>: ' . Utl::format($quote['downpay'], 'currency') : '';
       	if ($quote['discountwt'] > 0){
			$rows[] = ['tag' => 'tr', 'content' => [
				['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ' , 'content' => ''], 
				['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => '<b>' . $this->tr('Globaldiscountwt') . '</b>'],
				['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($quote['discountwt'], 'currency')]]
			];
       	}
       	$rows[] = ['tag' => 'tr', 'content' => [
	        ['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ', 'content' => $daysValidContent],
			['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => '<b>' . $this->tr('Totalwot') . '</b>'],
	    	['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($quote['pricewot'], 'currency')]]
	    ];
       	$rows[] = ['tag' => 'tr', 'content' => [
            ['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ', 'content' => ""],
       		['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => '<b>' . $this->tr('Tax') . '</b>'], 
        	['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($quote['pricewt'] - $quote['pricewot'], 'currency')]]
        ];
        $rows[] = ['tag' => 'tr', 'content' => [
            ['tag' => 'td', 'atts' => 'colspan="' . ($numberOfCols - 3) . '" ', 'content' => $downPayContent],
        	['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => '<b>' . $this->tr('Totalwt') . '</b>'],
        	['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($quote['pricewt'], 'currency')]]
        ];
        
        $atts['quotetable'] = HUtl::buildHtml(['tag' => 'table', 'atts' => 'style="text-align:center; border: solid; border-collapse: collapse;width:100%;"', 'content' => $rows]);
        
        return ['data' => ['value' => $atts]];
    } 
}
?>
