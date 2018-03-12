<?php
/**
 *
 * class for the notes tukos object, allowing to attach a textual note to tukos objects
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Zend\Console\Getopt;
use Ifsnop\Mysqldump;
use TukosLib\TukosFramework as Tfk;

class FullBackup {

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'parentTable-s'=> 'parent script table (optional, required if parentid is not a users)',
            ]);
            $this->tableObj = $objectsStore->objectModel('healthtables');
            $stmt = $store->tableStatus();
            $source = $appConfig->dataSource;
            while ($tableStatus = $stmt->fetch()){
                $tableName      = $tableStatus['Name'];
                if (in_array('updated', $store->tableCols($tableName))){
                    $lastUpdated    = $store->getOne(['table' => $tableName, 'orderBy' => ['updated DESC'], 'cols' => ['updated']]);
                    $lastUpdateTime = (!empty($lastUpdated) ? $lastUpdated['updated'] : false);
                    
                    $backupInfo     = $this->tableObj->getOne(['where'   => ['backuptype' => 'FULL', 'name' => $tableName],
                                                                'orderBy' => ['datehealthcheck DESC'],
                                                                'cols'    => ['id', 'name', 'datehealthcheck', 'backuptype', 'backupfilename']
                                                               ]);
                    if (!empty($backupInfo) && $backupInfo['datehealthcheck'] >= $lastUpdateTime && file_exists(Tfk::tukosUsersFiles . 'backups/' .  $backupInfo['backupfilename'])){
                        continue;
                    }else{
                        $dateBackup = date('Y-m-d H:i:s');
                        $backupFileName = $tableName . str_replace(':', '-', str_replace(' ', '_', $dateBackup)) . '.sql';
                        $dump = new Mysqldump($source['dbname'], $source['admin'], $source['pass'], $source['host'], $source['datastore'], ['include-tables' => [$tableName]]);
                        $dump->start(Tfk::tukosUsersFiles . 'backups/' . $backupFileName);
                        echo 'backed up table: ' . $tableName . ' into file: ' . $backupFileName;
                        $extendedParentId = [
                            'id'    => ($options->parentid    ? $options->parentid    : $user->id()),
                            'object' => ($options->parentTable ? $options->parentTable : 'users'),
                        ];
                        $this->tableObj->insertExtended([
                                'name'              => $tableName, 
                                'parentid'          => $extendedParentId, 
                                'datehealthcheck'   => $dateBackup,
                                'backuptype'        => 'FULL', 
                                'backupfilename'    => $backupFileName
                             ],
                             true
                        ); 
                    }
                }
            }
        }catch(Getopt_exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in FullBackup: ', $e->getUsageMessage());
        }
    }
}
?>
