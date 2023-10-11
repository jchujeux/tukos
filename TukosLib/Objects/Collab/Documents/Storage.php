<?php
/**
 *
 * class for the storage of tukos files
 */
namespace TukosLib\Objects\Collab\Documents;

use TukosLib\Utils\Utilities as Utl;

use TukosLib\Store\Store;
use TukosLib\TukosFramework as Tfk;

trait Storage {


    protected $_nextFileIdTableName = 'nextFileidTable';
    protected $_nextFileIdTableColsDefinition = array(
     'nextid'   =>  'mediumint(8) unsigned auto_increment PRIMARY KEY',
     'updated'  =>  "timestamp",
     );

    protected $_filesTableName = 'filescontent';
    protected $_filesTableColsDefinition = array(
     'id'       =>  'mediumint(8) unsigned auto_increment PRIMARY KEY',
     'fileid'   =>  "mediumint(8) unsigned NOT NULL default '0'",
     'content'  =>  'longblob NOT NULL',
     );

    function constructStorage() {
        
        if (!isset($this->filesStore)){
            $appConfig = Tfk::$registry->get('appConfig');
            
            $this->filesStore = new Store($appConfig->dataSource . 'files');
    
            if (!$this->filesStore->tableExists($this->_filesTableName)){
                $this->filesStore->createTable($this->_nextFileIdTableName, $this->_nextFileIdTableColsDefinition);
                $this->filesStore->insert(['nextid' => 0, 'updated' => date('Y-m-d H:i:s')], ['table' => $this->_nextFileIdTableName]);
                $this->filesStore->createTable($this->_filesTableName, $this->_filesTableColsDefinition, [['fileid']]);
            }
        }
    }
    
    public function nextFileId($increment = true){
        $this->constructStorage();
        $stmt = $this->filesStore->pdo->query('LOCK TABLES ' . $this->_nextFileIdTableName . ' WRITE');
        $nextId =  $this->filesStore->getValue(['table' => $this->_nextFileIdTableName, 'cols' => ['nextid']]);
        if($increment){ $this->filesStore->update(['nextid' => $nextId+1, 'updated' => date('Y-m-d H:i:s')], ['table' => $this->_nextFileIdTableName, 'where' => ['nextid' => $nextId]]);}
        $stmt = $this->filesStore->pdo->query('UNLOCK TABLES');
    return $nextId;
    }
        

    function insertFile($filePath, $fileId){
        $this->constructStorage();
        $fp = fopen($filePath, "rb");
        $cols = ['fileid' => $fileId];
        while (!feof($fp)) {
            $cols['content'] = fread($fp, 65535);
            $this->filesStore->insert($cols, ['table' => $this->_filesTableName]);
        }    
        fclose($fp);
    }

    function retrieveFile($filePath, $fileId){
        $this->constructStorage();
        $fileids = array_column($this->filesStore->getAll(['table' => $this->_filesTableName, 'where' => ['fileid' => $fileId], 'cols' => ['id']]), 'id');
        $fp = fopen($filePath, "rb");
        foreach ($fileids as $id){
            $bind = ['id' => $node['id']];
            fwrite($fp, $this->filesStore->getOne(['table' => $this->_filesTableName, 'where' => ['id' => $id], 'orderBy' => ['id ASC'], 'cols' => ['content']])['content']);
        }
        fclose($fp);
    }
    function echoFile($fileId){
        $this->constructStorage();
        $fileids = array_column($this->filesStore->getAll(['table' => $this->_filesTableName, 'where' => ['fileid' => $fileId], 'cols' => ['id']]), 'id');
        foreach ($fileids as $id){
            echo $this->filesStore->getOne(['table' => $this->_filesTableName, 'where' => ['id' => $id], 'orderBy' => ['id ASC'], 'cols' => ['content']])['content'];
        }
    }

    function deleteFile($fileId){
        $this->constructStorage();
        return $this->filesStore->delete(['where' => ['fileid' => $fileId]]);
    }
}
?>
