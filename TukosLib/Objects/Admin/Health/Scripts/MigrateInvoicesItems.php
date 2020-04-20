<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class MigrateInvoicesItems {
    function __construct($parameters){ 
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $invoicesObject = $objectsStore->objectModel('bustrackinvoices');
            $invoicesItemsObject = $objectsStore->objectModel('bustrackinvoicesitems');
            $invoices = $invoicesObject->getAll(['where' => [['col' => 'id', 'opr' => '>', 'value' => 0]], 'cols' => ['id', 'items']]);
            echo 'number of invoices found: ' . count($invoices) . '<br>';
            foreach($invoices as $invoice){
                echo 'handling invoice: ' . $invoice['id'] . '<br>';
                if (!empty($items = Utl::getItem('items', $invoice))){
                    $parentId = $invoice['id'];
                    $items = json_decode($items, true);
                    echo 'number of items found: ' . count($items) . '<br>';
                    foreach ($items as $item){
                        $item['parentid'] = $parentId;
                        unset($item['rowId']);
                        $invoicesItemsObject->insert($item);
                        echo "created item for invoice {$invoice['id']}";
                    }
                }
            }
            echo "We are done!";
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
