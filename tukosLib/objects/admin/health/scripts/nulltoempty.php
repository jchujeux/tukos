<?php
/**
 *
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;

use Zend\Console\Getopt;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class NullToEmpty {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');

        try{
            $options = new Getopt(
                ['class=s'          => 'this class name',
                 'parentid-s'       => 'parent id (optional, default is user->id())',
                 'parentTable-s'    => 'parent  table (optional, required if parentid is not a users)',
                ]);

            $tableObj = $objectsStore->objectModel('healthtables');
            $tablesToConsider = array_intersect(Directory::getObjs(), $store->hook->fetchTableList());
            foreach ($tablesToConsider as $tableName){
                $alterStmt = $store->hook->query("ALTER TABLE `" . $tableName . "` CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 NULL DEFAULT ''");
                $setToEmptyStmt = $store->hook->query("UPDATE `". $tableName . "` SET `name`='' WHERE `name` is null");
                
                $extendedParentId = [
                    'id'    => ($options->parentid    ? $options->parentid    : $user->id()),
                    'object' => ($options->parentTable ? $options->parentTable : 'users'),
                ];
                $objValue = ['name'         => $tableName, 
                             'parentid'     => $extendedParentId,
                          'datehealthcheck' => date('Y-m-d H:i:s'),
                             'comments'     => 'NullToEmpty - rows affected: ' . $setToEmptyStmt->rowCount(),
                            ];
                $tableObj->insertExtended($objValue, true);
            }
        }catch(Getopt_exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command aguments in NullToBlank: ', $e->getUsageMessage());
        }
    }
}
?>
