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
    
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
    	$result = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
    	$result['targetdb'] = Tfk::$registry->get('configStore')->getOne(['where' => ['username' => $result['name']], 'table' => 'users', 'cols' => ['targetdb']])['targetdb'];
    	return $result;
    }

    public function insertExtended($values, $init=false, $jsonFilter = false){
        if (empty($values['name']) || empty($values['password'])){
        	Feedback::add($this->tr('neednamepassword'));
        	return false;
        }else if (!empty($this->getOne(['where' => ['name' => $values['name']], 'cols' => ['name']]))){
        	Feedback::add($this->tr('useralreadyexists'));
        	return false;
        }else{
        	if (empty($values['targetdb'])){
        		$values['targetdb'] = Tfk::$registry->get('appConfig')->dataSource['dbname'];
        	}
        	$authenticationInfo = Utl::getItems(['name', 'password', 'targetdb'], $values);
        	$authenticationInfo['username'] = $userName = Utl::extractItem('name', $authenticationInfo);
        	$configStore = Tfk::$registry->get('configStore');
        	if (empty($configStore->getOne(['where' => ['username' => $userName], 'table' => 'users', 'cols' => ['username']]))){
        		$configStore->insert($authenticationInfo, ['table' => 'users']);
        	}
        	Utl::extractItem('targetdb', $values);
        	return parent::insertExtended($values, false, $jsonFilter);
        }
    }

    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false){
    	$authInfo = Utl::getItems(['name', 'password', 'targetdb'], $newValues);
    	$authUpdate = false;
    	if (!empty($authInfo)){
    		$existingAuthInfo = $this->getOne(['where' => ['id' => $newValues['id']], 'cols' => ['name', 'password']]);
    		if (isset($authInfo['name'])){
    			$newName = Utl::getItem('name', $authInfo);
    		    if (empty($newName)){
    				Feedback::add($this->tr('blanknamenotallowed'));
    				return false;
    			}else if ($newName !== $existingAuthInfo['name']){
    				$authInfo['username'] = $newName;
    			}
    		}else{
    			$newName = $existingAuthInfo['name'];
    		}
    	    if (isset($authInfo['password'])&& empty($authInfo['password'])){
    			Feedback::add($this->tr('emptypasswordnotallowed'));
    			return false;
    		}
    		if (isset($authInfo['targetdb'])){
    			$targetDb = Utl::getItem('targetdb', $authInfo);
    			if(empty($targetDb)){
    				Feedback::add($this->tr('emptytargetdbnotallowed'));
    				return false;
    			}else{
    				$targetStore = new Store(array_merge(Tfk::$registry->get('appConfig')->dataSource, ['dbname' => $targetDb]));
    				if (empty($targetStore->getOne(['table' => SUtl::$tukosTableName, 'where' => ['name' => $newName, 'object' => 'users'], 'cols' => ['name']]))){
    					Feedback::add($this->tr('targetdbdoesnothaveusersitemforusername'));
    					return false;
    				}
    			}
    		}
    	    $configStore = Tfk::$registry->get('configStore');
        	if (empty($configStore->getOne(['where' => ['username' => $newName], 'table' => 'users', 'cols' => ['username']]))){
        	    if (empty($authInfo['targetdb'])){
        			$authInfo['targetdb'] = Tfk::$registry->get('appConfig')->dataSource['dbname'];
        		}
        		if (empty($authInfo['username'])){
        			$authInfo['username'] = $existingAuthInfo['name'];
        		}
        		if (empty($authInfo['password'])){
        			$authInfo['password'] = $existingAuthInfo['password'];
        		}
        		$configStore->insert($authInfo, ['table' => 'users']);
        	}else{
        		$configStore->update($authInfo, ['table' => 'users', 'where' => ['username' => $newName]]);
        	}
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
