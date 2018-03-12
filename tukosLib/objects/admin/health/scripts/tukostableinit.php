<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class TukosTableInit {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        $tukosModel   = Tfk::$registry->get('tukosModel');
        try{
            $options = new Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'parentTable-s'=> 'parent script table (optional, required if parentid is not a users)',
            ]);
            //$store->emptyTable('tukos');// requires drop privileges for tukosAppAdmin
            $objectsToConsider = array_intersect(Directory::getObjs(), $store->hook->fetchTableList());
            foreach ($objectsToConsider as $objectName){
                try {
                    $objectModel = $objectsStore->objectModel($objectName);
                    $objectItems = $store->getAll([
                        'cols' => array_merge(array_intersect($tukosModel->sharedObjectCols, $store->tableCols($objectName)), $objectModel->extendedNameCols),
                        'table' => $objectName
                    ]);
                    $colsToExclude = array_diff($objectModel->extendedNameCols, ['name']);
                    foreach ($objectItems as $item){
                        $item['table'] = $objectName;
                        if ($objectModel->extendedNameCols != ['name']){
                            $item['extendedname'] = Utl::concat(Utl::getItems($objectModel->extendedNameCols, $item),' ', 25);
                            Utl::extractItems($colsToExclude, $item);
                        }
                        $store->insert($item, ['table' => 'tukos']);
                    }
                }catch(\Exception $e){
                    Tfk::error_message('on', ' Exception in TukosTableInit: ', $e->getMessage());
                }
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
