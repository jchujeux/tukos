<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
//use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class MigrateInvoicesItems {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        $tukosModel   = Tfk::$registry->get('tukosModel');
        try{
            $options = new \Zend_Console_Getopt([
                'app-s'		=> 'tukos application name (mandatory if run from the command line, not needed in interactive mode)',
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $objectsToConsider = array_merge(array_intersect(Directory::getNativeObjs(), $store->tableList()), ['tukos']);
            
            $invoicesObject = $objectsStore->objectModel('bustrackinvoices');
            $invoicesItemsObject = $objectsStore->objectModel('bustrackinvoicesitems');
            
            $invoices = $invoicesObject->getAll(['where' => [['col' => 'id', 'opr' => '>', 'value' => 0]], 'cols' => ['id', 'items']]);
            foreach($invoices as $invoice){
                if (!empty($items = Utl::getItem('items', $invoice))){
                    $parentId = $invoice['id'];
                    $items = json_decode($items, true);
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
