<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\Store\Store;
use TukosLib\TukosFramework as Tfk;

class TukosSports {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $sourceStore =    new Store(['datastore' => 'mysql', 'host'   => '127.0.0.1', 'admin'   => 'tukosAppAdmin', 'pass'   => MD5('XZK@w0kw' . getenv('MYSQL_ENV_VAR')), 'dbname'   => 'tukos20']);;

        $objectsStore = Tfk::$registry->get('objectsStore');
        $tukosModel   = Tfk::$registry->get('tukosModel');
        try{
            $options = new Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'parentTable-s'=> 'parent script table (optional, required if parentid is not a users)',
            ]);
            try {
                $objectName = 'translations';
                $objectModel = $objectsStore->objectModel($objectName);
                $sourceItems = $sourceStore->getAll([
                    'table' => 'tukos', 
                    'where' => ['tukos.object' => 'translations', 0 => ['col' => 'tukos.id' , 'opr' => '>', 'values' => 0], 'translations.setname' => 'sports'],
                    'cols'  => ['*'],
                    'table' => 'translations',
                    'join'  => [['inner', 'tukos', 'tukos.id = translations.id']]
                ]);
                foreach ($sourceItems as $item){
                    $item['contextid'] = 4;
                    $item['configstatus'] = 'sports';
                    $objectModel->insert($item);
                }
                Tfk::log_message('on', ' source items: ', $sourceItems);

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
