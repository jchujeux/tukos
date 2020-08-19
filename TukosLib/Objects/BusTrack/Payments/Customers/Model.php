<?php
namespace TukosLib\Objects\BusTrack\Payments\Customers;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\BusTrack\BusTrack;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;

class Model extends AbstractModel {
    
    public $customersOrSuppliers = 'customers';
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'date'        => 'VARCHAR(30)  DEFAULT NULL',
            'paymenttype' => "VARCHAR(255) DEFAULT NULL",
            'reference' => "VARCHAR(255) DEFAULT NULL",// for checks number
            'slip' => "VARCHAR(255) DEFAULT NULL",// for checks slip number
            'amount'   => "DECIMAL (10, 2)",
            'unassignedamount'   => "DECIMAL (10, 2)",
            'isexplained' => 'VARCHAR(7) DEFAULT NULL',
            'category'      => "MEDIUMINT DEFAULT NULL",
            'organization' => 'MEDIUMINT NULL DEFAULT NULL',
        ];
        parent::__construct($objectName, $translator, "bustrackpayments{$this->customersOrSuppliers}", ['parentid' => ['bustrackpeople', 'bustrackorganizations'], 'category' => ['bustrackcategories'], 'organization' => ['bustrackorganizations']], [], $colsDefinition, [],
            [], ['custom'], ['parentid', 'amount', 'date', 'name']);
        $this->setDeleteChildren();
        $this->paymentTypeOptions = BusTrack::paymentTypeOptions($this->customersOrSuppliers);
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['date' => date('Y-m-d')], $init));
    }
    public function unassignedAmountQuery($paymentsIds){
        $inValue = implode(',', $paymentsIds);
        return <<<EOT
    SELECT `bustrackpayments{$this->customersOrSuppliers}`.`id`, `bustrackpayments{$this->customersOrSuppliers}`.`amount`, IFNULL(`bustrackpayments{$this->customersOrSuppliers}`.`amount` - sum(`bustrackpayments{$this->customersOrSuppliers}items`.`amount`), `bustrackpayments{$this->customersOrSuppliers}`.`amount`)  as `unassignedamount`
    FROM `bustrackpayments{$this->customersOrSuppliers}`
        INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments{$this->customersOrSuppliers}`.`id`
        LEFT JOIN(`bustrackpayments{$this->customersOrSuppliers}items`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpayments{$this->customersOrSuppliers}items`.`id` AND `t1`.`parentid` = `bustrackpayments{$this->customersOrSuppliers}`.`id`
    WHERE (`bustrackpayments{$this->customersOrSuppliers}`.`id` IN ($inValue))
    GROUP BY `bustrackpayments{$this->customersOrSuppliers}`.`id`
EOT
        ;
    }
    public function assignedAmountQuery($paymentId){
        return <<<EOT
    SELECT IFNULL(sum(`bustrackpayments{$this->customersOrSuppliers}items`.`amount`), 0)  as `assignedamount`
    FROM `bustrackpayments{$this->customersOrSuppliers}`
        INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments{$this->customersOrSuppliers}`.`id`
        LEFT JOIN(`bustrackpayments{$this->customersOrSuppliers}items`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpayments{$this->customersOrSuppliers}items`.payments{$this->customersOrSuppliers} `t1`.`parentid` = `bustrackpayments{$this->customersOrSuppliers}`.`id`
    WHERE (`bustrackpayments{$this->customersOrSuppliers}`.`id` = $paymentId)
EOT
        ;
    }
    public function updatePayments($paymentsIds){
        $results = SUtl::$tukosModel->store->query($this->unassignedAmountQuery($paymentsIds));
        $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        foreach($results as $result){
            $this->updateOne($result);
        }
    }
    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (($amount = Utl::getItem('amount', $newValues)) && ($id = Utl::getItem('id', $newValues)) && !isset($newValues['unassignedamount'])){
            $result = SUtl::$tukosModel->store->query($this->assignedAmountQuery($id))->fetch(\PDO::FETCH_ASSOC);
            if (!empty($result)){
                $newValues['unassignedamount'] = $amount - $result['assignedamount'];
            }
        }
        return parent::updateOneExtended($newValues, $atts, $insertIfNoOld, $jsonFilter, $init);
    }
}
?>