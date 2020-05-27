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
        parent::__construct($objectName, $translator, 'bustrackreconciliations', ['parentid' => ['bustrackpeople', 'bustrackorganizations']], ['paymentslog'], $colsDefinition, [], [], ['custom']);
        $this->gridsIdCols = array_merge($this->gridsIdCols, ['paymentslog' => ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid']]);
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
        $objectsStore = Tfk::$registry->get('objectsStore');
        $categoriesModel = $objectsStore->objectModel('bustrackcategories');
        $modelObjects = ['organizations' => $objectsStore->objectModel('bustrackorganizations'), 'people' => $objectsStore->objectModel('bustrackpeople')];
        $invoicesModel = $objectsStore->objectModel('bustrackinvoices');
        $invoicesItemsModel = $objectsStore->objectModel('bustrackinvoicesitems');
        $paymentsModel = $objectsStore->objectModel('bustrackpayments');
        $categories = $categoriesModel->getCategories($organization);
        $categoriesFilters = [];
        $getPattern = function($items, $boundary = '\b'){
            return "/(.*)($boundary" . implode("$boundary|$boundary", $items) . "$boundary)(.*)/is";
        };
        $getSqlPattern = function($items){
            $beginWord = "[[:<:]]"; $endWord = "[[:>:]]";
            return "$beginWord" . implode("$endWord|$beginWord", $items) . "$endWord";
        };
        $toUpper = function($item){
            $item['name'] = strtoupper($item['name']);
            return $item;
        };
        foreach ($categories as $id => $category){
            $criterias = $categoriesModel->criterias($organization, $id);
            if (!empty($criterias)){
                $where = []; $whereModel = ''; $categoriesFilters[$id] = [];
                foreach($criterias as $criteria){
                    if ($model = Utl::getItem('customertype', $criteria)){
                        $whereModel = $model;
                        if ($value = Utl::getItem('value', $criteria)){
                            $where[$criteria['attribute']] = $criteria['value'];
                        }
                        $valueItems = [];
                    }else{
                        $valueItems = array_map('trim', explode(',', $criteria['value']));
                    }
                }
                if (!empty($whereModel)){
                    $items = Utl::toAssociative(array_map($toUpper, $modelObjects[$whereModel]->getAll(['where' => $where, 'cols' => ['id', 'name'], 'union' => false])), 'name');
                    if (!empty($items)){
                        $categoriesFilters[$id] = ['customerPattern' => $getPattern(array_keys($items)), 'sqlCustomerPattern' => $getSqlPattern(array_keys($items)), 'items' => $items];
                    }
                }
                if (!empty($valueItems)){
                    $categoriesFilters[$id] = array_merge($categoriesFilters[$id], ['valuePattern' => $getPattern($valueItems), 'valueSqlPattern' => $getSqlPattern($valueItems)]);
                }
                $categoriesFilters[$id] = array_merge($categoriesFilters[$id], Utl::getItems(['removematch', 'searchpayments', 'searchinvoices'], $criteria));
            }
        }
        $getDate = function($row) use ($workbook, $sheet, $dateCol){
            return date('Y-m-d', strtotime(str_replace('/', '-', $workbook->getCellValue($sheet, $row, $dateCol))));
        };
        $id = 0;
        while ($row <= $numberOfRows && $getDate($row) > $endDate){$row +=1;};
        while ($row <= $numberOfRows && $startDate <= ($date = $getDate($row))){
            if ($amount = $workbook->getCellValue($sheet, $row, $paymentCol)){
                $id +=1;
                $matches = []; $customerMatches = []; $customerId = ''; $valueMatches = []; $category = ''; $slip = ''; $isExplained = ''; $paymentId = ''; $invoiceId = ''; $invoiceItemId = '';
                $description = $workbook->getCellValue($sheet, $row, $descriptionCol);
                $paymentTypeMatch = preg_match($paymentIdentifierPattern, $description, $matches) ? $matches[1] : 'other';
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
                            $hasValueMatch = 0;
                            $customerId = (isset($filter['customerPattern']) && preg_match($filter['customerPattern'], str_replace('.', '', $description), $customerMatches)) ? $filter['items'][strtoupper($customerMatches[2])]['id'] : '';
                            if ($customerId && (!isset($filter['valuePattern']) || $hasValueMatch = preg_match($filter['valuePattern'], str_replace('.', '', $description), $valueMatches))){
                                if ($customerId && isset($filter['removematch'])){
                                    $description = $customerMatches[1] . $customerMatches[3];
                                }
                                $category = (string)$categoryId;
                                $isExplained = 'YES';
                                $where = $customerId ? ['parentid' => $customerId] : [];
                                if (isset($filter['searchpayments'])){
                                    if ($hasValueMatch){
                                        $where = array_merge($where, [['col' => 'name', 'opr' => 'RLIKE', 'values' => $filter['valueSqlPattern']]]);
                                    }
                                    $existingPayments = $paymentsModel->getAll(['where' => array_merge($where, ['date' => $date, 'amount' => $amount]), 'cols' => ['id']]);
                                    $paymentId = Utl::drillDown($existingPayments, [0, 'id']);
                                    if (count($existingPayments)> 1){
                                        Feedback::add("{$this->tr('Severalpaymentsfound')}: $id - {$this->tr('Selectedid')}: $paymentId");
                                    }
                                }
                                if (isset($filter['searchinvoices'])){
                                    $existingInvoices = $invoicesModel->getAll(['where' => array_merge($where, ['pricewt' => $amount]), 'cols' => ['id'], 'orderBy' => ['invoicedate' =>  'DESC']/*, 'union' => false*/]);
                                    $invoiceId = Utl::drillDown($existingInvoices, [0, 'id']);
                                    if (count($existingInvoices)>= 1){
                                        Feedback::add("{$this->tr('Severalinvoicesfound')}: $id - {$this->tr('Mostrecentselected')}: $invoiceId");
                                    }
                                    $invoiceId = Utl::drillDown($existingInvoices, [0, 'id']);
                                    if ($invoiceId){
                                        $existingInvoiceItemId = $invoicesItemsModel->getOne(['where' => ['parentid' => $invoiceId, 'pricewt' => $amount], 'cols' => ['id']]);
                                        $invoiceItemId = Utl::getItem('id', $existingInvoiceItemId);
                                    }
                                }
                                break;
                            }
                        }
                        break;
                };
                $paymentsToReturn[] = $paymentsRow = ['id' => $id, 'date' => $date, 'description' => $description, 'amount' => $amount, 'customer' => $customerId, 'paymenttype' => 'paymenttype' . $paymentTypeId[$paymentTypeMatch], 
                    'category' => $category, 'slip' => $slip, 'isexplained' => $isExplained, 'paymentid' => $paymentId, 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId];
                SUtl::addItemIdCols($paymentsRow, ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid']);
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
        $existingPaymentsIds = [];
        foreach($payments as &$payment){
            if($date = Utl::getItem('date', $payment)){
                //$description = Utl::getItem('description', $payment); $amount = number_format(Utl::getItem('amount', $payment), 2); $category = Utl::getItem('category', $payment); $customer = Utl::getItem('customer', $payment);
                $description = Utl::getItem('description', $payment); $amount = Utl::getItem('amount', $payment); $category = Utl::getItem('category', $payment); $customer = Utl::getItem('customer', $payment);
                if (!$paymentId = Utl::getItem('paymentid', $payment)){
                    $existingPayment = $paymentsModel->getOne(['where' => ['date' => $date, 'name' => $description, 'amount' => $amount], 'cols' => ['id']]);
                    if (!empty($existingPayment)){
                        $existingPaymentsIds[] = $payment['paymentid'] = $paymentId = $existingPayment['id'];
                    }
                }
                if(Utl::getItem('createinvoice', $payment, false, false)){
                    if ($category && $customer){
                        $vatRate = $categoriesModel->vatRate($organization, $category);
                        $payment['invoiceid'] = $invoiceId = $invoicesModel->insert(['parentid' => $customer, 'organization' => $organization, 'name' => $description, 'invoicedate' => $date, 'pricewt' => $payment['amount'],
                            'organization' => $organization], true)['id'];
                        $payment['invoiceitemid'] = $invoiceItemId = $invoicesItemsModel->insert(['parentid' => $invoiceId, 'name' => $description, 'category' => $category,
                            'vatfree' => $categoriesModel->vatFree($organization, $category),'vatrate' => $vatRate, 'pricewt' => $amount,
                            'pricewot' => $amount / (1 + $vatRate)
                        ], true)['id'];
                    }else{
                        $invoiceItemId = '';
                        Feedback::add($this->tr('RowNeedsCategoryInvoiceNotCreated') . ": {$payment['id']}");
                    }
                }else{
                    $invoiceItemId = Utl::getItem('invoiceitemid', $payment);
                    $invoiceId = Utl::getItem('invoiceid', $payment);
                }
                $unassignedAmount = $invoiceItemId ? 0.0 : $amount;
                if ($paymentId){
                    $paymentsModel->updateOne(array_filter(['id' => $paymentId, 'parentid' => $customer, 'date' => $date, 'paymenttype' => Utl::getItem('paymenttype', $payment), 'reference' => Utl::getItem('reference', $payment),
                        'slip' => Utl::getItem('slip', $payment), 'amount' => $amount, 'unassignedamount' => $unassignedAmount, 'category' => $category, 'organization' => $organization,
                        'name' => $description, 'isexplained' => Utl::getItem('isexplained', $payment)
                    ]));
                }else{
                    $insertedPayment = $paymentsModel->insert(['parentid' => $customer, 'date' => $date, 'paymenttype' => Utl::getItem('paymenttype', $payment), 'reference' => Utl::getItem('reference', $payment),
                        'slip' => Utl::getItem('slip', $payment), 'amount' => $amount, 'unassignedamount' => $unassignedAmount, 'category' => $category, 'organization' => $organization,
                        'name' => $description, 'isexplained' => Utl::getItem('isexplained', $payment)
                    ], true);
                    $paymentId = $payment['paymentid'] = $insertedPayment['id'];
                    if ($invoiceItemId){
                        $paymentsItemsModel->insert(['parentid' => $paymentId, 'name' => $description, 'amount' => $amount, 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId], true);
                    }
                }
                SUtl::addItemIdCols($payment, ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid']);
            }else{
                Feedback::add($this->tr('RowNeedsDateIgnored') . ": {$payment['id']}");
            }
        }
        if (!empty($existingPaymentsIds)){
            Feedback::add("{$this->tr('Existingpaymentsselected')}: " . implode(',', $existingPaymentsIds));
            
        }
        return ['data' => ['paymentslog' => $payments]];
    }
}
?>