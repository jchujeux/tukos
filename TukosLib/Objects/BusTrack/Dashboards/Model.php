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
            'paymentsflag' => 'VARCHAR(7) DEFAULT NULL',
            'pendinginvoicesflag' => 'VARCHAR(7) DEFAULT NULL',
            'unassignedpaymentsflag' => 'VARCHAR(7) DEFAULT NULL',
            'paymentscount'  => 'MEDIUMINT DEFAULT NULL',
            'paidvatfree' => "DECIMAL (5, 2)",
            'paidwithvatwot' => "DECIMAL (5, 2)",
            'paidvat' => "DECIMAL (5, 2)",
            'paidwotpercategory' => 'longtext',
            'invoicesclosedcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesopenedcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesongoingcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesduewot' => "DECIMAL (5, 2)",
            'pendingamount' => "DECIMAL (5, 2)",
            'unassignedamount' => "DECIMAL (5, 2)",
            'paymentslog' => 'longtext',
            'pendinginvoiceslog' => 'longtext',
            'unassignedpaymentslog' => 'longtext'
        ];
        parent::__construct($objectName, $translator, 'bustrackdashboards', ['parentid' => ['organizations']], [], $colsDefinition,  [], [], ['custom']);
    }
    public function paymentsKPIs($organization, $startDate, $endDate){
        $tk = SUtl::$tukosTableName; $nonCategorized = $this->tr('noncategorized');
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackinvoices`.`id` as `invoiceid`, `t2`.`name` as `invoicename`, `bustrackinvoices`.`reference` as `invoicereference`, `t2`.`parentid` as `customer`,`bustrackinvoices`.`invoicedate`, 
                   `bustrackinvoices`.`pricewt` as `invoiceamount`, 
                   `bustrackpayments`.`id` as `paymentid`, `t0`.`name` as `paymentname`, `bustrackpayments`.`date` as `paymentdate`, `bustrackpayments`.`paymenttype`, 
                   `bustrackpayments`.`reference` as `paymentreference`, `bustrackpayments`.`slip`,
                   `$tk`.`name` as `paymentitemname`, `bustrackpaymentsitems`.`amount` as `paymentitemamount`, IFNULL(`t3`.`name`, '$nonCategorized') as `category`, `bustrackinvoicesitems`.`vatfree`, `bustrackinvoicesitems`.`vatrate`
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
        $kpis['paymentscount'] = 0; $kpis['paidvatfree'] = 0; $kpis['paidwithvatwot'] = 0; $kpis['paidvat'] = 0; 
        $kpis['paidwotpercategory'] = [];
        
        foreach ($results as $result){
            $kpis['paymentscount'] += 1;
            if (empty($result['vatfree'])){
                $paidwot = $result['paymentitemamount']/ (1 + $result['vatrate']);
                $kpis['paidwithvatwot'] += $paidwot;
                $kpis['paidvat'] += $paidwot * $result['vatrate'];
                Utl::increment($kpis['paidwotpercategory'], $result['category'], $paidwot);
            }else{
                $kpis['paidvatfree'] += $result['paymentitemamount'];
                Utl::increment($kpis['paidwotpercategory'], $result['category'], $result['paymentitemamount']);
            }
        }
        $kpis['paymentslog'] = $results;
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
    public function unassignedPaymentsKPIs($organization, $startDate, $endDate){
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackpayments`.`id`, t0.`parentid` as `customer`, `t0`.`name`, `bustrackpayments`.`date`, `bustrackpayments`.`paymenttype`, 
                   `bustrackpayments`.`amount`, IFNULL(`bustrackpayments`.`amount` - sum(`bustrackpaymentsitems`.`amount`), `bustrackpayments`.`amount`)  as `unassignedamount`
            FROM `bustrackpayments`
                INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments`.`id`
                LEFT JOIN(`bustrackpaymentsitems`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpaymentsitems`.`id` AND `t1`.`parentid` = `bustrackpayments`.`id`
            WHERE (`bustrackpayments`.`date` >= '$startDate' AND `bustrackpayments`.`date` <= '$endDate')
            GROUP BY `bustrackpayments`.`id`
            HAVING `unassignedamount` <> 0 OR `unassignedamount` IS NULL
EOT
            );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC); $unassignedAmount = 0;
        foreach ($results as $result){
            $unassignedAmount += $result['unassignedamount'];
        }
        return ['unassignedamount' => $unassignedAmount, 'unassignedpaymentslog' => $results];
    }
    function processOne($where){
        $where = $this->user->filter($where, $this->objectName); $newValues = ['paymentslog' => '', 'pendinginvoiceslog' => '', 'unassignedpaymentslog' => ''];
        $values = $this->getOne(['where' => $where, 'cols' => ['parentid', 'startdate', 'enddate', 'paymentsflag', 'pendinginvoicesflag', 'unassignedpaymentsflag']]);
        if (!empty($values['parentid']) && !empty($values['startdate'])){
            if (empty($values['enddate'])){$values['enddate'] = date('Y-m-d');}
            if ($values['paymentsflag'] !== 'YES'){
                $newValues = array_merge($newValues, $this->paymentsKPIs($values['parentid'], $values['startdate'], $values['enddate']));
                $newValues['paidwotpercategory']     = Utl::toStoreData($newValues['paidwotpercategory'], 'category', 'amount');
            }
            if ($values['pendinginvoicesflag'] !== 'YES'){
                $newValues = array_merge($newValues, $this->pendingInvoicesKPIs($values['parentid'], $values['startdate'], $values['enddate']));
            }
            if ($values['unassignedpaymentsflag'] !== 'YES'){
                $newValues = array_merge($newValues, $this->unassignedPaymentsKPIs($values['parentid'], $values['startdate'], $values['enddate']));
            }
            if (!empty($newValues)){
                $newValues = Utl::jsonEncodeArray($newValues);
                $this->updateOne($newValues, ['where' => $where]);
                Feedback::add($this->tr('Dashboard updated'));
            }else{
                Feedback::add($this->tr('Nothingslectednochange'));
            }
        }else{
            Feedback::add($this->tr('Need organization, startdate and enddate'));
        }
        return [];
    }
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $result = parent::getOneExtended($atts);
        $result['paidwot'] = $result['paidvatfree'] + $result['paidwithvatwot'];
        $result['paidwt'] = $result['paidwot'] + $result['paidvat'];
        $kpiCol = 'paidwotpercategory';
        if (!empty($result[$kpiCol])){
            $values = json_decode($result[$kpiCol], true);
            $result[$kpiCol] = ['store' => $values];
        }
        if (!empty($result['paymentslog'])){
            $result['paymentslog'] = json_decode($result['paymentslog'], true);
            foreach ($result['paymentslog'] as &$item){
                $item['paymenttype'] = $this->tr($item['paymenttype']);
                Tfk::addExtra($item['invoiceid'], ['name' => Utl::extractItem('invoicename', $item) . '-' . Utl::extractItem('invoicereference', $item), 'object' => 'bustrackinvoices']);
                Tfk::addExtra($item['paymentid'], ['name' => Utl::getItem('paymentname', $item), 'object' => 'bustrackpayments']);
                SUtl::addItemIdCols($item, ['customer']);
            }
        }
        if (!empty($result['pendinginvoiceslog'])){
            $result['pendinginvoiceslog'] = json_decode($result['pendinginvoiceslog'], true);
            SUtl::addItemsIdCols($result, ['customer']);
        }
        if (!empty($result['unassignedpaymentslog'])){
            $result['unassignedpaymentslog'] = json_decode($result['unassignedpaymentslog'], true);
            foreach ($result['unassignedpaymentslog'] as &$item){
                $item['paymenttype'] = $this->tr($item['paymenttype']);
                Tfk::addExtra($item['id'], ['name' => Utl::getItem('name', $item), 'object' => 'bustrackpayments']);
                SUtl::addItemIdCols($item, ['customer']);
            }
        }
        return $result;
    }
}
?>
