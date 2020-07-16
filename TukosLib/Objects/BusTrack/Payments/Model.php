<?php
namespace TukosLib\Objects\BusTrack\Payments;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\XlsxInterface;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    
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
        parent::__construct($objectName, $translator, 'bustrackpayments', ['parentid' => ['bustrackpeople', 'bustrackorganizations'], 'category' => ['bustrackcategories'], 'organization' => ['bustrackorganizations']], [], $colsDefinition, [],
            [], ['custom'], ['parentid', 'amount', 'date', 'name']);
        $this->setDeleteChildren();
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['date' => date('Y-m-d')], $init));
    }
    public function unassignedAmountQuery($paymentsIds){
        $inValue = implode(',', $paymentsIds);
        return <<<EOT
    SELECT `bustrackpayments`.`id`, `bustrackpayments`.`amount`, IFNULL(`bustrackpayments`.`amount` - sum(`bustrackpaymentsitems`.`amount`), `bustrackpayments`.`amount`)  as `unassignedamount`
    FROM `bustrackpayments`
        INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments`.`id`
        LEFT JOIN(`bustrackpaymentsitems`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpaymentsitems`.`id` AND `t1`.`parentid` = `bustrackpayments`.`id`
    WHERE (`bustrackpayments`.`id` IN ($inValue))
    GROUP BY `bustrackpayments`.`id`
EOT
        ;
    }
    public function assignedAmountQuery($paymentId){
        return <<<EOT
    SELECT IFNULL(sum(`bustrackpaymentsitems`.`amount`), 0)  as `assignedamount`
    FROM `bustrackpayments`
        INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments`.`id`
        LEFT JOIN(`bustrackpaymentsitems`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpaymentsitems`.`id` AND `t1`.`parentid` = `bustrackpayments`.`id`
    WHERE (`bustrackpayments`.`id` = $paymentId)
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