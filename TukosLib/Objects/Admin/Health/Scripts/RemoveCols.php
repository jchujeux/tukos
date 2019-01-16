<?php
/**
 *
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;

use Zend\Console\Getopt;

use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class RemoveCols {

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
            $colsToRemove = ['idcolstables', 'childrencount','childrentables'];
            foreach ($tablesToConsider as $tableName){
                $removedCols = [];
                foreach ($colsToRemove as $col){
                    try{
                        $alterStmt = $store->hook->query("ALTER TABLE `" . $tableName . "` DROP `" . $col . "`");
                        $removedCols[] = $col;
                    } catch (\PDOException $e){
                        echo '<br>Could not remove column ' . $col . ' in table ' . $tableName . '. Error message: '. $e->getMessage();
                    }
                }
                if (!empty($removedCols)){
                    $extendedParentId = [
                        'id'    => ($options->parentid    ? $options->parentid    : $user->id()),
                        'object' => ($options->parentTable ? $options->parentTable : 'users'),
                    ];
                    $objValue = ['name'         => $tableName, 
                                 'parentid'     => $extendedParentId,
                              'datehealthcheck' => date('Y-m-d H:i:s'),
                                 'comments'     => 'The following col(s) were removed: ' . implode(',',$removedCols),
                                ];
                    echo '<br>Table ' . $tableName . ' - ' . $objValue['comments'];
                    $tableObj->insertExtended($objValue, true);
                }
            }
        }catch(Getopt_exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command aguments in RemoveCols: ', $e->getUsageMessage());
        }
    }
}
?>
