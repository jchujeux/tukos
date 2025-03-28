<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Store\Store;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class UpdateTukosConfigIntoUserDb {

    function __construct($parameters){
        $appConfig    = Tfk::$registry->get('appConfig');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        $options = new \Zend_Console_Getopt(
            ['app-s'		=> 'tukos application name (not needed in interactive mode)',
                'db-s'		    => 'tukos application database name (not needed in interactive mode)',
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional)',
                'rootUrl-s'		=> 'https://tukos.site or https://localhost, omit if interactive',
                'forgetuserchanges-s'      => 'override changes to items made in the user database'
            ]);
        $configStore  = new Store(array_merge($appConfig->dataSource, ['dbname' => 'tukosconfig']));
        if ($options->forgetuserchanges && $options->forgetuserchanges === 'YES'){
            $keepUserChanges = false;
        }else{
            $keepUserChanges = true;
            $configStoreOld  = new Store(array_merge($appConfig->dataSource, ['dbname' => 'tukosconfigold']));
        }
        $user = Tfk::$registry->get('user');
        $user->setLockedMode(false);
        try{
            $changedConfigs = ($keepUserChanges
                ? $store->query("SELECT t1.object, t1.id FROM tukosconfig.tukos as t1 LEFT JOIN tukosconfigold.tukos as t2 on (t1.id = t2.id) WHERE (t1.updated > t2.updated OR t2.id IS NULL) and t1.id > 0 and t1.id < 10000")
                : $store->query("SELECT t1.object, t1.id FROM tukosconfig.tukos as t1 WHERE t1.id > 0 and t1.id < 10000"))
            ->fetchAll(\PDO::FETCH_COLUMN|\PDO::FETCH_GROUP);
            $incompatibleObjects = []; 
            $updatedIds = [];
            foreach ($changedConfigs as $object => $ids){
                $objectModel = $objectsStore->objectModel($object);
                $cols = array_merge(array_diff($objectModel->allCols, ['history', 'updator']), ['object']);
                $optionalJoin = $configStore->tableExists($object) ? "NATURAL JOIN $object " : '';
                $configItems = $configStore->query("SELECT * from tukos $optionalJoin WHERE id IN (" . implode(',', $ids) . ")")->fetchAll(\PDO::FETCH_ASSOC);
                if ($keepUserChanges){
                    $configItemsOld = $configStoreOld->tableExists($object) ? Utl::toAssociative($configStoreOld->query("SELECT * from tukos $optionalJoin WHERE id IN (" . implode(',', $ids) . ")")->fetchAll(\PDO::FETCH_ASSOC), 'id') : [];
                }
                foreach ($configItems as $configItem){
                    $id = $configItem['id'];
                    $userItem = $objectModel->getOne(['where' => ['id' => $id], 'cols' => $cols]);
                    if (empty($userItem)){
                        $tempItem = $store->query("SELECT id, object from tukos where id = $id")->fetch(\PDO::FETCH_ASSOC);
                        if (!empty($tempItem)){
                            $incompatibleObjects[$id] = ['id' => $id, 'config' => $configItem['object'], 'user' => $tempItem['object']];
                            continue;
                        }
                    }
                   /*
                     * dans $configItem, remplacer les colonnes qui ont �t� modifi�es par l'utilisateur, C.A.D celles dont la valeur est diff�rente de celle de $itemConfigOld
                     * 
                     */
                    Utl::extractItems(['laststart', 'lastend', 'history', 'updator', 'updated', 'creator', 'created'], $configItem);
                    if ($keepUserChanges){
                        Utl::extractItems(['id', 'laststart', 'lastend', 'updator', 'updated', 'creator', 'created'], $userItem);
                        $colsToKeep = array_diff($userItem, Utl::getItem($id, $configItemsOld, []));
                        if (!empty($colsToKeep)){
                            $configItemOld = Utl::getItem($id, $configItemsOld, [], []);
                            $jsonColsToKeep = array_intersect($objectModel->jsonCols, array_keys($colsToKeep));
                            foreach ($jsonColsToKeep as $col){
                                $colsToKeep[$col] = json_decode($colsToKeep[$col], true);
                                if ($configItemOld[$col]){
                                    $configItemOld[$col] = json_decode($configItemOld[$col], true);
                                }
                            }
                            $jsonColsContentToKeep = Utl::array_diff_assoc_recursive($colsToKeep, $configItemOld);
                            foreach ($jsonColsToKeep as $col){
                                $colsToKeep[$col] = json_encode(Utl::array_merge_recursive_replace(json_decode($configItem[$col], true), $jsonColsContentToKeep[$col]));// we replace the full jsonCol value by the part which the user has changed
                            }
                            $colsToKeepIds[] = $id;
                        }
                    }else{
                        $colsToKeep = [];
                    }
                    $updated = $objectModel->updateOne(empty($colsToKeep) ? $configItem : array_merge($configItem, $colsToKeep));
                    if ($updated !== false){
                        $updatedIds[] = $id;
                    }else{
                        $noChangeIds[] = $id;
                    }
                }
            }
            
            if (!empty($incompatibleObjects)){
                echo Tfk::tr('incompatibleobjects') . ': ';
                var_dump($incompatibleObjects);
                echo '<br>';
            }
            if (!empty($colsToKeepIds)){
                echo Tfk::tr('modifiedconfigitemskept') . ': ' . implode(',', $colsToKeepIds) . '<br>';
            }
            if (!empty($updatedIds)){
                echo Tfk::tr('updatedconfigids') . ': ' . implode(',', $updatedIds) . '<br>';
            }
            if (!empty($noChangeIds)){
                echo Tfk::tr('unchangedconfigids') . ': ' . implode(',', $noChangeIds) . '<br>';
            }
            
        }catch(\Exception $e){
            Tfk::error_message('on', 'an exception occured while running script updateTukosConfigIntoUserDb : ', $e->getMessage());
        }
    }
}
?>
