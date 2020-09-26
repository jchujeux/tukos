<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Ifsnop\Mysqldump;
use TukosLib\TukosFramework as Tfk;

class FullBackup {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new \Zend_Console_Getopt(
                ['app-s'		=> 'tukos application name (not needed in interactive mode)',
                 'db-s'		    => 'tukos application database name (not needed in interactive mode)',
                 'class=s'      => 'this class name',
                 'parentid-s'   => 'parent id (optional)'
                ]);
            $source = $appConfig->dataSource;
            $dateBackup = date('Y-m-d H:i:s');
            $backupFileName = $source['dbname'] . str_replace(':', '-', str_replace(' ', '_', $dateBackup)) . '.sql.bz2';
            $dump = new Mysqldump("mysql:host={$source['host']};dbname={$source['dbname']}", $source['admin'], $source['pass'], ['compress' => 'Bzip2']);
            $dump->start(Tfk::$tukosTmpDir . $backupFileName);
            echo "Backed-up database: {$source['dbname']} into file $backupFileName";
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in FullBackup: ', $e->getUsageMessage());
        }
    }
}
?>
