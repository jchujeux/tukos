<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Store\Store;
use TukosLib\TukosFramework as Tfk;

class FromMergeToCopyTukosConfigIntoUserDb {
    
    function __construct($parameters){
        $appConfig    = Tfk::$registry->get('appConfig');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        $configStore  = new Store(array_merge($appConfig->dataSource, ['dbname' => 'tukosconfig']));
        try{
            $configObjects = $configStore->query("SELECT object FROM tukos WHERE id > 0 and id < 10000 GROUP BY object")->fetchAll(\PDO::FETCH_COLUMN, 0);
            $incompatibleObjects = [];
            foreach ($configObjects as $object){
                $objectModel = $objectsStore->objectModel($object);
                $cols = array_merge($objectModel->allCols, ['object']);
                $optionalJoin = $configStore->tableExists($object) ? "NATURAL JOIN $object " : '';
                $configItems = $configStore->query("SELECT * from tukos $optionalJoin WHERE object = '$object' AND id < 10000")->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($configItems as $item){
                    $userItem = $objectModel->getOne(['where' => ['id' => $item['id']], 'cols' => $cols, 'union' => false]);
                    if (empty($userItem)){
                        $tempItem = $store->query("SELECT id, object from tukos where id = {$item['id']}")->fetch(\PDO::FETCH_ASSOC);
                        if (!empty($tempItem)){
                            $incompatibleObjects[$item['id']] = ['config' => $item['object'], 'user' => $tempItem['object']];
                            continue;
                        }
                    }
                    $itemToCopy = array_merge($item, array_filter($userItem));
                    unset($itemToCopy['updator']);
                    unset($itemToCopy['history']);
                    $objectModel->updateOne($itemToCopy, ['union' => false]);
                }
            }
            echo 'incompatible objects (not copied): ' . var_dump($incompatibleObjects);
            
        }catch(\Exception $e){
            Tfk::error_message('on', 'an exception occured while runnins script FromMergeToCopyTukosConfigIntoUserDb : ', $e->getMessage());
        }
    }
}
?>
