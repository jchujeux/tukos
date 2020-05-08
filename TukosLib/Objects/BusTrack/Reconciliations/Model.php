<?php
namespace TukosLib\Objects\BusTrack\Reconciliations;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\XlsxInterface;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    
    function __construct($objectName, $translator=null){
        $colsDefinition =  [
            'startdate' => 'date NULL DEFAULT NULL',
            'enddate' => 'date NULL DEFAULT NULL',
            'paymentslog' => 'longtext',
        ];
        parent::__construct($objectName, $translator, 'bustrackreconciliations', ['parentid' => ['bustrackpeople', 'bustrackorganizations']], ['paymentslog'], $colsDefinition, [], ['worksheet', 'custom']);
    }
    function initialize($init=[]){
        return parent::initialize(array_merge(['date' => date('Y-m-d')], $init));
    }
    function importPayments($query, $values){
        $organization = $values['parentid'];
        if (empty($organization)){
            Feedback::add($this->tr('Needorganization'));
            return ['outcome' => 'failure', 'data' => ['payments' => []]];
        }
        $paymentIdentifiers = [1 => "REMISE DE CHEQUE        \n", 2 => "REMISE CARTE            \n", 3 => "VIREMENT EN VOTRE FAVEUR\n", 4 => "VERSEMENT D'ESPECES\n", 5 => "other"];
        $paymentTypeId = array_flip($paymentIdentifiers);
        $paymentIdentifierPattern = '/(' . implode('|', $paymentIdentifiers) . ')(.*)/s';
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
        $id = 0;
        while ($row <= $numberOfRows && $getDate($row) > $endDate){$row +=1;};
        while ($row <= $numberOfRows && $startDate <= ($date = $getDate($row))){
            if ($amount = $workbook->getCellValue($sheet, $row, $paymentCol)){
                $matches = []; $customer = ''; $category = ''; $slip = ''; $isExplained = '';
                $description = $workbook->getCellValue($sheet, $row, $descriptionCol);
                $paymentTypeMatch = preg_match($paymentIdentifierPattern, $description, $matches) ? $matches[1] : 'other';
                //$description = substr($description, strlen($matches[1]));
                $description = $matches ? $matches[2] : $description;
                switch($paymentTypeMatch){
                    case $paymentIdentifiers[1]:
                        $slip = preg_match('/([0-9]*)[ ]*$/', $description, $matches) ? $matches[1] : '';
                        break;
                    case $paymentIdentifiers[2]: //REMISE CARTE   CARTE 195270101 5689491 12/03
                        $slip = preg_match('/([0-9]*) [^ ]* $/', $description, $matches) ? $matches[1] : '';
                        break;
                    case $paymentIdentifiers[3]: 
                        foreach ($categoriesFilters as $categoryId => $filter){
                            if (preg_match($filter['pattern'], str_replace('.', '', $description), $matches)){
                                $customer = isset($filter['items']) ? $filter['items'][strtoupper($matches[1])]['id'] : '';
                                $category = (string)$categoryId;
                                $isExplained = 'YES';
                                break;
                            }
                        }
                        break;
                };
                $paymentsToReturn[] = $paymentsRow = ['id' => $id += 1, 'date' => $date, 'description' => $description, 'amount' => $amount, 'customer' => $customer, 'paymenttype' => 'paymenttype' . $paymentTypeId[$paymentTypeMatch], 
                    'category' => $category, 'slip' => $slip, 'isexplained' => $isExplained];
                SUtl::addItemIdCols($paymentsRow, ['customer', 'category']);
            }
            $row += 1;
        }
        $workbook->close();
        //$this->updateOne(['id' => $values['id'], 'paymentslog' => '']);
        return ['outcome' => 'success', 'data' => ['payments' => $paymentsToReturn]];
    }
    public function syncPayments($query, $values){
        $id = Utl::getItem('id', $query);
        $objectsStore = Tfk::$registry->get('objectsStore');
        $invoicesItemsModel = $objectsStore->objectModel('bustrackinvoicesitems');
        $invoicesModel = $objectsStore->objectModel('bustrackinvoices');
        $paymentsModel = $objectsStore->objectModel('bustrackpayments');
        $paymentsItemsModel = $objectsStore->objectModel('bustrackpaymentsitems');
        $categoriesModel = $objectsStore->objectModel('bustrackcategories');
        $organization = $values['organization'];
        $payments = $values['payments'];
        foreach($payments as &$payment){
            if($date = Utl::getItem('date', $payment)){
                $description = Utl::getItem('description', $payment);
                $existingPayment = $paymentsModel->getOne(['where' => ['date' => $date, 'name' => $description], 'cols' => ['id']]);
                if (!empty($existingPayment)){
                    Feedback::add("{$this->tr('paymentalreadyexists')}: id = {$existingPayment['id']}");
                    $payment['paymentid'] = $existingPayment['id'];
                }else{
                    $category = Utl::getItem('category', $payment);
                    $customer = Utl::getItem('customer', $payment);
                    $amount = Utl::getItem('amount', $payment);
                    if(Utl::getItem('createinvoice', $payment, false, false)){
                        $vatRate = $categoriesModel->vatRate($organization, $category);
                        $payment['invoiceid'] = $invoiceId = $invoicesModel->insert(['parentid' => $customer, 'name' => $description, 'invoicedate' => $date, 'pricewt' => $payment['amount'],
                            'organization' => $organization], true)['id'];
                        $payment['invoiceitemid'] = $invoiceItemId = $invoicesItemsModel->insert(['parentid' => $invoiceId, 'name' => $description, 'category' => $category,
                            'vatfree' => $categoriesModel->vatFree($organization, $category),'vatrate' => $vatRate, 'pricewt' => $amount,
                            'pricewot' => $amount / (1 + $vatRate)
                        ], true)['id'];
                    }else{
                        $invoiceItemId = Utl::getItem('invoiceitemid', $payment);
                        $invoiceId = Utl::getItem('invoiceid', $payment);
                    }
                    $unassignedAmount = $invoiceItemId ? 0.0 : $amount;
                    $insertedPayment = $paymentsModel->insert(['parentid' => $customer, 'date' => $date, 'paymenttype' => Utl::getItem('paymenttype', $payment), 'reference' => Utl::getItem('reference', $payment),
                        'slip' => Utl::getItem('slip', $payment), 'amount' => $amount, 'unassignedamount' => $unassignedAmount, 'category' => $category, 'organization' => $organization, 
                        'name' => $description, 'isexplained' => Utl::getItem('isexplained', $payment)
                    ], true);
                    if ($invoiceItemId){
                        $paymentsItemsModel->insert(['parentid' => $insertedPayment['id'], 'name' => $description, 'amount' => $amount, 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId], true);
                    }
                    $payment['paymentid'] = $insertedPayment['id'];
                    SUtl::addItemIdCols($payment, ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid']);
                }
            }else{
                Feedback::add($this->tr('RowNeedsDateIgnored') . ": {$payment['id']}");
            }
        }
        if (!empty($payments)){
            $this->updateOne(['id' => $id, 'paymentslog' => Utl::toAssociative($payments, 'id')]);
        }
        return [];
    }
}
?>