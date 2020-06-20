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
        parent::__construct($objectName, $translator, 'bustrackreconciliations', ['parentid' => ['bustrackorganizations']], ['paymentslog'], $colsDefinition, [], [], ['custom']);
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
                                $description = preg_replace("/\n|\r/", "", trim(($customerId && isset($filter['removematch'])) ? ($customerMatches[1] . $customerMatches[3]) : $description));
                                $category = (string)$categoryId;
                                $isExplained = 'YES';
                                $where = $customerId ? ['parentid' => $customerId] : [];
                                if (isset($filter['searchpayments'])){
                                    if ($hasValueMatch){
                                        $where = array_merge($where, [['col' => 'name', 'opr' => 'RLIKE', 'values' => $filter['valueSqlPattern']]]);
                                    }
                                    $existingPayments = $paymentsModel->getAll(['where' => array_merge($where, ['date' => $date, 'amount' => $amount]), 'cols' => ['id', 'name']]);
                                    if (($existingCount = count($existingPayments)) > 1){
                                        $found = 0;
                                        foreach($existingPayments as $payment){
                                            if ($payment['name'] === $description){
                                                $found += 1;
                                                $paymentId = $payment['id'];
                                            }
                                        }
                                        if ($found > 1){
                                            Feedback::add("{$this->tr('Severalpaymentsfound')}: $id - {$this->tr('Selectedid')}: $paymentId");
                                            $paymentId = Utl::drillDown($existingPayments, [0, 'id']);
                                        }
                                    }else{
                                        $paymentId = Utl::drillDown($existingPayments, [0, 'id']);
                                    }
                                }
                                if (isset($filter['searchinvoices'])){
                                    $this->searchInvoices(array_merge($where, ['pricewt' => $amount]), $id, $invoicesModel, $invoicesItemsModel, $invoiceId, $invoiceItemId);
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
        $existingPaymentsIds = []; $updatedPaymentsIds = []; $noDateAmountRows = []; $rowNeedsMorePaymentInfo = []; $createdPaymentsIds = []; $createdPaymentsItemsIds = []; $createdInvoicesIds = []; $createdInvoicesItemsIds = [];
        $paymentsColsMapping = ['id' => 'paymentid', 'parentid' => 'customer', 'name' => 'description', 'date' => 'date', 'isexplained' => 'isexplained', 'paymenttype' => 'paymenttype', 'reference' => 'reference', 'slip' => 'slip', 
            'amount' => 'amount', 'category' => 'category', 'organization' => 'organization'];
        $reconciliationColsMapping = array_flip($paymentsColsMapping);
        $paymentsModelCols = array_merge(array_keys($paymentsColsMapping), ['updated']);
        $paymentsReconciliationCols = array_values($paymentsColsMapping);
        $reconciliationToInvoicesMapping = ['customer' => 'parentid', 'amount' => 'pricewt', 'organization' => 'organization'];
        $invoicesReconciliationCols = array_keys($reconciliationToInvoicesMapping);
        foreach($payments as &$payment){
            $id = $payment['id'];
/*
            if (isset($payment['amount'])){
                $payment['amount'] = is_string($payment['amount']) ? $payment['amount'] : number_format($payment['amount'], 2);
            }
*/
            $payment['organization'] = $organization;
            foreach ($paymentsReconciliationCols as $col){
                $$col = Utl::getItem($col, $payment);
            }
            $presentCols = array_keys($payment);
            $presentPaymentReconciliationCols = array_intersect($presentCols, $paymentsReconciliationCols);
            $presentPaymentModelValues = $this->arrayColsMap($payment, $reconciliationColsMapping, $presentPaymentReconciliationCols);
            $whereCols = Utl::getItems(['parentid', 'name', 'organization', 'amount', 'category', 'paymenttype', 'reference', 'slip'], array_filter($presentPaymentModelValues));
            if ($paymentid || $reference || count($whereCols) >= 2){
                $where = $paymentid 
                    ? ['id' => $paymentid] 
                    : array_merge($whereCols, $this->dateFilter($query['startdate'], $query['enddate'], $date));
                $existingModelPayments = $paymentsModel->getAll(['where' => $where, 'cols' => $paymentsModelCols, 'orderBy' => ['date' => 'DESC']]);
                if (($paymentsCount = count($existingModelPayments))){
                    $existingModelPayment = array_filter($existingModelPayments[0]);
                    if ($paymentsCount > 1){
                        Feedback::add("{$this->tr('Severalpaymentsfound')}: $id - {$this->tr('Mostrecentselected')}: {$existingModelPayment['id']}");
                    }
                    if (!$paymentid){
                        $existingPaymentsIds[] =  $paymentid = $existingModelPayment['id'];
                    }
                    if (Utl::extractItem('updated', $existingModelPayment) > Utl::getItem('updated', $payment, '')){//payment was updated since last synchronisation => update reconciliation with payment info
                        $payment = array_merge($payment, $this->arrayColsMap($existingModelPayment, $paymentsColsMapping));
                        $updateModelPayment = true;
                    }else{
                        $existingModelPayment = array_merge($existingModelPayment, array_filter(Utl::getItems(['date', 'name', 'amount', 'isexplained', 'parentid', 'category', 'paymenttype', 'reference', 'slip'], $presentPaymentModelValues)));
                        $payment = array_merge($payment, $this->arrayColsMap($existingModelPayment, $paymentsColsMapping));
                        $updateModelPayment = false;
                    }
                }else{
                    $existingModelPayment = [];
                    $updateModelPayment = false;
                    if ($paymentid){
                        Feedback::add("{$this->tr('paymentidnotfoundforrow')}: $id - {$this->tr('payment')}: $paymentid. {$this->tr('newpaymenttocreate')}");
                    }
                }
                $createInvoice = Utl::getItem('createinvoice', $payment, false, false);
                $invoiceMatch = false;
                if (!Utl::getItem('invoiceid', $payment) && !Utl::getItem('invoiceitemid', $payment) && !empty($existingModelPayment)){
                    $where = []; $presentInvoicesCols = array_intersect(array_keys($payment), $invoicesReconciliationCols);
                    foreach ($presentInvoicesCols as $col){
                        $where[$reconciliationToInvoicesMapping[$col]] = $payment[$col];
                    }
                    $invoiceId = $invoiceItemId = '';
                    if (Utl::getItem('pricewt', $where)){
                        $this->searchInvoices($where, $id, $invoicesModel, $invoicesItemsModel, $invoiceId, $invoiceItemId);
                        $payment['invoiceid'] = $invoiceMatch = $invoiceId; $payment['invoiceitemid'] = $invoiceItemId;
                    }
                }else if (!$createInvoice){
                    $invoiceItemId = Utl::getItem('invoiceitemid', $payment);
                    $invoiceId = Utl::getItem('invoiceid', $payment);
                }
                if($createInvoice){
                    if ($invoiceMatch){
                        if (empty($existingModelPayment) && !$invoiceId = Utl::getItem('invoiceid', $payment)){
                            Feedback::add("{$this->tr('invoicematchesforrow')}: $id - {$this->tr('invoice')}: $invoiceId. {$this->tr('invoicestillcreated')}");
                        }else{
                            Feedback::add("{$this->tr('invoicesetorfoundforrow')}: $id - {$this->tr('invoice')}: $invoiceId. {$this->tr('invoicenotcreated')}");
                            $invoiceItemId = Utl::getItem('invoiceItemId', $payment);
                            $createInvoice = false;
                        }
                    }
                    if ($createInvoice){
                        if (($category = Utl::getItem('category', $payment)) && ($customer = Utl::getItem('customer', $payment)) && ($amount = Utl::getItem('amount', $payment)) && ($date = Utl::getItem('date', $payment))){
                            $vatRate = $categoriesModel->vatRate($organization, $category);
                            $createdInvoicesIds[] = $payment['invoiceid'] = $invoiceId = $invoicesModel->insert(['parentid' => $customer, 'organization' => $organization, 'name' => $description = Utl::getItem('description', $payment),
                                'invoicedate' => $date, 'pricewt' => $amount, 'organization' => $organization], true)['id'];
                            $createdInvoicesItemsIds[] = $payment['invoiceitemid'] = $invoiceItemId = $invoicesItemsModel->insert(['parentid' => $invoiceId, 'name' => $description, 'category' => $category,
                                'vatfree' => $categoriesModel->vatFree($organization, $category),'vatrate' => $vatRate, 'pricewt' => $amount, 'pricewot' => $amount / (1 + $vatRate)], true)['id'];
                            $payment['createinvoice'] = '';
                        }else{
                            $invoiceItemId = '';
                            Feedback::add($this->tr('RowNeedsCategoryCustomerAmountDateInvoiceNotCreated') . ": {$id}");
                        }
                    }
                    $payment['createinvoice'] = '';
                }
                if ($updateModelPayment){
                    if ($paymentsModel->updateOne($existingModelPayment)){
                        $updatedPaymentsIds[] = $paymentid = $existingModelPayment['id'];
                    }
                    if ($invoiceItemId){
                        $paymentItems = $paymentsItemsModel->getAll(['where' => ['parentid' => $paymentid, 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId], 'cols' => ['id']]);
                        if (!($paymentsItemsCount = count($paymentItems))){
                            $createdPaymentsItems[] = $paymentsItemsModel->insert(['parentid' => $paymentid, 'name' => $description, 'amount' => $amount, 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId], true)['id'];
                        }else if ($paymentsItemsCount === 1){
                            if ($paymentsItemsModel->updateOne(array_merge($paymentItem, ['name' => $description, 'amount' => $amount]))){
                                $updatedPaymentsItemsIds[] = $paymentItem['id'];
                            }
                        }else{
                            Feedback::add($this->tr('Severalpaymentsitemsforrownotupdated') . ": {$id}");
                        }
                    }
                }else{
                    if (!$paymentid){
                        if (count(array_filter(Utl::getItems(['date', 'amount', 'category'], $presentPaymentModelValues))) >= 3){
                            $createdPaymentsIds[] = $paymentid = $payment['paymentid'] = $paymentsModel->insert(array_merge($presentPaymentModelValues, ['unassignedamount' => $invoiceItemId ? 0.0 : $amount]), true)['id'];
                        }else{
                            $noDateAmountRows[] = $id;
                        }
                    }
                    if ($invoiceItemId){
                        $paymentItems = $paymentsItemsModel->getAll(['where' => ['parentid' => $paymentid, 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId], 'cols' => ['id']]);
                        if (!($paymentsItemsCount = count($paymentItems))){
                            $createdPaymentsItemsIds[] = $paymentsItemsModel->insert(['parentid' => $paymentid, 'name' => $description, 'amount' => $amount, 'invoiceid' => $invoiceId, 'invoiceitemid' => $invoiceItemId], true)['id'];
                        }else if ($paymentsItemsCount === 1){
                            if ($paymentsItemsModel->updateOne(array_merge($paymentItems[0], ['name' => $description, 'amount' => $amount]))){
                                $updatedPaymentsItemsIds[] = $paymentItems[0]['id'];
                            }
                        }else{
                            Feedback::add($this->tr('Severalpaymentsitemsforrownotupdated') . ": {$id}");
                        }
                    }
                }
                SUtl::addItemIdCols($payment, ['paymentid', 'customer', 'category', 'invoiceid', 'invoiceitemid']);
                $payment['updated'] = date('Y-m-d H:i:s');
            }else{
                $rowNeedsMorePaymentInfo[] = $id;
            }
        }
        if (!empty($existingPaymentsIds)){
            Feedback::add("{$this->tr('Existingpaymentsselected')}: " . implode(', ', $existingPaymentsIds));
        }
        if (!empty($updatedPaymentsIds)){
            Feedback::add("{$this->tr('UpdatedPayments')}: " . implode(', ', $updatedPaymentsIds));
        }
        if (!empty($updatedPaymentsItemsIds)){
            Feedback::add("{$this->tr('UpdatedPaymentsItems')}: " . implode(', ', $updatedPaymentsItemsIds));
        }
        if (!empty($noDateAmountRows)){
            Feedback::add("{$this->tr('NodateAmountRowsPaymentNotCreated')}: " . implode(', ', $noDateAmountRows));
        }
        if (!empty($rowNeedsMorePaymentInfo)){
            Feedback::add("{$this->tr('RowNeedsMorePaymentInfoIgnored')}: " . implode(', ', $rowNeedsMorePaymentInfo));
        }
        if (!empty($createdPaymentsIds)){
            Feedback::add("{$this->tr('CreatedPayments')}: " . implode(', ', $createdPaymentsIds));
        }
        if (!empty($createdPaymentsItemsIds)){
            Feedback::add("{$this->tr('CreatedPaymentsItems')}: " . implode(', ', $createdPaymentsItemsIds));
        }
        if (!empty($createdInvoicesIds)){
            Feedback::add("{$this->tr('CreatedInvoices')}: " . implode(', ', $createdInvoicesIds));
        }
        if (!empty($createdInvoicesItemsIds)){
            Feedback::add("{$this->tr('CreatedInvoicesItems')}: " . implode(', ', $createdInvoicesItemsIds));
        }
        Feedback::add($this->tr('synchrocompleted'));
        return ['data' => ['paymentslog' => $payments]];
    }
    public function searchInvoices($where, $id, $invoicesModel, $invoicesItemsModel, &$invoiceId, &$invoiceItemId){
        $existingInvoices = $invoicesModel->getAll(['where' => $where, 'cols' => ['id'], 'orderBy' => ['invoicedate' =>  'DESC']]);
        $invoiceId = Utl::drillDown($existingInvoices, [0, 'id']);
        if (count($existingInvoices)> 1){
            Feedback::add("{$this->tr('Severalinvoicesfound')}: $id - {$this->tr('Mostrecentselected')}: $invoiceId");
        }
        if ($invoiceId){
            $existingInvoiceItemId = $invoicesItemsModel->getOne(['where' => ['parentid' => $invoiceId, 'pricewt' => $where['pricewt']], 'cols' => ['id']]);
            $invoiceItemId = Utl::getItem('id', $existingInvoiceItemId);
        }
    }
    public function arrayColsMap($array, $mapping, $cols = []){
        $result = [];
        if (empty($cols)){
            $cols = array_keys($mapping);
        }
        foreach($cols as $col){
            if (isset($array[$col])){
                $result[$mapping[$col]] = $array[$col];
            }
        }
        return $result;
    }
    public function dateFilter($startDate, $endDate, $targetDate, $beforeDays=30){
        return [['col' => 'date', 'opr' => '>=', 'values' => (new \DateTime($startDate))->sub(new \DateInterval("P{$beforeDays}D"))->format('Y-m-d')], ['col' => 'date', 'opr' => '<=', 'values' => empty($targetDate) ? $endDate : $targetDate]];
    }
}
?>