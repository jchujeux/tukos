<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class TablesSharedColsRemoval {

    function __construct($parameters){ 
        $store        = Tfk::$registry->get('store');
        try{
            $options = new Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'parentTable-s'=> 'parent script table (optional, required if parentid is not a users)',
            ]);
            //$objectsToConsider = array_intersect(Directory::getObjs(), $store->tableList()());
            $objectsToConsider = ['tasks'];//no worksheet
                                    //'dashboards', //no optional at all
            foreach ($objectsToConsider as $objectName){
                try {
                    $result = $store->query(
                        'ALTER TABLE ' . $objectName . 
                        ' DROP COLUMN parentid, DROP COLUMN name, DROP COLUMN contextid, DROP COLUMN comments, DROP COLUMN created, DROP COLUMN updated, ' .
                        ' DROP COLUMN creator, DROP COLUMN updator, DROP COLUMN permission/*, DROP COLUMN worksheet*/, DROP COLUMN custom, DROP COLUMN history'
                    );
                    echo 'table ' . $objectName . ' cleaned!';
                }catch(\Exception $e){
                    Tfk::error_message('on', ' Exception in TablesSharedColsRemoval: ', $e->getMessage());
                }
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
