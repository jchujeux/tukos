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
        parent::__construct($objectName, $translator, 'bustrackpayments', ['parentid' => ['bustrackpeople', 'bustrackorganizations']], [], $colsDefinition, [], [], ['custom'], ['parentid', 'amount', 'date', 'name']);
        $this->setDeleteChildren();
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['date' => date('Y-m-d')], $init));
    }
    function importPayments($query, $values){
        $organization = $values['organization'];
        if (empty($organization)){
            Feedback::add($this->tr('Needorganization'));
            return ['outcome' => 'failure', 'data' => ['payments' => []]];
        }
        $paymentIdentifiers = [1 => 'REMISE DE CHEQUE', 2 => 'REMISE CARTE', 3 => 'VIREMENT', 4 => "VERSEMENT D'ESPECES", 5 => "other"];
        $paymentTypeId = array_flip($paymentIdentifiers);
        $paymentIdentifierPattern = '/(' . implode('|', $paymentIdentifiers) . ')/';
        $paymentsToReturn = [];
        $fileName = $_FILES['uploadedfile']['tmp_name'];
        $workbook = new XlsxInterface();
        $workbook->open($fileName);
        $sheet = $workbook->getSheet('1');
        $numberOfRows = $workbook->numberOfRows($sheet);
        $dateCol = 1; $paymentCol = 4; $descriptionCol = 2; $row = 11;
        $startDate = Utl::getItem('startdate', $values, '1900-01-01');
        $endDate = Utl::getItem('enddate', $values, '9999-12-31', '9999-12-31');
        $categoriesModel = Tfk::$registry->get('objectsStore')->objectModel('bustrackcategories');
        $modelObjects = ['organizations' => Tfk::$registry->get('objectsStore')->objectModel('bustrackorganizations'), 'people' => Tfk::$registry->get('objectsStore')->objectModel('bustrackpeople')];
        $categories = $categoriesModel->getCategories($organization);
        $categoriesFilters = [];
        $getPattern = function($items, $boundary = '\b'){
            return "/($boundary" . implode("$boundary|$boundary", $items) . "$boundary)/i";
        };
        $toUpper = function($item){
            $item['name'] = strtoupper($item['name']);
            return $item;
        };
        foreach ($categories as $id => $category){
            $criterias = $categoriesModel->criterias($organization, $id);
            if (!empty($criterias)){
                $where = [];
                forEach($criterias as $criteria){
                    $where[$criteria['attribute']] = $criteria['value'];
                }
                if ($model = Utl::getItem('customertype', $criteria)){
                    $items = Utl::toAssociative(array_map($toUpper, $modelObjects[$model]->getAll(['where' => $where, 'cols' => ['id', 'name']])), 'name');
                    $categoriesFilters[$id] = ['pattern' => $getPattern(array_keys($items)), 'items' => $items];
                }else{
                    $items = array_map('trim', explode(',', $criteria['value']));
                    $categoriesFilters[$id] = ['pattern' => $getPattern($items)];
                }
            }
        }
        $getDate = function($row) use ($workbook, $sheet, $dateCol){
            return date('Y-m-d', strtotime(str_replace('/', '-', $workbook->getCellValue($sheet, $row, $dateCol))));
        };
        while ($row <= $numberOfRows && $getDate($row) > $endDate){$row +=1;};
        while ($row <= $numberOfRows && $startDate <= ($date = $getDate($row))){
            if ($amount = $workbook->getCellValue($sheet, $row, $paymentCol)){
                $matches = []; $customer = ''; $category = ''; $slip = '';
                $description = $workbook->getCellValue($sheet, $row, $descriptionCol);
                $paymentType = preg_match($paymentIdentifierPattern, $description, $matches) ? $matches[1] : '';
                switch($paymentType){
                    case 'REMISE CARTE': //REMISE CARTE   CARTE 195270101 5689491 12/03
                        $slip = preg_match('/([0-9]*) [^ ]* $/', $description, $matches) ? $matches[1] : '';
                        break;
                    case 'REMISE DE CHEQUE':
                        $slip = preg_match('/([0-9]*)[ ]*$/', $description, $matches) ? $matches[1] : '';
                        break;
                    case 'VIREMENT': 
                        foreach ($categoriesFilters as $categoryId => $filter){
                            if (preg_match($filter['pattern'], str_replace('.', '', $description), $matches)){
                                $customer = isset($filter['items']) ? $filter['items'][strtoupper($matches[1])]['id'] : '';
                                $category = (string)$categoryId;
                                break;
                            }
                        }
                        break;
                    default:
                        $paymentType = "other";
                };
                $paymentsToReturn[] = $paymentsRow =
                    ['date' => $date, 'description' => $description, 'amount' => $amount, 'customer' => $customer, 'paymenttype' => 'paymenttype' . $paymentTypeId[$paymentType], 'category' => $category, 'slip' => $slip];
                SUtl::addItemIdCols($paymentsRow, ['customer', 'category']);
            }
            $row += 1;
        }
        $workbook->close();
        return ['outcome' => 'success', 'data' => ['payments' => $paymentsToReturn]];
    }
    public function syncPayments($query, $values){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $invoicesItemsModel = $objectsStore->objectModel('bustrackinvoicesitems');
        $invoicesModel = $objectsStore->objectModel('bustrackinvoices');
        $paymentsModel = $objectsStore->objectModel('bustrackpayments');
        $paymentsItemsModel = $objectsStore->objectModel('bustrackpaymentsitems');
        $categoriesModel = $objectsStore->objectModel('bustrackcategories');
        $organization = $values['organization'];
        foreach($values['payments'] as &$payment){
            $existingPayment = $paymentsModel->getOne(['where' => ['date' => $payment['date'], 'name' => $payment['description']], 'cols' => ['id']]);
            if (!empty($existingPayment)){
                Feedback::add("{$this->tr('paymentalreadyexists')}: id = {$existingPayment['id']}");
                $payment['id'] = $existingPayment['id'];
            }else{
                $category = Utl::getItem('category', $payment);
                if(Utl::getItem('createinvoice', $payment, false, false)){
                    $vatRate = $categoriesModel->vatRate($organization, $category);
                    $payment['invoiceid'] = $invoiceId = $invoicesModel->insert(['parentid' => $payment['customer'], 'name' => $payment['description'], 'invoicedate' => $payment['date'], 'pricewt' => $payment['amount'],
                        'organization' => $organization], true)['id'];
                    $payment['invoiceitemid'] = $invoiceItemId = $invoicesItemsModel->insert(['parentid' => $invoiceId, 'name' => $payment['description'], 'category' => $category, 
                        'vatfree' => $categoriesModel->vatFree($organization, $category),'vatrate' => $vatRate, 'pricewt' => $payment['amount'], 
                        'pricewot' => $payment['amount'] / (1 + $vatRate)
                    ], true)['id'];
                }else{
                    $invoiceItemId = Utl::getItem('invoiceitemid', $payment);
                    $invoiceId = Utl::getItem('invoiceid', $payment);
                }
                $insertedPayment = $paymentsModel->insert(['parentid' => $payment['customer'], 'date' => $payment['date'], 'paymenttype' => $payment['paymenttype'], 'reference' => Utl::getItem('reference', $payment), 
                    'slip' => Utl::getItem('slip', $payment), 'amount' => $payment['amount'], 'category' => $category, 'organization' => $organization, 'name' => $payment['description']], true);
                if ($invoiceItemId){
                    $paymentsItemsModel->insert(['parentid' => $insertedPayment['id'], 'name' => $payment['description'], 'amount' => $payment['amount'], 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId], true);
                }
                $payment['id'] = $insertedPayment['id'];
            }
        }
        return ['data' => ['payments' => $values['payments']]];
    }
    public function updatePayments($paymentsIds){
        $inValue = implode(',', $paymentsIds);
        $results = SUtl::$tukosModel->store->query(<<<EOT
            SELECT `bustrackpayments`.`id`, `bustrackpayments`.`amount`, IFNULL(`bustrackpayments`.`amount` - sum(`bustrackpaymentsitems`.`amount`), `bustrackpayments`.`amount`)  as `unassignedamount`
            FROM `bustrackpayments`
                INNER JOIN (`tukos` as `t0`) ON `t0`.`id` = `bustrackpayments`.`id`
                LEFT JOIN(`bustrackpaymentsitems`, `tukos` as `t1`) ON `t1`.`id` = `bustrackpaymentsitems`.`id` AND `t1`.`parentid` = `bustrackpayments`.`id`
            WHERE (`bustrackpayments`.`id` IN ($inValue))
            GROUP BY `bustrackpayments`.`id`
EOT
            );
        $results = $results->fetchAll(\PDO::FETCH_ASSOC);
        foreach($results as $result){
            $this->updateOne($result);
        }
    }
}
?>