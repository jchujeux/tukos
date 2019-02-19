<?php
/**
 *
 * Provides user properties to the tukos environment
 *
 */
namespace TukosLib\Auth;

use TukosLib\Objects\Admin\Users\CustomViews\UserCustomization;
use TukosLib\Auth\ContextCustomization;
use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Objects\Directory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Cipher;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class UserInformation{
    use ContextCustomization;
    public function __construct($objectModulesDefaultContextName, $modulesMenuLayout, $tukosCkey){
        $this->objectModules = array_keys($objectModulesDefaultContextName);
        $this->objectModulesDefaultContextName = $objectModulesDefaultContextName;
        $this->modulesMenuLayout = $modulesMenuLayout;
        $this->tukosCkey = $tukosCkey;
        $this->objectsStore = Tfk::$registry->get('objectsStore');
    }
    
    public function setUser($where){
        try {
            $tk = SUtl::$tukosTableName;
            $tu = 'users';
            $this->userInfo = SUtl::$store->getOne([/*can't use Users\Model here as AbstractModel relies on $this->userInfo */
                'table' => $tu, 'join' => [['inner', $tk,  $tk . '.id = ' . $tu . '.id']],
                'where' => SUtl::transformWhere($where, $tu),
                //'cols'  => SUtl::transformCols(['id', 'parentid', 'name', 'password', 'rights', 'modules', 'contextid', 'language', 'environment', 'tukosorganization', 'customviewids', 'customcontexts', 'pagecustom'], $tu)
                'cols'  => ['*']
            ]);
            if (empty($this->userInfo)){
                return false;
            }
            $this->unallowedModules  = ($this->rights() === 'SUPERADMIN' || $this->userInfo['modules'] === null) ? [] : json_decode($this->userInfo['modules'], true);
            if ($where['name'] === 'tukosBackOffice'){
                $this->allowedModules = ['contexts', 'backoffice'];
            }else{
                $this->allowedModules =  array_diff($this->objectModules, $this->unallowedModules);
                if ($where['name'] !== 'tukosscheduler' && $this->isSuperAdmin()){
                    SUtl::$store->addMissingColsIfNeeded (SUtl::$tukosModel->colsDescription, $tk);
                }
            }            
            $this->ckey = $this->userInfo['password'];
            $translatorsStore = Tfk::$registry->get('translatorsStore');
            if (isset($this->userInfo['language'])){
                $translatorsStore->setLanguage($this->userInfo['language']);
            }
            $this->language = $translatorsStore->getLanguageCol();
            Tfk::setEnvironment($this->userInfo['environment']);
            Tfk::setTranslator($this->language);
            $this->contextModel = $this->objectsStore->objectModel('contexts', null);/* here and not in __construct  as else creates infinite loop recursion */
            $this->pageCustomization = array_merge(['fieldsMaxSize' => 100000], empty($pageCustom = $this->userInfo['pagecustom']) ? [] : json_decode($pageCustom, true));
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
    public function dateFormat(){
        $formats = ['en_us' => 'Y-m-d', 'fr_fr' => 'd-m-Y', 'es_es' => 'd-m-Y'];
        return isset($formats[$this->language]) ? $formats[$this->language] : 'Y-m-d';
    }
    public function isAdmin(){
        return ($this->rights() === 'ADMIN' || $this->rights() === 'SUPERADMIN');
    }
    public function isSuperAdmin(){
        return $this->rights() === 'SUPERADMIN';
    }
    public function allowedModules(){
        return $this->allowedModules;
    }
    public function allowedNativeObjects(){
        return array_intersect($this->allowedModules, directory::getNativeObjs());
    }
    public function isAllowed($module, $query){
        $module = strtolower($module);
        if ($module === 'users' && isset($query['id']) && $query['id'] === $this->id()){
            return true;
        }else{
            return in_array($module, $this->allowedModules);
        }
    }
    public function unallowedModules(){
        return $this->unallowedModules;
    }
    public function isUnallowed($module){
        return in_array(strtolower($module), $this->unallowedModules);
    }

    public function contextTreeAtts($tr){
        $result = ['storeArgs' => ['data'  => $this->contextModel->storeData], 
                   'root'  => call_user_func($tr, $this->contextModel->rootId),
                   //'paths' => [$this->contextModel->ancestors]
        ];
        foreach ($result['storeArgs']['data'] as $key => $data){
            $result['storeArgs']['data'][$key]['name'] = call_user_func($tr, $data['name']);
        }
        return $result;
    }

    public function encrypt($string, $mode){
        return Cipher::encrypt($string, ($mode === 'private' ? $this->ckey : $this->tukosCkey));
    }
    public function decrypt($string, $mode){
        return Cipher::decrypt($string, ($mode === 'private' ? $this->ckey : $this->tukosCkey));
    }

    public function modulesMenuLayout(){
        return $this->modulesMenuLayout;
    }
      
   /*
    * Flag to determine whether a record can be modified by current user. 
    */
    public function canEdit($permission, $updator){

        switch ($this->rights()){
            case 'SUPERADMIN':
                return true;
                break;
            case 'ADMIN':
            case 'ENDUSER' :
                if (($permission === 'RO' || $permission === 'PR') && $this->id() !== $updator){
                    return false;
                }else{
                    return true;
                }
                break;
        }
    }

   /*
    * Add condition to the where clause for $store->getXXX so as 
    * not to retrieve private values if not being superadmin and not being its updator
    */
    function filterPrivate($where, $tableName=''){
        switch ($this->rights()){
            case 'SUPERADMIN':
                break;
            case 'ADMIN':
            case 'ENDUSER' :
                $permissionCol = ($tableName === '' ? '' : $tableName . '.') . 'permission';
                $updatorCol = ($tableName === '' ? '' : $tableName . '.') . 'updator';
                $where[] = [['col' => $permissionCol, 'opr' => 'NOT LIKE', 'values' => 'PR'],
                            ['col' => $updatorCol   , 'opr' => 'LIKE', 'values' => $this->id(), 'or' => true],
                           ];
                break;
        }
        return $where;  
    }
    function filterReadOnly($where, $tableName = ''){
        if ($this->rights()!== "SUPERADMIN"){
            $permissionCol = ($tableName === '' ? '' : $tableName . '.') . 'permission';
            $updatorCol = ($tableName === '' ? '' : $tableName . '.') . 'updator';
            $where[] =[['col' => $permissionCol, 'opr' => '=', 'values' => 'PU'], ['col' => $updatorCol, 'opr' => '=', 'values' => $this->id(), 'or' => true]];
        }
        return $where;
    }
   /*
    * Add condition to the where clause for $store->getXXX so as 
    * not to retrieve values not in current user context as well as values with negative ids (considered deleted)
    */
    function filterContext($where, $objectName='', $tableName=''){// $tableName is needed in queries involving joins as contextid may then appear in different tables
        $col = ($tableName === '' ? '' : $tableName . '.') . 'contextid';
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

    function filter($where, $objectName='', $tableName=''){
        if (isset($where['id']) && $where['id'] === $this->id()){
        	return $where;//so that a user can always access his own item
        }else{
    		return $this->filterContext($this->filterPrivate($where, $tableName), $objectName, $tableName);
        }
    }

    public function hasUpdateRights($item){
        return $this->rights() === "SUPERADMIN" || $item['updator'] === $this->id() || $item['permission'] === 'PU' || $item['id'] === $this->id();
    }
    private function customViewIds(){
        if (!property_exists($this, 'customViewIds')){
            if (!empty($this->userInfo['customviewids'])){
                $this->customViewIds = json_decode($this->userInfo['customviewids'], true);
            }else{
                $this->customViewIds = [];
            }
            $this->customViewsNotFound = [];
        }
        return $this->customViewIds;
    }
    public function customViewId($objectName, $view, $paneMode = 'Tab'){
        $customViewIds = $this->customViewIds();
        return Utl::drillDown($customViewIds, [$objectName, strtolower($view), strtolower($paneMode)]);
    }
    
    public function getCustomView($objectName, $view, $paneMode = 'Tab', $keys = [], $notFoundValue=[]){
        $customViewId = $this->customViewId($objectName, $view, $paneMode);
        if (empty($customViewId)){
                return [];
        }else{
            $result = $this->objectsStore->objectModel('customviews')->getOne(
                ['where' => ['id' => $customViewId], 'cols' => ['customization']], ['customization' => $keys], $notFoundValue
            );
            if (empty($result)){
                if (!in_array($customViewId, $this->customViewsNotFound)){
                    $this->customViewsNotFound[] = $customViewId;
                    Feedback::add(Tfk::tr('CustomViewNotFound' . ' - id: ' . $customViewId));
                }
                $customViewId = null;
                return [];
            }else{
                SUtl::addIdCol($customViewId);
                return is_null($result['customization']) ? [] : $result['customization'];
            }
        }
    }
    function setCustomViewId($objectName, $view, $paneMode, $customViewId){
        $view = strtolower($view);
        $paneMode = strtolower($paneMode);
        $this->objectsStore->objectModel('users')->updateOne(
            ['customviewids' => [$objectName => [$view => [$paneMode => $customViewId]]]], 
            ['where' => ['id' => $this->id()]], 
            true, true
        );
        $customViewIds = $this->customViewIds();/* to make sure $this->customViewIds is initialized*/
        $this->customViewIds = Utl::array_merge_recursive_replace($this->customViewIds, [$objectName => [$view => [$paneMode => $customViewId]]]);
    }
    public function updateCustomView($objectName, $view, $paneMode, $newValues){
        $customViewId = $this->customViewId($objectName, $view, $paneMode);
        if (empty($customViewId)){
            $result = $this->objectsStore->objectModel('customviews')->insert(
                ['name' => 'new', 'vobject' => $objectName, 'view' => strtolower($view), 'panemode' => strtolower($paneMode), 'customization' => $newValues], 
                true, true
            );
            $this->setCustomViewId($objectName, $view, $paneMode, $result['id']);
            return ['customviewid' => $result['id']];
        }else{
            $result = $this->objectsStore->objectModel('customviews')->updateOne(
                ['vobject' => $objectName, 'view' => strtolower($view), 'panemode' => strtolower($paneMode), 'customization' => $newValues], 
                ['where' => ['id' => $customViewId]], 
                true, true
            );
            return [];
        }
    }
    public function pageCustomization(){
        return $this->pageCustomization;//empty($this->userInfo['pagecustom']) ? [] : json_decode($this->userInfo['pagecustom'], true);
    }
    public function updateUserInfo($pageCustom){
        //$this->userInfo['pagecustom'] = json_encode(empty($this->userInfo['pagecustom']) ? $pageCustom : array_merge(json_decode($this->userInfo['pagecustom'], true), $pageCustom));
        $this->pageCustomization = array_merge($this->pageCustomization, $pageCustom);
        $this->objectsStore->objectModel('users')->updateOne(['pagecustom' => json_encode($this->pageCustomization)], ['where' => ['id' => $this->id()]]);
        Feedback::add(Tfk::tr('serveractiondone'));
        return [];
    }
    public function fieldsMaxSize(){
        return intval(Utl::getItem('fieldsMaxSize', $this->pageCustomization));
    }
    public function historyMaxItems(){
        return IntVal(Utl::getItem('historyMaxItems', $this->pageCustomization));
    }
}
?>
