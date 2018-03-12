<?php
namespace TukosLib\Objects\Admin\Users;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Store\Store;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel{
    protected $rightsOptions = ['SUPERADMIN', 'ADMIN', 'ENDUSER'];
    protected $environmentOptions = ['production', 'development'];
    
    public static $colsDefinition = [
            'password'      =>  'VARCHAR(255)  DEFAULT NULL',
            'rights'        =>  'VARCHAR(80) DEFAULT NULL',
            'modules'       =>  'VARCHAR(2048) DEFAULT NULL',
            'language'      =>  "VARCHAR(80) DEFAULT NULL",
            'environment'   =>  "VARCHAR(80) DEFAULT NULL",
            //'targetdb'   =>  "VARCHAR(50) DEFAULT NULL",
    		'customviewids' =>  'longtext DEFAULT NULL',
            'customcontexts'=>  'longtext DEFAULT NULL',
            'pagecustom'=>  'longtext DEFAULT NULL',
    ];
    
    function __construct($objectName, $translator=null){
        $this->languageOptions = Tfk::$registry->get('appConfig')->languages['supported'];

        parent::__construct($objectName, $translator, 'users', ['parentid' => ['people']], ['modules', 'customviewids', 'customcontexts', 'pagecustom'], self::$colsDefinition, '', ['rights', 'modules', 'language']);

        switch ($this->user->rights()){
            case 'SUPERADMIN': 
                $this->rightsOptions = ['ADMIN', 'ENDUSER', 'SUPERADMIN'];
                break;
            case 'ADMIN':
                $this->rightsOptions = ['ADMIN', 'ENDUSER'];
                break;
            case 'ENDUSER':
                $this->rightsOptions = ['ENDUSER'];
                break;
        }
    }
    function initialize($init=[]){
        
    	return parent::initialize(array_merge(['rights' => 'ENDUSER', 'targetdb' => Tfk::$registry->get('appConfig')->dataSource['dbname']], $init));
    }    
    public function hasUpdateRights($item){
        return $item['id'] === $this->user->id() || parent::hasUpdateRights($item);
    }
    
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
    	$result = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
    	$result['targetdb'] = $this->store->dbName;
    	return $result;
    }

    public function insertExtended($values, $init=false, $jsonFilter = false){
        if (empty($values['name']) || empty($values['password']) || empty($values['targetdb'])){
        	Feedback::add($this->tr('neednamepasswordtargetdb'));
        	return false;
        }else if (!empty($this->getOne(['where' => ['name' => $values['name']], 'cols' => ['name']]))){
        	Feedback::add($this->tr('useralreadyexists'));
        	return false;
        }else{
        	$authenticationInfo = Utl::getItems(['name', 'password', 'targetdb'], $values);
        	$authenticationInfo['username'] = $userName = Utl::extractItem('name', $authenticationInfo);
        	$configStore = Tfk::$registry->get('configStore');
        	if (empty($configStore->getOne(['where' => ['username' => $userName], 'table' => 'users', 'cols' => ['username']]))){
        		$configStore->insert($authenticationInfo, ['table' => 'users']);
        	}
        	Utl::extractItem('targetdb', $values);
        	return $this->insert($values, false, $jsonFilter);
        }
    }

    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false){
    	$authInfo = Utl::getItems(['name', 'password', 'targetdb'], $newValues);
    	$authUpdate = false;
    	if (!empty($authInfo)){
    		$existingName = $this->getOne(['where' => ['id' => $newValues['id']], 'cols' => ['name']])['name'];
    		if (isset($authInfo['name'])){
    			$newName = Utl::extractItem('name', $authInfo);
    		    if (empty($newName)){
    				Feedback::add($this->tr('blanknamenotallowed'));
    				return false;
    			}else if ($newName !== $existingName){
    				$authInfo['username'] = $newName;
    			}
    		}
    	    if (isset($authInfo['password'])&& empty($authInfo['password'])){
    			Feedback::add($this->tr('emptypasswordnotallowed'));
    			return false;
    		}
    		if (isset($authInfo['targetdb'])){
    			if(empty($targetDb = $authInfo['targetdb'])){
    				Feedback::add($this->tr('emptytargetdbnotallowed'));
    				return false;
    			}else{
    				$targetStore = new Store(array_merge(Tfk::$registry->get('appConfig')->dataSource, ['dbname' => $targetDb]));
    				if (empty($targetStore->getOne(['table' => SUtl::$tukosTableName, 'where' => ['name' => $existingName, 'object' => 'users'], 'cols' => ['name']]))){
    					Feedback::add($this->tr('targetdbdoesnothaveusersitemforusername'));
    					return false;
    				}
    			}
    		}
    		Tfk::$registry->get('configStore')->update($authInfo, ['table' => 'users', 'where' => ['username' => $existingName]]);
    		if (Utl::extractItem('password', $newValues, false)){
    			Feedback::add($this->tr('passwordupdated'));
    		}
    		if (Utl::extractItem('targetdb', $newValues, false)){
    			Feedback::add($this->tr('reauthenticatefornewtargetdb'));
    		}
    		$authUpdate = true;
    	}
    	$result = parent::updateOneExtended($newValues, $atts, $insertIfNoOld, $jsonFilter);
    	if (!$result && $authUpdate){
    		$result = ['id' => $newValues['id']];
    	}
    	return $result;
    }
    
    public function delete ($where, $item = []){
    	$username = $this->getOne(['where' => $where, 'cols' => ['name']])['name'];
    	Tfk::$registry->get('configStore')->delete(['table' => 'users', 'where' => ['username' => $username]]);
    	parent::delete($where, $item);
    }
}
?>
