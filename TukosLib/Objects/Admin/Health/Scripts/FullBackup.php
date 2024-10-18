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
        $options = new \Zend_Console_Getopt(
            ['app-s'		=> 'tukos application name (not needed in interactive mode)',
                'db-s'		    => 'tukos application database name (not needed in interactive mode)',
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional)',
                'rootUrl-s'		=> 'https://tukos.site or https://localhost, omit if interactive',
                'backupstokeep-s'      => 'number of past backups to keep'
            ]);
        $store        = Tfk::$registry->get('store');
        $lastUpdated = $store->getOne(['table' => 'tukos', 'cols' => ['updated'], 'where' => [], 'orderBy' => ['updated' => 'DESC']])['updated'];
        $lastBackupDate = $store->getOne(['table' => 'options', 'where' => ['name' => 'lastbackupdate'], 'cols' => ['value']]);
        $appConfig    = Tfk::$registry->get('appConfig');
        $source = $appConfig->dataSource;
        if (!$lastBackupDate || $lastBackupDate['value'] < $lastUpdated){
            $dateBackup = date('Y-m-d H:i:s');
            $backupFileName = $source['dbname'] . str_replace(':', '-', str_replace(' ', '_', $dateBackup)) . '.sql.bz2';
            $dump = new Mysqldump("mysql:host={$source['host']};dbname={$source['dbname']}", $source['admin'], $source['pass'], ['compress' => 'Bzip2', 'default-character-set' => 'utf8mb4']);
            $dump->start(Tfk::$tukosTmpDir . $backupFileName);
            echo "Backed-up database: {$source['dbname']} into file $backupFileName";
            if ($lastBackupDate){
                $store->update(['name' => 'lastBackupDate', 'value' => $dateBackup], ['table' => 'options', 'where' => ['name' => 'lastBackupDate']]);
            }else{
                $store->insert(['name' => 'lastBackupDate', 'value' => $dateBackup], ['table' => 'options']);
            }
            $oldBackupFiles = array_values(array_filter(scanDir(Tfk::$tukosTmpDir, SCANDIR_SORT_DESCENDING), function ($fileName) use ($source){
                return preg_match('/^' . $source['dbname'] . '.*\.sql\.bz2/', $fileName);
            }));
            if (($arraySize = count($oldBackupFiles)) > $options->backupstokeep){
                for ($i = $options->backupstokeep; $i <$arraySize; $i++){
                    unlink(Tfk::$tukosTmpDir . $oldBackupFiles[$i]);
                    $deletedFiles[] =  $oldBackupFiles[$i];
                }
                echo "<br>Deleted the following old backup files: " . implode(', ', $deletedFiles);
            }
        }else{
            echo "No change identified for : {$source['dbname']} since last backup on {$lastBackupDate['value']}";            
        }
        
    }
}
?>
