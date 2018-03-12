<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class TukosTableQuery {

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
            try {
                $objectName = 'users';
                $objectModel = $objectsStore->objectModel($objectName);
                $objectItem = $store->getOne([
                    'where' => ['id' => 7, 'rights' => 'SUPERADMIN'],
                    'cols'  => ['id', 'name', 'parentid', 'rights'], 
                    'table' => $objectName
                ]);
                Tfk::log_message('on', ' object item: ', $objectItem);

                $newObjectItem = $store->getOne([
                    'where' => ['tukos.id' => 7, 'users.rights' => 'SUPERADMIN'],
                    'cols'  => ['tukos.id', 'tukos.name', 'tukos.parentid', 'users.rights'],
                    'table' => 'users',
                    'join'  => [['inner', 'tukos', 'tukos.id = users.id']]
                ]);
                Tfk::log_message('on', ' new object item: ', $newObjectItem);
                $newObjectItem = $store->getOne([
                    'where' => ['tukos.id' => 7],
                    'cols'  => ['tukos.id', 'tukos.name', 'tukos.parentid', 'ifnull(t2.extendedname,t2.name)', 't2.object'],
                    'table' => 'users',
                    'join'  => [['inner', 'tukos', 'tukos.id = users.id'], ['inner', 'tukos as t2', 'tukos.parentid = t2.id']]
                ]);
                Tfk::log_message('on', ' new extended object item: ', $newObjectItem);
                $newObjectItem = $store->getAll([
                    //'where' => ['tukos.object' => 'users'],
                    'cols'  => ['tukos.id', 'tukos.name',  
                                "concat('{id:',tukos.parentid,',name:',ifnull(t2.extendedname,t2.name),',object:',t2.object,'}') as parentid",
                                "concat('{id:',tukos.contextid,',name:',ifnull(t3.extendedname,t3.name),',object:',t3.object,'}') as contextid",
                    ],
                    'table' => 'tukos',
                    'join'  => [/*['inner', 'tukos', 'tukos.id = users.id'], */['inner', 'tukos as t2', 'tukos.parentid = t2.id'], ['inner', 'tukos as t3', 'tukos.contextid = t3.id']]
                ]);
                Tfk::log_message('on', ' new extended object item with two idcols & concatenation: ', $newObjectItem);
                $newObjectItem = $store->getAll([
                    //'where' => ['tukos.object' => 'users'],
                    'cols'  => ['tukos.id', 'tukos.name', 'tukos.parentid', 'tukos.contextid',
                    ],
                    'table' => 'tukos',
                    //'join'  => [/*['inner', 'tukos', 'tukos.id = users.id'], */['inner', 'tukos as t2', 'tukos.parentid = t2.id'], ['inner', 'tukos as t3', 'tukos.contextid = t3.id']]
                ]);
                Tfk::log_message('on', ' new extended object item with two idcols & concatenation: ', $newObjectItem);
                $newObjectItem = $store->getAll([
                    'where' => ['tukos.object' => 'users'],
                    'cols'  => ['tukos.id', 'tukos.name', 'tukos.parentid', 'tukos.contextid',
                    ],
                    'table' => 'tukos',
                    'join'  => [['inner', 'tukos', 'tukos.id = users.id'], ['inner', 'tukos as t2', 'tukos.parentid = t2.id'], ['inner', 'tukos as t3', 'tukos.contextid = t3.id']]
                ]);
                Tfk::log_message('on', ' new extended object item with two idcols & concatenation: ', $newObjectItem);
                $newObjectItems = $store->getAll([
                    'where' => ['users.rights' => 'SUPERADMIN'],
                    'cols'  => ['tukos.id', 'tukos.name', 'tukos.parentid', 'users.rights'],
                    'table' => 'users',
                    'join'  => [['inner', 'tukos', 'tukos.id = users.id']],
                    'orderBy'=> ['tukos.id DESC']
                ]);
                Tfk::log_message('on', ' new object item: ', $newObjectItems);
                $storeProfiles = Tfk::$registry->get('store')->getProfiles();
                $storeProfilesOutput = HUtl::page('Tukos Profiler Results',  HUtl::table($storeProfiles, []));
                file_put_contents('/tukosstoreprofiles.html', $storeProfilesOutput);
            }catch(\Exception $e){
                Tfk::error_message('on', ' Exception in TukosTableQuery: ', $e->getMessage());
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
