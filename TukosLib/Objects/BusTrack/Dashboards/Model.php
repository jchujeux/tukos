<?php
namespace TukosLib\Objects\BusTrack\Dashboards;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel { 
    function __construct($objectName, $translator=null){
        $colsDefinition = [
            'startdate' => 'date NULL DEFAULT NULL',
            'enddate' => 'date NULL DEFAULT NULL',
            'startdatependinginvoices' => 'date NULL DEFAULT NULL',
            'paymentsflag' => 'VARCHAR(7) DEFAULT NULL',
            'pendinginvoicesflag' => 'VARCHAR(7) DEFAULT NULL',
            'paymentsdetailsflag' => 'VARCHAR(7) DEFAULT NULL',
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
        parent::__construct($objectName, $translator, 'bustrackdashboards', ['parentid' => ['organizations']], [], $colsDefinition,  [], [], ['custom']);
        $this->categoriesModel = Tfk::$registry->get('objectsStore')->objectModel('bustrackcategories');
    }
    public function paymentsDetailsKPIs($organization, $startDate, $endDate){
        $tk = SUtl::$tukosTableName;
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackinvoices`.`id` as `invoiceid`, `t2`.`name` as `invoicename`, `bustrackinvoices`.`reference` as `invoicereference`, `t2`.`parentid` as `customer`,`bustrackinvoices`.`invoicedate`, 
                   `bustrackinvoices`.`pricewt` as `invoiceamount`, 
                   `bustrackpayments`.`id` as `paymentid`, `t0`.`name` as `paymentname`, `bustrackpayments`.`date` as `paymentdate`, 
                   `$tk`.`name` as `paymentitemname`, `bustrackpaymentsitems`.`amount` as `paymentitemamount`, IFNULL(`t3`.`id`, 0) as `category`, `bustrackinvoicesitems`.`vatfree`, `bustrackinvoicesitems`.`vatrate`
            FROM `bustrackpaymentsitems`
                INNER JOIN `$tk` on `$tk`.`id` = `bustrackpaymentsitems`.`id`
                INNER JOIN (`$tk` as `t0` INNER JOIN `bustrackpayments`) on (`t0`.`id` = `bustrackpayments`.`id` AND `$tk`.`parentid` = `bustrackpayments`.`id`)
                INNER JOIN (`$tk` as `t1` INNER JOIN `bustrackinvoicesitems`) on (`t1`.`id` = `bustrackinvoicesitems`.`id` AND `bustrackpaymentsitems`.`invoiceitemid` = `bustrackinvoicesitems`.`id`)
                INNER JOIN (`$tk` as `t2` INNER JOIN `bustrackinvoices`) on (`t2`.`id` = `bustrackinvoices`.`id` AND `bustrackpaymentsitems`.`invoiceid` = `bustrackinvoices`.`id`)
                LEFT JOIN (`$tk` as `t3` INNER JOIN `bustrackcategories`) on (`t3`.`id` = `bustrackcategories`.`id` AND (`bustrackinvoicesitems`.`category` = `bustrackcategories`.`id` OR `bustrackinvoicesitems`.`category` IS NULL))
            WHERE (`bustrackpayments`.`date` >= '$startDate' AND `bustrackpayments`.`date` <= '$endDate' AND `bustrackinvoices`.`organization` = $organization)
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
            SELECT `bustrackinvoices`.`id`, `t0`.`parentid` as `customer`, `t0`.`name`, `bustrackinvoices`.`reference`, `bustrackinvoices`.`invoicedate`,
                   `bustrackinvoices`.`pricewt` as `pricewt`, IFNULL(`pricewt` - sum(`bustrackpaymentsitems`.`amount`), `pricewt`) as `lefttopay`
            FROM `bustrackinvoices`
                INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackinvoices`.`id`
                LEFT JOIN(`bustrackpaymentsitems`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpaymentsitems`.`id` AND `bustrackpaymentsitems`.`invoiceid` = `bustrackinvoices`.`id`
            WHERE (`bustrackinvoices`.`invoicedate` >= '$startDate' AND `bustrackinvoices`.`invoicedate` <= '$endDate' AND `bustrackinvoices`.`organization` = $organization)
            GROUP BY `bustrackinvoices`.`id`
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
            SELECT `bustrackpayments`.`id`, t0.`parentid` as `customer`, `t0`.`name`, `bustrackpayments`.`date`, `bustrackpayments`.`paymenttype`,
                   `bustrackpayments`.`reference` as `paymentreference`, `bustrackpayments`.`slip`,  `bustrackpayments`.`isexplained`,
                   `bustrackpayments`.`amount`, `bustrackpayments`.`amount` - IFNULL(sum(`bustrackpaymentsitems`.`amount`), 0)  as `unassignedamount`, `bustrackpayments`.`category`
            FROM `bustrackpayments`
                INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments`.`id`
                LEFT JOIN(`bustrackpaymentsitems`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpaymentsitems`.`id` AND `t1`.`parentid` = `bustrackpayments`.`id`
            WHERE (`bustrackpayments`.`date` >= '$startDate' AND `bustrackpayments`.`date` <= '$endDate')
            GROUP BY `bustrackpayments`.`id`
EOT
            );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        $kpis['unexpvatfree'] = 0; $kpis['unexpwithvatwot'] = 0; $kpis['unexpvat'] = 0;
        $kpis['expvatfree'] = 0; $kpis['expwithvatwot'] = 0; $kpis['expvat'] = 0;
        $kpis['unaswotpercategory'] = [];
        foreach ($results as $result){
            $categoryId = Utl::getItem('category', $result, 0, 0);
            $vatRate = $this->categoriesModel->vatRate($organization, $categoryId);
            $expOrUnexp = $result['isexplained'] === 'YES' ? 'exp' : 'unexp';
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
        $values = $this->getOne(['where' => $where, 'cols' => ['parentid', 'startdate', 'enddate', 'startdatependinginvoices', 'paymentsflag', 'pendinginvoicesflag', 'paymentsdetailsflag']]);
        if (!empty($values['parentid']) && !empty($values['startdate'])){
            if (empty($values['enddate'])){$values['enddate'] = date('Y-m-d');}
            if ($values['paymentsdetailsflag'] !== 'YES'){
                $newValues = array_merge($newValues, $this->paymentsDetailsKPIs($values['parentid'], $values['startdate'], $values['enddate']));
            }
            if ($values['pendinginvoicesflag'] !== 'YES'){
                $newValues = array_merge($newValues, $this->pendingInvoicesKPIs($values['parentid'], empty($startDate = $values['startdatependinginvoices']) ? $values['startdate'] : $startDate, $values['enddate']));
            }
            if ($values['paymentsflag'] !== 'YES'){
                $newValues = array_merge($newValues, $this->paymentsKPIs($values['parentid'], $values['startdate'], $values['enddate']));
            }
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
            }else{
                Feedback::add($this->tr('Nothingselectednochange'));
            }
        }else{
            Feedback::add($this->tr('Needorgastartend'));
            return false;
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
                Tfk::addExtra($item['invoiceid'], ['name' => Utl::extractItem('invoicename', $item) . '-' . Utl::extractItem('invoicereference', $item), 'object' => 'bustrackinvoices']);
                Tfk::addExtra($item['paymentid'], ['name' => Utl::getItem('paymentname', $item), 'object' => 'bustrackpayments']);
                SUtl::addItemIdCols($item, ['customer']);
            }
        }
        if (!empty($result['pendinginvoiceslog'])){
            $result['pendinginvoiceslog'] = json_decode($result['pendinginvoiceslog'], true);
            foreach ($result['pendinginvoiceslog'] as &$item){
                Tfk::addExtra($item['id'], ['name' => Utl::extractItem('name', $item) . '-' . Utl::extractItem('reference', $item), 'object' => 'bustrackinvoices']);
                SUtl::addItemIdCols($item, ['customer']);
            }
        }
        if (!empty($result['paymentslog'])){
            $result['paymentslog'] = json_decode($result['paymentslog'], true);
            foreach ($result['paymentslog'] as &$item){
                $item['paymenttype'] = $this->tr($item['paymenttype']);
                Tfk::addExtra($item['id'], ['name' => Utl::extractItem('name', $item), 'object' => 'bustrackpayments']);
                SUtl::addItemIdCols($item, ['customer', 'category']);
            }
        }
        return $result;
    }
}
?>
