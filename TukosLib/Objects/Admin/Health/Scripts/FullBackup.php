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
        $source = $appConfig->dataSource;
        $dateBackup = date('Y-m-d H:i:s');
        $backupFileName = $source['dbname'] . str_replace(':', '-', str_replace(' ', '_', $dateBackup)) . '.sql.bz2';
        $dump = new Mysqldump("mysql:host={$source['host']};dbname={$source['dbname']}", $source['admin'], $source['pass'], ['compress' => 'Bzip2', 'default-character-set' => 'utf8mb4']);
        $dump->start(Tfk::$tukosTmpDir . $backupFileName);
        echo "Backed-up database: {$source['dbname']} into file $backupFileName";
    }
}
?>
