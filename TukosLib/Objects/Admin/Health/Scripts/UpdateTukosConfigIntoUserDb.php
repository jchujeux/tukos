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
        $configStore  = new Store(array_merge($appConfig->dataSource, ['dbname' => 'tukosconfig']));
        $configStoreOld  = new Store(array_merge($appConfig->dataSource, ['dbname' => 'tukosconfigold']));
        try{
            $changedConfigs = $store->query("SELECT t1.object, t1.id FROM tukosconfig.tukos as t1 LEFT JOIN tukosconfigold.tukos as t2 on (t1.id = t2.id) WHERE (t1.updated <> t2.updated OR t2.id IS NULL) and t1.id > 0 and t1.id < 10000")
                ->fetchAll(\PDO::FETCH_COLUMN|\PDO::FETCH_GROUP);
            $incompatibleObjects = []; 
            $updatedIds = [];
            foreach ($changedConfigs as $object => $ids){
                $objectModel = $objectsStore->objectModel($object);
                $cols = array_merge($objectModel->allCols, ['object']);
                $optionalJoin = $configStore->tableExists($object) ? "NATURAL JOIN $object " : '';
                $configItems = $configStore->query("SELECT * from tukos $optionalJoin WHERE id IN (" . implode(',', $ids) . ")")->fetchAll(\PDO::FETCH_ASSOC);
                $configItemsOld = Utl::toAssociative($configStoreOld->query("SELECT * from tukos $optionalJoin WHERE id IN (" . implode(',', $ids) . ")")->fetchAll(\PDO::FETCH_ASSOC), 'id');
                foreach ($configItems as $configItem){
                    $id = $configItem['id'];
                    $userItem = $objectModel->getOne(['where' => ['id' => $id], 'cols' => $cols, 'union' => false]);
                    if (empty($userItem)){
                        $tempItem = $store->query("SELECT id, object from tukos where id = $id")->fetch(\PDO::FETCH_ASSOC);
                        if (!empty($tempItem)){
                            $incompatibleObjects[$id] = ['config' => $configItem['object'], 'user' => $tempItem['object']];
                            continue;
                        }
                    }
                   /*
                     * dans $configItem, remplacer les colonnes qui ont été modifiées par l'utilisateur, C.A.D celles dont la valeur est différente de celle de $itemConfigOld
                     * 
                     */
                    $itemToCopy = array_merge($configItem, array_diff($userItem, Utl::getItem($id, $configItemsOld, [])));
                    unset($itemToCopy['updator']);
                    unset($itemToCopy['history']);
                    $objectModel->updateOne($itemToCopy, ['union' => false]);
                    $updatedIds[] = $id;
                }
            }
            if (!empty($incompatibleObjects)){
                echo 'incompatible objects (not copied): ' . var_dump($incompatibleObjects);
            }else{
                echo 'the following ids were updated: ' . implode(',', $updatedIds);
            }
            
        }catch(\Exception $e){
            Tfk::error_message('on', 'an exception occured while runnins script FromMergeToCopyTukosConfigIntoUserDb : ', $e->getMessage());
        }
    }
}
?>
