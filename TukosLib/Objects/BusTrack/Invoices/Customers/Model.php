<?php
namespace TukosLib\Objects\BusTrack\Invoices\Customers;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\BusTrack\BusTrack;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    public $statusOptions = ['draft', 'waiting', 'paid', 'dispute', 'litigation', 'abandonned'];
    public $itemsLabels = ['catalogid' => 'CatalogId', 'name' => 'Service', 'comments' => 'Details', 'quantity' => 'Quantity', 'unitpricewot' => 'Unitpricewot',  'unitpricewt' => 'Unitpricewt',
    		'discount' => 'Discount', 'pricewot' => 'Pricewot', 'vatrate' => 'VATRate', 'pricewt' => 'Pricewt'
    ];
    public $customersOrSuppliers = 'customers';
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'organization' => 'MEDIUMINT NULL DEFAULT NULL',
            'contact' => 'MEDIUMINT NULL DEFAULT NULL',
            'reference' => 'VARCHAR(50)  DEFAULT NULL',
            'relatedquote' => 'INT(11) NULL DEFAULT NULL',
            'invoicedate' => 'date NULL DEFAULT NULL',
        	'items'  => 'longtext',
        	'discountpc' => "DECIMAL (5, 4)",
        	'discountwt' => "DECIMAL (10, 2)",
        	'pricewot'   => "DECIMAL (10, 2)",
            'pricewt'   => "DECIMAL (10, 2)",
            'lefttopay' => "DECIMAL (10, 2)",
            'status' =>  'VARCHAR(50)  DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, "bustrackinvoices{$this->customersOrSuppliers}", ['parentid' => ['bustrackpeople', 'bustrackorganizations'], 'organization' => ['bustrackorganizations'], 'contact' => ['bustrackpeople'],
            'relatedquote' => ['bustrackquotes']], ['items'], $colsDefinition, [], ['status'], ['custom', 'history'], ['name', 'parentid', 'reference']);
        $this->gridsIdCols =  array_merge($this->gridsIdCols, ['items' => ['catalogid']]);
        $this->setDeleteChildren(["bustrackinvoices{$this->customersOrSuppliers}items"]);
        $this->paymentTypeOptions = BusTrack::paymentTypeOptions($this->customersOrSuppliers);
    }    

    function initialize($init=[]){
        return parent::initialize(array_merge(['reference' => 'ABCAAAAMMJJXX', 'invoicedate' => date('Y-m-d')], $init));
    }
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
        $organization = Utl::getItem('organization', $values);
    	$refPrefix = empty($organization) ? 'XXX' : Tfk::$registry->get('objectsStore')->objectModel('organizations')->getOne(['where' => ['id' => $organization], 'cols' => ['trigram']])['trigram'];
    	return parent::insert($values, $init, $jsonFilter, ['dateCol' => 'invoicedate', 'referenceCol' => 'reference', 'prefix' => $refPrefix]);
    }

    public function invoiceTable($query, $valuesAndAtts = []){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $organizationsModel = $objectsStore->objectModel('organizations');
        $atts = $valuesAndAtts['atts'];
        $invoice = $valuesAndAtts['values'];
        if ($invoice['organization'] && (($vatMode = Utl::getItem('vatmode', $organizationsModel->getOne(['where' => ['id' => $invoice['organization']], 'cols' => ['vatmode']]))) === 'exemption')){
            $colsFormatType = ['catalogid' => 'string', 'name' => 'string', 'comments' => 'string', 'quantity' => 'string', 'unitpricewt' => 'currency',
                'discount' => 'percent', 'pricewt' => 'currency'];
            $this->itemsLabels['pricewt'] = 'Priceexempted';
            $this->itemsLabels['unitpricewt'] = 'Unitprice';
        }else{
            $colsFormatType = ['catalogid' => 'string', 'name' => 'string', 'comments' => 'string', 'quantity' => 'string', 'unitpricewot' => 'currency', 'unitpricewt' => 'currency',
                'discount' => 'percent', 'pricewot' => 'currency', 'vatrate' => 'percent', 'pricewt' => 'currency'];
        }
        $optionalCols = ['catalogid', 'comments'];
        $absentOptionalCols = array_filter($optionalCols, function($col) use ($atts){
            return $atts[$col] !== 'on';
        });
        $selectedColsFormatType = array_diff_key($colsFormatType, array_flip($absentOptionalCols));
        $colsToRetrieve = array_merge(array_keys($selectedColsFormatType), ['id']);
        $invoiceItemsModel = $objectsStore->objectModel("bustrackinvoices{$this->customersOrSuppliers}items");
        $items = $invoiceItemsModel->getAll(['where' => ['parentid' => $invoice['id']], 'cols' => $colsToRetrieve]);
        $hasDiscountCol = false;
        $hasCommentsCol = false;
        foreach($items as $item){
        	if (isset($item['discount']) && $item['discount'] > 0){
        		$hasDiscountCol = true;
        		break;
        	}
        	if (($comments = Utl::getItem('comments', $item)) && $comments !== '<br />'){
        	    $hasCommentsCol = true;
        	}
        }
        if (!$hasDiscountCol){
        	unset($selectedColsFormatType['discount']);
        }
        if (!$hasCommentsCol){
            unset($selectedColsFormatType['comments']);
        }
        $numberOfCols = count($selectedColsFormatType);

        $thAtts = 'style="border: 1px solid;border-collapse: collapse;padding: 2px;min-width:80px;" ';
        
        $nameAndCommentsWidth = $vatMode === 'exemption' ? 80 : 40;
        $thNameAtts = sprintf('style="border: 1px solid;border-collapse: collapse;padding: 2px;width: %1$s" ', in_array('comments', $absentOptionalCols) ? $nameAndCommentsWidth . '%' : ($nameAndCommentsWidth / 2) .  '%');
        $tdAtts = 'style="border: 1px solid;border-collapse: collapse;padding: 2px;" ';
        $tdAttsLeft = 'style="border: 1px solid;border-collapse: collapse;padding: 2px;text-align: left;padding-left: 10px;" ';
        $tdNumberAtts = 'style="border: 1px solid;border-collapse: collapse;padding: 2px;text-align: right;padding-right: 10px;" ';
        $getTdAtts = function ($formatType) use ($tdAtts, $tdNumberAtts){
        		return $formatType === 'string' ? $tdAtts : $tdNumberAtts;
        };
        $rowContent = [];
        array_walk($selectedColsFormatType, function($formatType, $col) use (&$rowContent, $thNameAtts, $thAtts){
            $rowContent[] = ['tag' => 'th', 'atts' => ($col === 'name' || $col === 'comments') ? $thNameAtts : $thAtts, 'content' => $this->tr($this->itemsLabels[$col])];
        });
        $rows = [['tag' => 'tr', 'content' => $rowContent]];
        foreach ($items as $item){
            $rowContent = [];
            array_walk($selectedColsFormatType, function($formatType, $col) use (&$rowContent, $getTdAtts, $item){
                $rowContent[] = ['tag' => 'td', 'atts' => $getTdAtts($formatType), 'content' => isset($item[$col]) ? Utl::format($item[$col], $formatType, $this->tr) : ''];
            });
            $rows[] = ['tag' => 'tr', 'content' => $rowContent];
        }
        $numberOfRows = 4 + ($invoice['discountwt'] > 0 ? 1 : 0);
        $rows[] = ['tag' => 'tr', 'content' => [['tag' => 'td', 'atts' => 'colspan="' . $numberOfCols . '" style="border: 0px;"',  'content' => '&nbsp; ']]];
        $numberOfCols3 = $numberOfCols - 3;
        $rows[] = ['tag' => 'tr', 'content' => [
            ['tag' => 'td', 'atts' => "colspan=\"$numberOfCols3\" rowspan=\"$numberOfRows\" style=\"border: 0px;text-align: left;line-height: 80%\"",  'content' => "<small>{$this->tr('Paymentconditions')}</small>"],
            ['tag' => 'td', 'atts' => "colspan=\"3\" style=\"border: 0px;\"",  'content' => '&nbsp; '],
        ]];
        if ($invoice['discountwt'] > 0){
			$rows[] = ['tag' => 'tr', 'content' => [
				['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $this->tr('Globaldiscountwt')],
				['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format(-$invoice['discountwt'], 'currency')]]
			];
       	}
       	if ($vatMode !== 'exemption'){
       	    $rows[] = ['tag' => 'tr', 'content' => [
       	        ['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $this->tr('Totalwot')],
       	        ['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($invoice['pricewot'], 'currency')]]
       	    ];
       	    $rows[] = ['tag' => 'tr', 'content' => [
       	        ['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $this->tr('tax')],
       	        ['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format((float)$invoice['pricewt'] - (float)$invoice['pricewot'], 'currency')]]
       	    ];
       	}
        $rows[] = ['tag' => 'tr', 'content' => [
        	['tag' => 'td', 'atts' => 'colspan="2"' . $tdAttsLeft, 'content' => $vatMode === 'exemption' ? ('<b>' . $this->tr('Totalexemption') . '</b><br><small>' . $this->tr('Exemptionmessage') . '</small>') : ('<b>' . $this->tr('Totalwt') . '</b>')],
        	['tag' => 'td', 'atts' => $tdNumberAtts, 'content' => Utl::format($invoice['pricewt'], 'currency')]]
        ];
        $atts['invoicetable'] = HUtl::buildHtml(['tag' => 'table', 'atts' => 'style="text-align:center; border: solid; border-collapse: collapse;width:100%;"', 'content' => $rows]);
        
        return ['data' => ['value' => $atts]];
    } 
    public function getQuoteChanged($atts){
        $quoteModel = Tfk::$registry->get('objectsStore')->objectModel('bustrackquotes');
        $invoice = $quoteModel->getOne(['where' => ['id' => $atts['where']['relatedquote']], 'cols' => ['parentid', 'name', 'items', 'discountpc', 'discountwt', 'pricewot', 'pricewt', 'downpay']], ['items' => []]);
        $invoice['todeduce'] = Utl::extractItem('downpay', $invoice);
        return $invoice;
	}
	public function updateForPaymentsItems($invoicesIds){
	    $inValue = implode(',', $invoicesIds);
	    $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackinvoices{$this->customersOrSuppliers}`.`id`, IFNULL(`bustrackinvoices{$this->customersOrSuppliers}`.`pricewt` - sum(`bustrackpayments{$this->customersOrSuppliers}items`.`amount`), `pricewt`) as `lefttopay`
            FROM `bustrackinvoices{$this->customersOrSuppliers}`
                INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`
                LEFT JOIN(`bustrackpayments{$this->customersOrSuppliers}items`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpayments{$this->customersOrSuppliers}items`.`id` AND `bustrackpayments{$this->customersOrSuppliers}items`.`invoiceid` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`
            WHERE (`bustrackinvoices{$this->customersOrSuppliers}`.`id` IN ($inValue))
            GROUP BY `bustrackinvoices{$this->customersOrSuppliers}`.`id`
EOT
	        );
	    $results = $results->fetchAll(\PDO::FETCH_ASSOC);
	    foreach($results as $result){
	        $this->updateOne($result);
	    }
	}
    public function updateForInvoicesItems($invoicesIds){
        $inValue = implode(',', $invoicesIds);
        $results = SUtl::$tukosModel->store->query(<<<EOT
SELECT `bustrackinvoices{$this->customersOrSuppliers}`.`id`, IFNULL(sum(`bustrackinvoices{$this->customersOrSuppliers}items`.`pricewot`), 0) as `pricewot`, IFNULL(sum(`bustrackinvoices{$this->customersOrSuppliers}items`.`pricewt`), 0) as `pricewt`, 
        IFNULL(IFNULL(sum(`bustrackinvoices{$this->customersOrSuppliers}items`.`pricewt`), 0) - sum(`bustrackpayments{$this->customersOrSuppliers}items`.`amount`), @pricewt) as `lefttopay`
   FROM `bustrackinvoices{$this->customersOrSuppliers}`
      INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`
      LEFT JOIN(`bustrackinvoices{$this->customersOrSuppliers}items`, `tukos` as `t1`) ON `t1`.`id` = `bustrackinvoices{$this->customersOrSuppliers}items`.`id` AND `t1`.`parentid` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`
      LEFT JOIN(`bustrackpayments{$this->customersOrSuppliers}items`, `tukos` as `t2`) ON `t2`.`id` = `bustrackpayments{$this->customersOrSuppliers}items`.`id` AND `bustrackpayments{$this->customersOrSuppliers}items`.`invoiceid` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`
WHERE (`bustrackinvoices{$this->customersOrSuppliers}`.`id` IN ($inValue))
EOT
            );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        foreach($results as $result){
            $this->updateOne($result);
        }
    }
}
?>