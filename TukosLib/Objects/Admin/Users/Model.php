<?php
namespace TukosLib\Objects\Admin\Users;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Store\Store;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Cipher;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel{
    protected $rightsOptions = ['SUPERADMIN', 'ADMIN', 'ENDUSER','RESTRICTEDUSER'];
    protected $environmentOptions = ['production', 'development'];
    
    public static $_colsDefinition = [
            'password'      =>  'VARCHAR(255)  DEFAULT NULL',
            'rights'        =>  'VARCHAR(80) DEFAULT NULL',
        'modules'       =>  'VARCHAR(2048) DEFAULT NULL',
        'restrictedmodules'       =>  'VARCHAR(2048) DEFAULT NULL',
        'language'      =>  "VARCHAR(80) DEFAULT NULL",
            'environment'   =>  "VARCHAR(80) DEFAULT NULL",
        "targetdb" => "VARCHAR(50) DEFAULT NULL",
            'tukosorganization' =>  "VARCHAR(80) DEFAULT NULL",
            'dropboxaccesstoken'      =>  'VARCHAR(255)  DEFAULT NULL',
            'dropboxbackofficeaccess' => 'VARCHAR(10) DEFAULT NULL',
            'googletranslationaccesskey'      =>  'VARCHAR(255)  DEFAULT NULL',
            'enableoffline'  => 'VARCHAR(10) DEFAULT NULL',
            'customviewids' =>  'longtext DEFAULT NULL',
            'customcontexts'=>  'longtext DEFAULT NULL',
            'pagecustom'=>  'longtext DEFAULT NULL',
    ];
    public static $_colsIndexes = [];
    
    function __construct($objectName, $translator=null){
        $this->languageOptions = Tfk::$registry->get('appConfig')->languages['supported'];

        parent::__construct($objectName, $translator, 'users', ['parentid' => ['people']], ['modules', 'customviewids', 'customcontexts', 'pagecustom'], self::$_colsDefinition, self::$_colsIndexes, ['rights', 'modules', 'language'], ['custom', 'history']);

        switch ($this->user->rights()){
            case 'ADMIN':
                $this->rightsOptions = ['ADMIN', 'ENDUSER', 'RESTRICTEDUSER'];
                break;
            case 'ENDUSER':
                $this->rightsOptions = ['ENDUSER'];
                break;
            case 'RESTRICTEDUSER':
                $this->rightsOptions = ['RESTRICTEDUSER'];
                break;
        }
    }
    function initialize($init=[]){
        
    	return parent::initialize(array_merge(['rights' => 'ENDUSER', 'targetdb' => Tfk::$registry->get('appConfig')->dataSource['dbname'], 'tukosorganization' => $this->user->tukosOrganization()], $init));
    }    
    public function getOneExtended ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
    	$result = parent::getOneExtended($atts, $jsonColsPaths, $jsonNotFoundValue);
    	if (empty($result) || Utl::getItem('grade', $result) === 'TEMPLATE'){
    	    return $result;
    	}else{
    	    $result['targetdb'] = Tfk::$registry->get('configStore')->getOne(['where' => ['username' => $result['name']], 'table' => 'usersauth', 'cols' => ['targetdb']])['targetdb'];
    	    return $result;
    	}
    }
    public function insertExtended($values, $init=false, $jsonFilter = false){
        if (!$this->user->isAtLeastAdmin() && $values['id'] !== $this->user->id()){
            Feedback::add($this->tr('needAdminRightsToCreateUser'));
            return false;
        }
        if ($values['grade'] === 'TEMPLATE'){
            return parent::insertExtended($values, $init, $jsonFilter);
        }else{
            if (empty($values['name'])){
            	Feedback::add($this->tr('needname'));
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
            	if (empty($configStore->getOne(['where' => ['username' => $userName], 'table' => 'usersauth', 'cols' => ['username']]))){
            	    if (empty($authenticationInfo['password'])){
            	        $authenticationInfo['password'] = MD5(Utl::random_password());//so that private encrytion for the user can take place
            	    }
            	    $configStore->insert($authenticationInfo, ['table' => 'usersauth']);
            	}else{
            	    if (!empty($authenticationInfo['password'])){
            	        Feedback::add($this->tr('userexistsnopassword'));
            	        return false;
            	    }
            	}
            	Utl::extractItem('targetdb', $values);
            }
        	if (empty(Utl::getItem('tukosorganization', $values))){
        	    $values['tukosorganization'] = $this->user->tukosOrganization();
        	}
        	$item =  parent::insertExtended($values, false, $jsonFilter);
        	if (Utl::getItem('dropboxbackofficeaccess', $values) === 'yes'){
        	    $this->updateOne(['custom' => ['dropbox' => [$item['id']]]], ['where' => ['name' => 'tukosBackOffice']]);
        	}
        	return $item;
        }
    }
    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (!$this->user->isAtLeastAdmin() && $newValues['id'] !== $this->user->id()){
            Feedback::add($this->tr('needAdminRightsToCreateUser'));
            return false;
        }
        if ($newValues['grade'] === 'TEMPLATE'){
            return parent::updateOneExtended($newValues, $atts, $insertIfNoOld, $jsonFilter, $init);
        }else{
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
        				unset($authInfo['name']);
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
        			    $targetDb = Tfk::$registry->get('appConfig')->dataSource['dbname'];
        			}
        			$targetStore = new Store(array_merge(Tfk::$registry->get('appConfig')->dataSource, ['dbname' => $targetDb]));
    				if (empty($targetStore->getOne(['table' => SUtl::$tukosTableName, 'where' => ['name' => $newName, 'object' => 'users'], 'cols' => ['name']]))){
    					Feedback::add($this->tr('targetdbdoesnothaveusersitemforusername'));
    					return false;
    				}
        		}
        	    $configStore = Tfk::$registry->get('configStore');
            	if (empty($configStore->getOne(['where' => ['username' => $existingAuthInfo['name']], 'table' => 'usersauth', 'cols' => ['username']]))){
            		/*if (empty($authInfo['username'])){
            			$authInfo['username'] = $existingAuthInfo['name'];
            		}
            		if (empty($authInfo['password'])){
            			$authInfo['password'] = $existingAuthInfo['password'];
            		}
            		$configStore->insert($authInfo, ['table' => 'usersauth']);*/
            	    Feedback::add($this->tr('existingusernotfoundintukosconfig.usersauth'));
            	    return [];
            	}else{
            	    $configStore->update($authInfo, ['table' => 'usersauth', 'where' => ['username' => $existingAuthInfo['name']]]);
            	}
        		if (Utl::extractItem('password', $newValues, false)){
        			Feedback::add($this->tr('passwordupdated'));
        		}
        		if (Utl::extractItem('targetdb', $newValues, false) !== false && $newValues['id'] === $this->user->id()){
        			Feedback::add($this->tr('reauthenticatefornewtargetdb'));
        		}
        		$authUpdate = true;
        	}
        	$result = parent::updateOneExtended($newValues, $atts, $insertIfNoOld, $jsonFilter);
        	if (!$result && $authUpdate){
        		$result = ['id' => $newValues['id']];
        	}else{
        	    $this->processDropbox($newValues);
        	}
        	return $result;
        }
    }
    private function processDropbox($newValues){
        switch (Utl::getItem('dropboxbackofficeaccess', $newValues)){
            case 'yes': 
                $this->updateOne(['custom' => ['dropbox' => [$newValues['id']]]], ['where' => ['name' => 'tukosBackOffice']]); 
                break;
            case 'no': 
                $dropboxIds = $this->getOne(['where' => ['name' => 'tukosBackOffice'], 'cols' => ['custom']], ['custom' => ['dropbox']])['custom'];
                if (($key = array_search($newValues['id'], $dropboxIds)) !== false){
                    $dropboxIds[$key] = '~delete';
                    $this->updateOne(['custom' => ['dropbox' => $dropboxIds]], ['where' => ['name' => 'tukosBackOffice']]);
                }
        }
    }
}
?>
