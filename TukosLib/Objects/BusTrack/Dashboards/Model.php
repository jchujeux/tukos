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
            'paymentscount'  => 'MEDIUMINT DEFAULT NULL',
            'paidvatfree' => "DECIMAL (5, 2)",
            'paidwithvatwot' => "DECIMAL (5, 2)",
            'paidvat' => "DECIMAL (5, 2)",
            'paidwotpercategory' => 'longtext',
            'invoicesclosedcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesopenedcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesongoingcount' => 'MEDIUMINT DEFAULT NULL',
            'invoicesduewot' => "DECIMAL (5, 2)",
            'paymentslog' => 'longtext'];
        parent::__construct($objectName, $translator, 'bustrackdashboards', ['parentid' => ['organizations']], [], $colsDefinition,  [], [], ['custom']);
    }
    public function currentKPIs($organization, $startDate, $endDate){
        $tk = SUtl::$tukosTableName;
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackinvoices`.`id`, `t2`.`name` as `invoicename`, `$tk`.`name`, `bustrackpaymentsitems`.`amount`, `bustrackpayments`.`date`, `bustrackpayments`.`paymenttype`, `bustrackpayments`.`reference` as `paymentreference`,
                `bustrackpayments`.`slip`, `t3`.`name` as `category`, `bustrackinvoicesitems`.`vatfree`, `bustrackinvoicesitems`.`vatrate`, `bustrackinvoices`.`reference`, `bustrackinvoices`.`invoicedate`
            FROM `bustrackpaymentsitems`
                INNER JOIN `$tk` on `$tk`.`id` = `bustrackpaymentsitems`.`id`
                INNER JOIN (`$tk` as `t0` INNER JOIN `bustrackpayments`) on (`t0`.`id` = `bustrackpayments`.`id` AND `$tk`.`parentid` = `bustrackpayments`.`id`)
                INNER JOIN (`$tk` as `t1` INNER JOIN `bustrackinvoicesitems`) on (`t1`.`id` = `bustrackinvoicesitems`.`id` AND `bustrackpaymentsitems`.`invoiceitemid` = `bustrackinvoicesitems`.`id`)
                INNER JOIN (`$tk` as `t2` INNER JOIN `bustrackinvoices`) on (`t2`.`id` = `bustrackinvoices`.`id` AND `bustrackpaymentsitems`.`invoiceid` = `bustrackinvoices`.`id`)
                INNER JOIN (`$tk` as `t3` INNER JOIN `bustrackcategories`) on (`t3`.`id` = `bustrackcategories`.`id` AND `bustrackinvoicesitems`.`category` = `bustrackcategories`.`id`)
            WHERE (`bustrackpayments`.`date` >= '$startDate' AND `bustrackpayments`.`date` <= '$endDate' AND `bustrackinvoices`.`organization` = $organization);
EOT
        );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        $kpis['startdate'] = $startDate; $kpis['enddate'] = $endDate; $kpis['parentid'] = $organization; 
        $kpis['paymentscount'] = 0; $kpis['paidvatfree'] = 0; $kpis['paidwithvatwot'] = 0; $kpis['paidvat'] = 0; 
        $kpis['paidwotpercategory'] = [];
        
        foreach ($results as $i => $result){
            $kpis['paymentscount'] += 1;
            if (empty($result['vatfree'])){
                $paidwot = $result['amount']/ (1 + $result['vatrate']);
                $kpis['paidwithvatwot'] += $paidwot;
                $kpis['paidvat'] += $paidwot * $result['vatrate'];
                Utl::increment($kpis['paidwotpercategory'], $result['category'], $paidwot);
            }else{
                $kpis['paidvatfree'] += $result['amount'];
                Utl::increment($kpis['paidwotpercategory'], $result['category'], $result['amount']);
            }
        }
        $kpis['paymentslog'] = $results;
        return $kpis;
    }
    function processOne($where){
        $where = $this->user->filter($where, $this->objectName);
        $values = $this->getOne(['where' => $where, 'cols' => ['parentid', 'startdate', 'enddate']]);
        if (!empty($values['parentid']) && !empty($values['startdate']) && !empty($values['enddate'])){
            $kpiValues = $this->currentKPIs($values['parentid'], $values['startdate'], $values['enddate']);
            $kpiValues['paidwotpercategory']     = Utl::toStoreData($kpiValues['paidwotpercategory'], 'category', 'amount');
            $kpiValues = Utl::jsonEncodeArray($kpiValues);
            $this->updateOne($kpiValues, ['where' => $where]);
            Feedback::add($this->tr('Dashboard updated'));
        }else{
            Feedback::add($this->tr('Need organization, startdate and enddate'));
        }
        return [];
    }
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $tr = $this->tr; $tPer = $tr('per');
        $result = parent::getOneExtended($atts);
        $result['paidwot'] = $result['paidvatfree'] + $result['paidwithvatwot'];
        $result['paidwt'] = $result['paidwot'] + $result['paidvat'];
        $kpiAttTypes = ['category'];
        $kpiValTypes = ['amount'];
        $kpiCol = 'paidwotpercategory';
        if (!empty($result[$kpiCol])){
            $values = json_decode($result[$kpiCol], true);
            $result[$kpiCol] = ['store' => $values];
        }
        if (!empty($result['paymentslog'])){
            $result['paymentslog'] = json_decode($result['paymentslog'], true);
            $extraIds = [];
            foreach ($result['paymentslog'] as &$item){
                $item['paymenttype'] = $this->tr($item['paymenttype']);
                Tfk::addExtra($item['id'], ['name' => Utl::extractItem('invoicename', $item), 'object' => 'bustrackinvoices']);
            }
        }
        return $result;
    }
}
?>
