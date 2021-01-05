<?php
namespace TukosLib\Objects\BusTrack\Dashboards\Customers;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel { 
    public $customersOrSuppliers = 'customers';
    
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'startdate' => 'date NULL DEFAULT NULL',
            'enddate' => 'date NULL DEFAULT NULL',
            'startdatependinginvoices' => 'date NULL DEFAULT NULL',
            'detailsvatfree' => "DECIMAL (10, 2)",
            'detailswithvatwot' => "DECIMAL (10, 2)",
            'detailsvat' => "DECIMAL (10, 2)",
            'detailswot' => "DECIMAL (10, 2)",
            'detailswt' => "DECIMAL (10, 2)",
            'unasvatfree' => "DECIMAL (10, 2)",
            'unaswithvatwot' => "DECIMAL (10, 2)",
            'unasvat' => "DECIMAL (10, 2)",
            'unaswot' => "DECIMAL (10, 2)",
            'unaswt' => "DECIMAL (10, 2)",
            'expvatfree' => "DECIMAL (10, 2)",
            'expwithvatwot' => "DECIMAL (10, 2)",
            'expvat' => "DECIMAL (10, 2)",
            'expwot' => "DECIMAL (10, 2)",
            'expwt' => "DECIMAL (10, 2)",
            'unexpvatfree' => "DECIMAL (10, 2)",
            'unexpwithvatwot' => "DECIMAL (10, 2)",
            'unexpvat' => "DECIMAL (10, 2)",
            'unexpwot' => "DECIMAL (10, 2)",
            'unexpwt' => "DECIMAL (10, 2)",
            'totalvatfree' => "DECIMAL (10, 2)",
            'totalwithvatwot' => "DECIMAL (10, 2)",
            'totalvat' => "DECIMAL (10, 2)",
            'totalwot' => "DECIMAL (10, 2)",
            'totalwt' => "DECIMAL (10, 2)",
            'totalwotpercategory' => 'longtext',
            'invoicesclosedcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesopenedcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesongoingcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesduewot' => "DECIMAL (10, 2)",
            'pendingamount' => "DECIMAL (10, 2)",
            'paymentslog' => 'longtext',
            'pendinginvoiceslog' => 'longtext',
            'paymentsdetailslog' => 'longtext'
        ];
        parent::__construct($objectName, $translator, "bustrackdashboards{$this->customersOrSuppliers}", ['parentid' => ['organizations']], [], $colsDefinition,  [], [], ['custom']);
        $this->categoriesModel = Tfk::$registry->get('objectsStore')->objectModel('bustrackcategories');
    }
    public function paymentsDetailsKPIs($organization, $startDate, $endDate){
        $tk = SUtl::$tukosTableName;
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackinvoices{$this->customersOrSuppliers}`.`id` as `invoiceid`, `t2`.`name` as `invoicename`, `bustrackinvoices{$this->customersOrSuppliers}`.`reference` as `invoicereference`, `t2`.`parentid` as `customer`,`bustrackinvoices{$this->customersOrSuppliers}`.`invoicedate`, 
                   `bustrackinvoices{$this->customersOrSuppliers}`.`pricewt` as `invoiceamount`, 
                   `bustrackpayments{$this->customersOrSuppliers}`.`id` as `paymentid`, `t0`.`name` as `paymentname`, `bustrackpayments{$this->customersOrSuppliers}`.`date` as `paymentdate`, `$tk`.`id` as `paymentitemid`, 
                   `$tk`.`name` as `paymentitemname`, `bustrackpayments{$this->customersOrSuppliers}items`.`amount` as `paymentitemamount`, IFNULL(`t3`.`id`, 0) as `category`, `bustrackinvoices{$this->customersOrSuppliers}items`.`vatfree`, `bustrackinvoices{$this->customersOrSuppliers}items`.`vatrate`,
                   `bustrackinvoices{$this->customersOrSuppliers}items`.`category`
            FROM `bustrackpayments{$this->customersOrSuppliers}items`
                INNER JOIN `$tk` on `$tk`.`id` = `bustrackpayments{$this->customersOrSuppliers}items`.`id`
                INNER JOIN (`$tk` as `t0` INNER JOIN `bustrackpayments{$this->customersOrSuppliers}`) on (`t0`.`id` = `bustrackpayments{$this->customersOrSuppliers}`.`id` AND `$tk`.`parentid` = `bustrackpayments{$this->customersOrSuppliers}`.`id`)
                INNER JOIN (`$tk` as `t1` INNER JOIN `bustrackinvoices{$this->customersOrSuppliers}items`) on (`t1`.`id` = `bustrackinvoices{$this->customersOrSuppliers}items`.`id` AND `bustrackpayments{$this->customersOrSuppliers}items`.`invoiceitemid` = `bustrackinvoices{$this->customersOrSuppliers}items`.`id`)
                INNER JOIN (`$tk` as `t2` INNER JOIN `bustrackinvoices{$this->customersOrSuppliers}`) on (`t2`.`id` = `bustrackinvoices{$this->customersOrSuppliers}`.`id` AND `bustrackpayments{$this->customersOrSuppliers}items`.`invoiceid` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`)
                LEFT JOIN (`$tk` as `t3` INNER JOIN `bustrackcategories`) on (`t3`.`id` = `bustrackcategories`.`id` AND (`bustrackinvoices{$this->customersOrSuppliers}items`.`category` = `bustrackcategories`.`id` OR `bustrackinvoices{$this->customersOrSuppliers}items`.`category` IS NULL))
            WHERE (`bustrackpayments{$this->customersOrSuppliers}`.`date` >= '$startDate' AND `bustrackpayments{$this->customersOrSuppliers}`.`date` <= '$endDate' AND `bustrackinvoices{$this->customersOrSuppliers}`.`organization` = $organization)
EOT
        );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        $kpis['detailsvatfree'] = 0; $kpis['detailswithvatwot'] = 0; $kpis['detailsvat'] = 0; 
        $kpis['detailswotpercategory'] = [];
        
        foreach ($results as $result){
            if (empty($result['vatfree'])){
                $paidwot = $result['paymentitemamount']/ (1 + $result['vatrate']);
                $kpis['detailswithvatwot'] += $paidwot;
                $kpis['detailsvat'] += $paidwot * $result['vatrate'];
                Utl::increment($kpis['detailswotpercategory'], $result['category'], $paidwot);
            }else{
                $kpis['detailsvatfree'] += $result['paymentitemamount'];
                Utl::increment($kpis['detailswotpercategory'], $result['category'], $result['paymentitemamount']);
            }
        }
        $kpis['paymentsdetailslog'] = $results;
        return $kpis;
    }
    public function pendingInvoicesKPIs($organization, $startDate, $endDate){
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackinvoices{$this->customersOrSuppliers}`.`id`, `t0`.`parentid` as `customer`, `t0`.`name`, `bustrackinvoices{$this->customersOrSuppliers}`.`reference`, `bustrackinvoices{$this->customersOrSuppliers}`.`invoicedate`, `bustrackinvoices{$this->customersOrSuppliers}`.`contact`, `t0`.`comments`, 
                   `bustrackinvoices{$this->customersOrSuppliers}`.`pricewt` as `pricewt`, IFNULL(`pricewt` - sum(`bustrackpayments{$this->customersOrSuppliers}items`.`amount`), `pricewt`) as `lefttopay`
            FROM `bustrackinvoices{$this->customersOrSuppliers}`
                INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`
                LEFT JOIN(`bustrackpayments{$this->customersOrSuppliers}items`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpayments{$this->customersOrSuppliers}items`.`id` AND `bustrackpayments{$this->customersOrSuppliers}items`.`invoiceid` = `bustrackinvoices{$this->customersOrSuppliers}`.`id`
            WHERE (`bustrackinvoices{$this->customersOrSuppliers}`.`invoicedate` >= '$startDate' AND `bustrackinvoices{$this->customersOrSuppliers}`.`invoicedate` <= '$endDate' AND `bustrackinvoices{$this->customersOrSuppliers}`.`organization` = $organization)
            GROUP BY `bustrackinvoices{$this->customersOrSuppliers}`.`id`
            HAVING `lefttopay` <> 0 OR `lefttopay` IS NULL
EOT
            );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC); $pendingAmount = 0;
        foreach ($results as $result){
            $pendingAmount += $result['lefttopay'];
        }
        return ['pendingamount' => $pendingAmount, 'pendinginvoiceslog' => $results];
    }
    public function paymentsKPIs($organization, $startDate, $endDate){
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackpayments{$this->customersOrSuppliers}`.`id`, t0.`parentid` as `customer`, `t0`.`name`, `bustrackpayments{$this->customersOrSuppliers}`.`date`, `bustrackpayments{$this->customersOrSuppliers}`.`paymenttype`,
                   `bustrackpayments{$this->customersOrSuppliers}`.`reference` as `paymentreference`, `bustrackpayments{$this->customersOrSuppliers}`.`slip`,  `bustrackpayments{$this->customersOrSuppliers}`.`isexplained`,
                   `bustrackpayments{$this->customersOrSuppliers}`.`amount`, `bustrackpayments{$this->customersOrSuppliers}`.`amount` - IFNULL(sum(`bustrackpayments{$this->customersOrSuppliers}items`.`amount`), 0)  as `unassignedamount`, `bustrackpayments{$this->customersOrSuppliers}`.`category`
            FROM `bustrackpayments{$this->customersOrSuppliers}`
                INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments{$this->customersOrSuppliers}`.`id`
                LEFT JOIN(`bustrackpayments{$this->customersOrSuppliers}items`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpayments{$this->customersOrSuppliers}items`.`id` AND `t1`.`parentid` = `bustrackpayments{$this->customersOrSuppliers}`.`id`
            WHERE (`bustrackpayments{$this->customersOrSuppliers}`.`date` >= '$startDate' AND `bustrackpayments{$this->customersOrSuppliers}`.`date` <= '$endDate')
            GROUP BY `bustrackpayments{$this->customersOrSuppliers}`.`id`
EOT
            );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        $kpis['unexpvatfree'] = 0; $kpis['unexpwithvatwot'] = 0; $kpis['unexpvat'] = 0;
        $kpis['expvatfree'] = 0; $kpis['expwithvatwot'] = 0; $kpis['expvat'] = 0;
        $kpis['unaswotpercategory'] = [];
        foreach ($results as $result){
            $categoryId = Utl::getItem('category', $result, 0, 0);
            $vatRate = $this->categoriesModel->vatRate($organization, $categoryId);
            //$expOrUnexp = $result['isexplained'] === 'YES' ? 'exp' : 'unexp';
            $expOrUnexp = empty($result['isexplained']) ? 'unexp' : 'exp';
            if ($vatRate){
                $kpis[$expOrUnexp . 'withvatwot'] += $paidwot = $result['unassignedamount'] / (1 + $vatRate);
                $kpis[$expOrUnexp . 'vat'] += $paidwot * $vatRate;
                Utl::increment($kpis['unaswotpercategory'], $categoryId, $paidwot);
            }else{
                $kpis[$expOrUnexp . 'vatfree'] += $result['unassignedamount'];
                Utl::increment($kpis['unaswotpercategory'], $categoryId, $result['unassignedamount']);
            }
        }
        $kpis['paymentslog'] = $results;
        return $kpis;
    }
    function processOne($where){
        $where = $this->user->filter($where, $this->objectName); $newValues = ['paymentslog' => '', 'pendinginvoiceslog' => '', 'paymentsdetailslog' => ''];
        $values = $this->getOne(['where' => $where, 'cols' => ['parentid', 'startdate', 'enddate', 'startdatependinginvoices']]);
        if (!empty($values['parentid']) && !empty($values['startdate'])){
            if (empty($values['enddate'])){
                $values['enddate'] = date('Y-m-d');
            }
            $newValues = array_merge($newValues, $this->paymentsDetailsKPIs($values['parentid'], $values['startdate'], $values['enddate']));
            $newValues = array_merge($newValues, $this->pendingInvoicesKPIs($values['parentid'], empty($startDate = $values['startdatependinginvoices']) ? $values['startdate'] : $startDate, $values['enddate']));
            $newValues = array_merge($newValues, $this->paymentsKPIs($values['parentid'], $values['startdate'], $values['enddate']));
            if (!empty($newValues)){
                $newValues['totalwotpercategory'] = Utl::incrementArray(Utl::extractItem('detailswotpercategory', $newValues, [], []), Utl::extractItem('unaswotpercategory', $newValues, [], []));
                $newValues = Utl::jsonEncodeArray($newValues);
                foreach (['details', 'exp', 'unexp'] as $prefix){
                    $newValues["{$prefix}wot"] = $newValues["{$prefix}vatfree"] + $newValues["{$prefix}withvatwot"];
                    $newValues["{$prefix}wt"] = $newValues["{$prefix}wot"] + $newValues["{$prefix}vat"];
                }
                foreach (['vatfree', 'withvatwot', 'vat', 'wot', 'wt'] as $label){
                    $newValues["total{$label}"] = $newValues["details{$label}"] + $newValues["exp{$label}"] + $newValues["unexp{$label}"];
                }
                $this->updateOne($newValues, ['where' => $where])['id'];
                Feedback::add($this->tr('Dashboard updated'));
            }
        }else{
            Feedback::add($this->tr('Needorgastartend'));
            return [];
        }
        return [];
    }
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $result = parent::getOneExtended($atts);
        $totalWotPerCategory = json_decode($result['totalwotpercategory'], true);
        $organization = $result['parentid'];
        $namedPerCategory = [];
        if (!empty($totalWotPerCategory)){
            foreach($totalWotPerCategory as $id => $item){
                $namedPerCategory[$this->categoriesModel->tName($organization, $id)] = $item;
            }
            $result['totalwotpercategory'] = ['store' => Utl::toStoreData($namedPerCategory, 'category', 'amount')];
        }
        if (!empty($result['paymentsdetailslog'])){
            $result['paymentsdetailslog'] = json_decode($result['paymentsdetailslog'], true);
            foreach ($result['paymentsdetailslog'] as &$item){
                Tfk::addExtra($item['invoiceid'], ['name' => Utl::extractItem('invoicename', $item) . '-' . Utl::extractItem('invoicereference', $item), 'object' => "bustrackinvoices{$this->customersOrSuppliers}"]);
                Tfk::addExtra($item['paymentid'], ['name' => Utl::getItem('paymentname', $item), 'object' => "bustrackpayments{$this->customersOrSuppliers}"]);
                SUtl::addItemIdCols($item, ['customer', 'category']);
            }
        }
        if (!empty($result['pendinginvoiceslog'])){
            $result['pendinginvoiceslog'] = json_decode($result['pendinginvoiceslog'], true);
            foreach ($result['pendinginvoiceslog'] as &$item){
                Tfk::addExtra($item['id'], ['name' => Utl::extractItem('name', $item) . '-' . Utl::extractItem('reference', $item), 'object' => "bustrackinvoices{$this->customersOrSuppliers}"]);
                SUtl::addItemIdCols($item, ['customer']);
            }
        }
        if (!empty($result['paymentslog'])){
            $result['paymentslog'] = json_decode($result['paymentslog'], true);
            foreach ($result['paymentslog'] as &$item){
                $item['paymenttype'] = $this->tr($item['paymenttype']);
                Tfk::addExtra($item['id'], ['name' => Utl::extractItem('name', $item), 'object' => "bustrackpayments{$this->customersOrSuppliers}"]);
                SUtl::addItemIdCols($item, ['customer', 'category']);
            }
        }
        return $result;
    }
}
?>
