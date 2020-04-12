<?php
namespace TukosLib\Auth;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

trait ContextCustomization {

    
    function customContexts(){
        if (!property_exists($this, 'customContextIds')){
            $this->customUserContextIds = json_decode(Utl::getItem('customcontexts', $this->userInfo, "[]", "[]"), true);
            $this->customTukosContextIds = json_decode(Utl::getItem('customcontexts', $this->tukosInfo, "[]", "[]"), true);
            $this->customContextIds = array_merge($this->customTukosContextIds, $this->customUserContextIds);
        }
    }
    function customContextId($module, $tukosOrUser){//returns the customized context for this module or null
        $this->customContexts();
        $tukosOrUserContexts = ['tukos' => 'customTukosContextIds', 'user' => 'customUserContextIds'][$tukosOrUser];
        return Utl::getItem($module, $this->$tukosOrUserContexts, null);
        //return (isset($this->customContextIds[$module]) ? $this->customContextIds[$module] : null);
    }
    private function moduleCustomContextId($module, $modulesLayout){
        static $contextId = false;
        if (is_array($modulesLayout)){
            foreach($modulesLayout as $key => $layout){
                $keyModule = (($key[0] === '#' || $key[0] === '@') ? substr($key, 1): $key);
                if ($keyModule === $module){
                    return (isset($this->customContextIds[$keyModule]) ?  $this->customContextIds[$keyModule] :  true);
                }else{
                    $contextId = $this->moduleCustomContextId($module, (isset($layout[0]) ? $layout[0] : null));
                    if ($contextId === true){// found the module, not yet the first ancestor with a custom context 
                        return (isset($this->customContextIds[$keyModule]) ? $this->customContextIds[$keyModule] : true);
                    }else if ($contextId !== false){
                        return $contextId;
                    }
                }
            }
        }
        return $contextId;
    } 
    private function setContextId($module){
        $this->customContexts();
        $contextId = $this->moduleCustomContextId($module, $this->modulesMenuLayout);
        if ($contextId === true){//module was found and has no customization in its ancestors
            return $this->contextModel->getContextId('name', Utl::getItem($module, $this->objectModulesDefaultContextName, $module, $module));
        }else if ($contextId === false){//module was not found: default to root
            return $this->contextModel->rootId;
        }else{
            return $this->contextModel->getContextId('id', $contextId);
        }
    }
    public function forceContextId($module, $contextId){//hack for translations
    	$this->moduleContextId[$module] = $contextId;
    }
    public function getContextId($module){// returns the contextid for this module looking into the ancestor customization path, with default as fallback.
        if (!isset($this->moduleContextId[$module])){
            $this->moduleContextId[$module] = $this->setContextId($module);
        }
        return $this->moduleContextId[$module];
    }
    public function customContextAncestorsPaths($module){
        $customContextId = $this->getContextId($module);
        $contextAncestors = [];
        Utl::getAncestors($customContextId, $this->contextModel->storeData, $contextAncestors, 'id', 'parentid');
        if (empty($contextAncestors)){// contextid for this user incompatibible with his visibility rights (?)
            Feedback::add('configuration error: you do not have access to the custom context: ', $customContextId);
            return [[['id' => 'tukos']]];
        }else{
            $contextAncestors = array_reverse($contextAncestors);
            foreach ($contextAncestors as $ancestor){
                $returnedPath[] = ['id' => $ancestor];
            }
            return [$returnedPath];
        }
    }
    function updateModuleContext($module, $contexts){
        $this->customContexts();
        $tukosOrUserContextsIds = ['tukosContext' => 'customTukosContextIds', 'userContext' => 'customUserContextIds'];
        foreach($contexts as $name => $contextId){
            $contextIds = $tukosOrUserContextsIds[$name];
            $this->$contextIds[$module] = $contextId;
            $this->objectsStore->objectModel('users')->updateOne(['customcontexts' => [$module => $contextId]], ['where' => $name === 'tukos' ? ['name' => 'tukosContext'] : ['id' => $this->id()]], true, true);
        }
        return ['data' => ['activeContext' => $this->getContextId($module)]];
    }
}
?>