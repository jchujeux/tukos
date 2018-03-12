<?php
/**
 *
 */
namespace TukosLib\Auth;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

trait ContextCustomization {

    function customContexts(){
        if (!property_exists($this, 'customContextIds')){
            if (!empty($this->userInfo['customcontexts'])){
                $this->customContextIds = json_decode($this->userInfo['customcontexts'], true);
            }else{
                $this->customContextIds = [];
            }
        }
    }
    function customContextId($module){
        $this->customContexts();
        return (isset($this->customContextIds[$module]) ? $this->customContextIds[$module] : null);
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
            return $this->contextModel->getContextId('name', $this->objectModulesDefaultContextName[$module]);
        }else if ($contextId === false){//module was not found: default to root
            return $this->contextModel->rootId;
        }else{
            return $this->contextModel->getContextId('id', $contextId);
        }
    }

    public function forceContextId($module, $contextId){
    	$this->moduleContextId[$module] = $contextId;
    }
    
    public function getContextId($module){// if customization is present returns the customized value, else returns the default one
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
    
    function updateModuleContext($moduleContext){
        $contextid = (empty($moduleContext['contextid']) ? '' : $moduleContext['contextid']);
        $this->customContexts();
        $this->objectsStore->objectModel('users')->updateOne(
            ['customcontexts' => [$moduleContext['module'] => $contextid]], 
            ['where' => ['id' => $this->id()]], 
            true, true
        );
        if (!empty($contextid)){
            $this->customContextIds[$moduleContext['module']] = $contextid;
        }else if(isset($this->customContextIds[$moduleContext['module']])){
            unset($this->customContextNames[$moduleContext['module']]);
        }
        return $moduleContext;
    }
}
?>
