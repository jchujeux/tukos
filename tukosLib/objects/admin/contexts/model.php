<?php
/**
 * class for the contexts  object, i.e the tree representing the tukos objects space:
 *  - Every object in tukos is attached to one or several nodes in the tukos space
 *  - tukos knows which tukos space(s) is(are) currenly active, i.e. queries for objects in the currently active space(s) will be successful
 */
namespace TukosLib\Objects\Admin\Contexts;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [];
        parent::__construct($objectName, $translator, 'contexts', ['parentid' => ['contexts']], [], $colsDefinition, '', ['name']);
        $this->storeData = $this->getAll(['where' => $this->user->filterPrivate([]), 'cols' => ['id', 'name', 'parentid']]);
        $this->ancestors   = [];
        $this->descendants = [];
        $this->rootName = 'tukos';
        $this->rootId = $this->getContextId('name', $this->rootName);
        
        Utl::getAncestors($this->user->contextid(), $this->storeData, $this->ancestors, 'id', 'parentid');
        if (empty($this->ancestors)){/* contextid for this user incompatibible with his visibility rights (user does not have access to that context => misconfiguration*/
            Tfk::debug_mode('error', 'misconfiguration: user does not have access to his context - userid, contextid: ', [$this->user->id(), $this->user->contextid()]);
            //$this->rootId = $this->getOne(['where' => ['name' => $this->rootName], 'cols' => 'id'])['id'];
        }else{
            $this->ancestors = array_reverse($this->ancestors);
            $this->rootId = $this->ancestors[0];
            Utl::getDescendants($this->user->contextid(), $this->storeData, $this->descendants, 'id', 'parentid');
        }
    }

    public function getOne ($atts, $jsonColsKeys = [], $jsonNotFoundValue=null){
        $unallowed = $this->user->unallowedModules();
        if (!empty($unallowed)){
            $atts['where'][] = ['col' => 'name', 'opr' => 'NOT IN', 'values' => $unallowed];
        }
        return parent::getOne($atts, $jsonColsKeys, $jsonNotFoundValue);
    }

    public function getAll ($atts){ //$where, $cols, $orderBy, $range
        $unallowed = $this->user->unallowedModules();
        if (!empty($unallowed)){
            $atts['where'][] = ['col' => 'name', 'opr' => 'NOT IN', 'values' => $unallowed];
        }
        return parent::getAll($atts);
    }

    public function getContextId($colToSearch, $targetValue){
        $matchKey = Utl::array2D_Search_Strict($this->storeData, $colToSearch, $targetValue); 
        
        if ($matchKey !== false){
            return $this->storeData[$matchKey]['id'];
        }else{
            return $this->rootId;
        }
    }
    public function getRootId(){
    	return $this->rootId;
    }
    
    public function getAncestorsId($contextid){
        $ancestors = [];
        Utl::getAncestors($contextid, $this->storeData, $ancestors, 'id', 'parentid');
        if (empty($ancestors)){
            Feedback::add($this->tr('Could not find ancestors for context id: ', $contextid));
        }else{
            $ancestors = array_reverse($ancestors);
        }
        return $ancestors;
    }
    public function getDescendantsId($contextid){
        $descendants = [];
        Utl::getDescendants($contextid, $this->storeData, $descendants, 'id', 'parentid');
        return $descendants;
    }
    public function getFullPathIds($contextid){
        return $contextid === $this->rootId ? [] : array_merge($this->getAncestorsId($contextid), $this->getDescendantsId($contextid), [0]);
    }
}
?>
