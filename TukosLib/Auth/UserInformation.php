<?php
namespace TukosLib\Auth;

use TukosLib\Auth\ContextCustomization;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Objects\Directory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Cipher;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class UserInformation{
    use ContextCustomization;
    public function __construct($objectModulesDefaultContextName, $modulesMenuLayout, $tukosCkey){
        $this->objectModules = Tfk::$registry->get('appConfig')->objectModules;// $this->objectModules[] = 'help';
        $this->objectModulesDefaultContextName = $objectModulesDefaultContextName;
        $this->modulesMenuLayout = $modulesMenuLayout;
        $this->tukosCkey = $tukosCkey;
        $this->objectsStore = Tfk::$registry->get('objectsStore');
        $this->lockedMode = true;
        $this->promoteRestricted = false;
    }
    
    public function setUser($where){
        try {
            $tk = SUtl::$tukosTableName;
            $tu = 'users';
            ($userName = $where['name']) === 'tukos' ? $getFunc = 'getOne' : list($getFunc, $where) = ['getAll', ['object' => 'users', ['col' => 'name', 'opr' => 'IN', 'values' => [$userName, 'tukos']]]];
            $usersInfo = SUtl::$store->$getFunc([/*can't use Users\Model here as AbstractModel relies on $this->userInfo */
                'table' => $tu, 'join' => [['inner', $tk,  $tk . '.id = ' . $tu . '.id']],
                'where' => SUtl::transformWhere($where, $tu),
                'cols' => ['tukos.id', 'password', 'rights', 'modules', 'restrictedmodules',  'language', 'environment', 'tukosorganization', 'customviewids', 'customcontexts', 'pagecustom', 'dropboxaccesstoken', 'dropboxbackofficeaccess', 'googletranslationaccesskey', 'enableoffline',
                            'parentid', 'name', 'contextid', 'custom'],
                'union' => Tfk::$registry->get('tukosModel')->parameters['union']
            ]);
            if ($userName === 'tukos'){
                $this->userInfo = $this->tukosInfo = $usersInfo;
            }else{
                if (count($usersInfo) === 2){
                    $this->tukosInfo = $usersInfo[0]['name'] === 'tukos' ? $usersInfo[$key = 0] : $usersInfo[$key = 1];
                    $this->userInfo = $usersInfo[1 - $key];
                    if ($userName === 'sysadmin' && Tfk::$registry->get('appConfig')->dataSource['dbname'] === 'tukosconfig'){
                        $this->setLockedMode(false);
                    }
                }else{
                    Feedback::add(Tfk::tr('Username') . ': ' . $userName);
                    return false;
                }
            }
            $this->unallowedModules  = ($this->isSuperAdmin() || $this->userInfo['modules'] === null) ? [] : json_decode($this->userInfo['modules'], true);
            $this->restrictedModules = ($this->isSuperAdmin() || $this->userInfo['restrictedmodules'] === null) ? [] : json_decode($this->userInfo['restrictedmodules'], true);
            if ($userName === 'tukosBackOffice'){
                $this->allowedModules = ['contexts', 'backoffice', 'blog', 'tukos'];
            }else{
                $this->allowedModules =  array_values(array_diff(array_merge($this->objectModules, ['navigation']), $this->unallowedModules));
                if ($this->isSuperAdmin()){
                    SUtl::$store->addMissingColsIfNeeded (SUtl::$tukosModel->colsDescription, $tk);
                }
            }            
            $this->ckey = Utl::getItem('password', $this->userInfo, $this->tukosCkey, $this->tukosCkey);
            $translatorsStore = Tfk::$registry->get('translatorsStore');
            if ($language = Utl::getItem('language', $this->userInfo)){
                $translatorsStore->setLanguage($language);
            }
            $this->language = $translatorsStore->getLanguageCol();
            Tfk::setEnvironment($this->userInfo['environment']);
            Tfk::setTranslator($this->language);
            $this->contextModel = $this->objectsStore->objectModel('contexts', null);/* here and not in __construct  as else creates infinite loop recursion */
            Tfk::$registry->isRestrictedUser = $this->userInfo['rights'] === 'RESTRICTEDUSER';
            return true;
        }catch(\Exception $e){
            //Tfk::debug_mode('log', Tfk::tr('errorgettinguserinformation'), $e->getMessage());
            Feedback::add(Tfk::tr('errorgettinguserinformation') . ': ' . $e->getMessage());
            return false;
        }
    }

    public function id(){
        return $this->userInfo['id'];
    }
    public function peopleId(){
        return $this->userInfo['parentid'];
    }
    public function username(){
        return $this->userInfo['name'];
    }
    public function getModel($object){
        $objectModel = $object . "Model";
        return isset($this->$objectModel) ? $this->$objectModel : $this->$objectModel = $this->objectsStore->objectModel($object);
    }
    public function peoplefirstAndLastNameOrUserName($userId){
        $peopleId = $this->getModel('users')->getOne(['where' => ['id' => $userId], 'cols' => ['parentid']])['parentid'];
        if(empty($peopleId)){
            return $this->username();
        }else{
            $people = $this->getModel('people')->getOne(['where' => ['id' => $peopleId], 'cols' => ['name', 'firstname']]);
            return "{$people['firstname']} {$people['name']}";
        }
    }
    public function contextid(){
        return $this->userInfo['contextid'];
    }
    public function rights(){
        return $this->userInfo['rights'];
    }
    public function language(){
        return  $this->language;
    }
    public function tukosOrganization(){
        return  Utl::getItem('tukosorganization', $this->userInfo, '');
    }
    public function dropboxAccessToken(){
        return $this->decrypt(Utl::getItem('dropboxaccesstoken', $this->userInfo, ''), 'private');
    }
    public function googleTranslationAccessKey(){
        return $this->decrypt(Utl::getItem('googletranslationaccesskey', $this->userInfo, ''), 'private');
    }
    public function dateFormat(){
        $formats = ['en_us' => 'Y-m-d', 'fr_fr' => 'd-m-Y', 'es_es' => 'd-m-Y'];
        return isset($formats[$this->language]) ? $formats[$this->language] : 'Y-m-d';
    }
    public function isAtLeastAdmin(){
        return ($this->rights() === 'ADMIN' || $this->rights() === 'SUPERADMIN');
    }
    public function isSuperAdmin(){
        return $this->rights() === 'SUPERADMIN';
    }
    public function isRestrictedUser(){
        return $this->rights() === 'RESTRICTEDUSER';
    }
    public function allowedModules(){
        return $this->allowedModules;
    }
    public function isOfflineEnabled(){
        return $this->userInfo['enableoffline'] === 'yes';
    }
    public function allowedNativeObjects(){
        return array_intersect($this->allowedModules, directory::getNativeObjs());
    }
    public function isPageAllowed($module){
        return in_array($module, $this->allowedModules);
    }
    public function isAllowed($module, $query){
        if ($module === 'users' && isset($query['id']) && $query['id'] === $this->id()){
            return true;
        }else{
            return in_array($module, $this->allowedModules) || $this->isRestricted($module);
        }
    }
    public function isRestricted($module){
        return in_array($module, $this->restrictedModules);
    }
    public function unallowedModules(){
        return $this->unallowedModules;
    }
    public function isUnallowed($module){
        return in_array($module, $this->unallowedModules);
    }

    public function contextTreeAtts($tr){
        $result = ['storeArgs' => ['data'  => $this->contextModel->storeData], 
                   'root'  => $this->contextModel->getRootId(),
        ];
        foreach ($result['storeArgs']['data'] as &$data){
            $data['name'] = $tr($data['name']);
        }
        return $result;
    }
    public function getRootId(){
        return $this->contextModel->getRootId();
    }

    public function encrypt($string, $mode, $deterministic = false){
        return Cipher::encrypt($string, ($mode === 'private' ? $this->ckey : $this->tukosCkey), $deterministic);
    }
    public function decrypt($string, $mode, $deterministic = false){
        return Cipher::decrypt($string, ($mode === 'private' ? $this->ckey : $this->tukosCkey), $deterministic);
    }

    public function modulesMenuLayout(){
        return $this->modulesMenuLayout;
    }
    function fullColName($colName, $tableName = ''){
        return ($tableName === '' ? '' : $tableName . '.') . $colName;
    }
    function filterPrivate($where, $objectName=''){
        switch ($this->rights()){
            case 'SUPERADMIN':
                break;
            default:
                $permissions = (empty($objectName) || !$this->isRestricted($objectName) || $this->promoteRestricted) ?  ['RL', 'RO', 'UL', 'PU'] : ['UL','PU'];
                $where[] = [
                    [
                        [
                            ['col' => $this->fullColName('permission'), 'opr' => 'IN', 'values' => $permissions],
                            ['col' => ($aclName = $this->fullColName('acl')), 'opr' => 'NOT RLIKE', 'values' => $this->id() . '","permission":"0"']
                            
                        ],
                        [
                            ['col' => $this->fullColName('updator'), 'opr' => 'LIKE', 'values' => $this->id()],
                            [['col' => $this->fullColName('creator'), 'opr' => 'LIKE', 'values' => $this->id()],['col' => $aclName, 'opr' => 'NOT RLIKE', 'values' => $this->id() . '","permission":"0"']],
                            'or' => true],
                        ['col' => $this->fullColName('id'), 'opr' => '=', 'values' => $this->id(), 'or', true],
                        'or' => true
                    ],
                    ['col' => $aclName, 'opr' => 'RLIKE', 'values' => $this->id() . '","permission":"[123]"', 'or' => true]
                ];
                break;
        }
        return $where;  
    }
    function filterReadonly($where, $objectName = ''){
        switch($this->rights()){
            case "SUPERADMIN":
                break;
            default:
                if (!empty($objectName) && $this->isRestricted($objectName)){
                    $where[] = [
                        ['col' => $this->fullColName('updator'), 'opr' => '=', 'values' => $this->id(), 'or' => true],
                        ['col' => $this->fullColName('acl'), 'opr' => 'RLIKE', 'values' => $this->id(). '","permission":"[23]"', 'or' => true]
                    ];
                }else{
                    $where[] = [
                        [['col' => $this->fullColName('permission'), 'opr' => '=', 'values' => 'PU'], ['col' => ($this->fullColName('acl')), 'opr' => 'NOT RLIKE', 'values' => $this->id() . '","permission":"[01]"']],
                        ['col' => $this->fullColName('updator'), 'opr' => '=', 'values' => $this->id(), 'or' => true],
                        ['col' => $this->fullColName('acl'), 'opr' => 'RLIKE', 'values' => $this->id(). '","permission":"[23]"', 'or' => true]
                    ];
                }
                break;
        }
        return $where;
    }
    function filterContext($where, $objectName=''){
        $col = $this->fullColName('contextid');
        if (isset($where['contextpathid'])){
            $contextPathId = Utl::extractItem('contextpathid', $where);
        }else if(!empty($objectName)){
            $contextPathId = $this->getContextId($objectName);
        }
        if (!empty($contextPathId) && !empty($fullPathIds = $this->contextModel->getFullPathIds($contextPathId))){
	        $where[] = [['col' => $col, 'opr' => 'IN', 'values' => $fullPathIds],
	                    ['col' => $col, 'opr' => 'IS NULL', 'values' => null, 'or' => true],// rows with null contextid are considered visible by all users
	        ];
        }
        return $where;  
    }
    function filter($where, $objectName=''){
        if (isset($where['id']) && $where['id'] === $this->id()){
        	return $where;//so that a user can always access his own item
        }else{
    		return $this->filterContext($this->filterPrivate($where, $objectName), $objectName);
        }
    }
    public function aclRights ($userId, $acl){
        if (!empty($acl)){
            $rights = false;
            if (is_string($acl)){
                $acl = json_decode($acl, true);
            }
            foreach ($acl as $rule){
                if (Utl::getItem('userid', $rule) == $userId){
                    $rights = Utl::getItem('permission', $rule, false);
                    break;
                }
            }
            return $rights;
        }else{
            return false;
        }
    }
    public function setLockedMode($trueFalse){
        $this->lockedMode = $trueFalse;
    }
    public function getLockedMode(){
        return $this->lockedMode;
    }
    public function hasUpdateRights($item, $objectName, $newItem=[]){
        if ($this->lockedMode && in_array(Utl::getItem('permission', $item) , ['PL', 'RL', 'UL']) && !empty($newItem) && Utl::getItem('permission', $newItem, $item['permission']) === $item['permission']){
            return false;
        }
        $aclRights = $this->aclRights($userId = $this->id(), Utl::getItem('acl', $item));
        return $this->isSuperAdmin() || $aclRights > 1 || ($aclRights === false && ($item['updator'] === $userId || ($item['permission'] === 'PU' && (!$this->isRestricted($objectName)|| $item['creator'] === $userId)) || $item['id'] === $userId));
    }
    public function hasDeleteRights($item, $objectName=''){
        if ($this->lockedMode && (in_array($item['permission'] , ['PL', 'RL', 'UL']))){
            return false;
        }
        $aclRights = $this->aclRights($userId = $this->id(), Utl::getItem('acl', $item));// || ($this->rights() === 'RESTRICTEDUSER' && (empty($objectName) || $this->isRestricted($objectName)))
        return $this->isSuperAdmin() /*|| $item['updator'] === $userId */|| ($item['permission'] === 'PU' && ($aclRights === false || $aclRights > 2)) || $aclRights > 2 || ($item['creator'] === $userId && !$aclRights);
        return $this->isSuperAdmin() || $aclRights > 2 || ($aclRights === false && ($item['updator'] === $userId || ($item['permission'] === 'PU' && (!$this->isRestricted($objectName)|| $item['creator'] === $userId))));
    }
    public function getDropboxUserAccessToken($userId){
        if (is_string($this->userInfo['custom'])){
            $this->userInfo['custom'] = json_decode($this->userInfo['custom'], true);
        }
        if (in_array($userId, $this->userInfo['custom']['dropbox'])){
            $usersModel = $this->getModel('users');//$this->objectsStore->objectModel('users');
            $values = $usersModel->getOne(['where' => ['id' => $userId], 'cols' => ['password', 'dropboxaccesstoken']]);
            if (empty($values['dropboxaccesstoken'])){
                Feedback::add('nodropboxaccesstokenforuser' . ': ' . $userId);
                return false;
            }else{
                return Cipher::decrypt($values['dropboxaccesstoken'], $values['password']);
            }
        }else{
            Feedback::add('noaccesstouserdropbox');
            return false;
        }
    }
    private function customViewIds($tukosOrUser){
        $tukosOrUser === 'tukos' ? list($viewIds, $info) = ['tukosViewIds', 'tukosInfo'] : list($viewIds, $info) = ['customViewIds', 'userInfo'];
        if (!property_exists($this, $viewIds)){
            if (!empty($this->$info['customviewids'])){
                $this->$viewIds = json_decode($this->$info['customviewids'], true);
            }else{
                $this->$viewIds = [];
            }
        }
        return $this->$viewIds;
    }
    public function tukosOrUserViewId($objectName, $view, $tukosOrUser, $paneMode = 'Tab'){
        return Utl::drillDown($this->customViewIds($tukosOrUser), [strtolower($objectName), strtolower($view), strtolower($paneMode)]);
    }
    public function customViewId($objectName, $view, $paneMode = 'Tab', $tukosOrUser = 'user'){
        return $this->tukosOrUserViewId($objectName, $view, $tukosOrUser, $paneMode);
    }
    public function getCustomView($objectName, $view, $paneMode = 'Tab', $keys = [], $notFoundValue=[]){
        $customViewId = $this->customViewId($objectName, $view, $paneMode);
        $tukosViewId = $customViewId === ($tukosViewId = $this->userName() === 'tukos' ? 0 : $this->customViewId($objectName, $view, $paneMode, 'tukos')) ? 0 : $tukosViewId;
        
        $getParams = empty($customViewId) 
            ? (empty($tukosViewId) ? [] : list($getFunc, $where) = ['getOne', ['id' => $tukosViewId]])
            : (empty($tukosViewId) ? list($getFunc, $where) = ['getOne', ['id' => $customViewId]] : list($getFunc, $where) = ['getAll', [['col' => 'id', 'opr' => 'IN', 'values' => [$tukosViewId, $customViewId]]]]);
        if (empty($getParams)){
            return [];
        }else{
            $result = $this->objectsStore->objectModel('customviews')->$getFunc(['where' => $where, 'cols' => ['id', 'customization']], ['customization' => $keys], $notFoundValue);
            if ($getFunc === 'getAll'){
                if (count($result) === 2){
                    //$tukosCustom = Utl::drillDown(json_decode(Utl::getItem('customization', $result[$key = ($result[0]['id'] === $tukosViewId ? 0 : 1)], '[]', '[]'), true), $keys, []);
                    //$userCustom = Utl::drillDown(json_decode(Utl::getItem('customization', $result[1 - $key], '[]', '[]'), true), $keys, []);
                    $tukosCustom = Utl::getItem('customization', $result[$key = ($result[0]['id'] === $tukosViewId ? 0 : 1)], [], []);
                    $userCustom = Utl::getItem('customization', $result[1 - $key], [], []);
                    SUtl::addIdCol($customViewId); SUtl::addIdCol($tukosViewId);
                    return Utl::array_merge_recursive_replace($tukosCustom, $userCustom);
                }else if(count($result === 1)){
                    Feedback::add(Tfk::tr('CustomViewNotFound') . ' - id: ' . $result[0]['id'] === $tukosViewId ? $customViewId : $tukosViewId);
                    SUtl::addIdCol($result[0]['id']);
                    return Utl::getItem('customization', $result[0], [], [])
                    ;
                }else{
                    Feedback::add(Tfk::tr('CustomViewNotFound') . " - id: $customViewId, $tukosViewId");
                    return [];
                    
                }
            }else{
                if (empty($result)){
                    Feedback::add(Tfk::tr('CustomViewNotFound') . " - id: {$where['id']}");
                    return [];
                }else{
                    SUtl::addIdCol($where['id']);
                    return Utl::getItem('customization', $result, [], []);
                }
            }
        }
    }
    function setCustomViewId($objectName, $view, $paneMode, $customViewId, $tukosOrUser = 'user'){
        $objectName = strtolower($objectName);
        $view = strtolower($view);
        $paneMode = strtolower($paneMode);
        $this->objectsStore->objectModel('users')->updateOne(
            ['customviewids' => [$objectName => [$view => [$paneMode => $customViewId]]]],
            ['where' => $tukosOrUser === 'user' ? ['id' => $this->id()] : ['name' => 'tukos']],
            true, true
            );
        $viewIds = $tukosOrUser === 'tukos' ? 'tukosViewIds' : 'customViewIds';
        $this->$viewIds = Utl::array_merge_recursive_replace($this->customViewIds($tukosOrUser), [$objectName => [$view => [$paneMode => $customViewId]]]);
    }
    public function updateCustomView($objectName, $view, $paneMode, $newValues, $tukosOrUser = 'user'){
        $paneMode = strtolower($paneMode); $view = strtolower($view);
        $customViewId = $this->customViewId($objectName, $view, $paneMode, $tukosOrUser);
        if (empty($customViewId)){
            Feedback::add('updateCustomView: inserting a new view not supposed to happen');
        }else{
            $this->objectsStore->objectModel('customviews')->updateOne(
                ['vobject' => $objectName, 'view' => strtolower($view), 'panemode' => strtolower($paneMode), 'customization' => $newValues], 
                ['where' => ['id' => $customViewId]], 
                true, false
            );
        }
    }
    public function pageCustomization($tukosOrUser = 'combined'){
        if (!property_exists($this, 'pageCustomization')){
            $this->pageUserCustomization = json_decode(Utl::getItem('pagecustom', $this->userInfo, '[]', '[]'), true);
            if ($this->userName() !== 'tukos'){
                $this->pageTukosCustomization = json_decode(Utl::getItem('pagecustom', $this->tukosInfo, '[]', '[]'), true);
                $this->pageCustomization = Utl::array_merge_recursive_replace($this->pageTukosCustomization, $this->pageUserCustomization);
            }else{
                $this->pageCustomization = $this->pageTukosCustomization = $this->pageUserCustomization;
            }
            $this->pageCustomization = array_merge(['fieldsMaxSize' => 100000], $this->pageCustomization);
        }
        switch($tukosOrUser){
            case 'combined': return $this->pageCustomization; break;
            case 'tukos'   : return $this->pageTukosCustomization; break;
            case 'user'    : return $this->pageUserCustomization;
        }
    }
    public function updatePageCustom($pageCustom, $tukosOrUser){
        $usersModel = $this->getModel('users');//$this->objectsStore->objectModel('users');
        $where = ($tukosOrUser === 'user') ? ['id' => $this->id()] : ['name' => 'tukos'];
        foreach ($pageCustom as &$item){
            if ($item === ''){
                $item = '~delete';
            }
        }
        $usersModel->updateOne(['pagecustom' => $pageCustom], ['where' => $where], false, true);
        return $usersModel->getOne(['where' => $where, 'cols' => ['pagecustom']], ['pagecustom' => []]);
/*        if ($tukosOrUser === 'user'){
            $this->objectsStore->objectModel('users')->updateOne(['pagecustom' => $pageCustom], ['where' => ['id' => $this->id()]], false, true);
        }else{
            $this->objectsStore->objectModel('users')->updateOne(['pagecustom' => $pageCustom], ['where' => ['name' => 'tukos']], false, true);
        }
        Feedback::add(Tfk::tr('serveractiondone'));
        return [];*/
    }
    public function fieldsMaxSize(){
        return intval(Utl::getItem('fieldsMaxSize', $this->pageCustomization()));
    }
    public function historyMaxItems(){
        return IntVal(Utl::getItem('historyMaxItems', $this->pageCustomization()));
    }
    public function getCustomTukosUrl($request, $query){
        if (!isset(Tfk::$registry->route['object']) && ($customTukosUrl = Utl::getItem(Tfk::$registry->appName, Utl::toAssociative(Utl::getItem('defaultTukosUrls', $this->pageCustomization(), []), 'app')))){
            if ($customUrlObject = Utl::getItem('object', $customTukosUrl)){
                $request = array_merge($request, ['object' => $customUrlObject, 'view' => ucfirst(Utl::getItem('view', $customTukosUrl, 'overview'))]);
            }
            if ($customUrlQuery = Utl::getItem('query', $customTukosUrl)){
                $conditions = explode(',', $customUrlQuery); $customQuery = [];
                foreach($conditions as $condition){
                    list($key, $value) = explode('=', $condition);
                    $customQuery[$key] = $value;
                }
                $query = array_merge($query, $customQuery);
            }
        }
        return [$request, $query];
    }
    public function showTooltips(){
        return Utl::getItem('showTooltips', $this->pageCustomization(), 'YES', 'YES');
    }
}
?>
