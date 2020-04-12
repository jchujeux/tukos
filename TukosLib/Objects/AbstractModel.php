<?php
/**
 *
 * abstract class for tukos objects (models) 
 * We implement here the business logic of a tukos object: aTukos object
 *      - is associated to a table in the associated $store
 *      - provides methods and properties that are then available to the Controllers and other Tukos objects

 */
namespace TukosLib\Objects;

use TukosLib\Utils\Feedback;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\Directory;
use TukosLib\Objects\ItemHistory;
use TukosLib\Objects\ItemJsonCols;
use TukosLib\Objects\ItemsChildren;
use TukosLib\Objects\Store;
use TukosLib\Objects\ItemsCache;
use TukosLib\Objects\ItemCustomization;
use TukosLib\Objects\ContentExporter;
use TukosLib\Objects\ItemsExporter;
use TukosLib\Objects\ItemsImporter;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;

use TukosLib\TukosFramework as Tfk;

abstract class AbstractModel extends ObjectTranslator {

    use ItemHistory, ItemJsonCols, ItemsChildren, Store, ItemCustomization, ContentExporter, ItemsExporter, ItemsImporter;
    
  //these are the minimal cols required in the $store  table for any Tukos object, with associated keys definition, to be completed with object specific cols definitions
    protected $permissionOptions = ['NOTDEFINED', 'PR', 'RO', 'PU', 'ACL'];
    protected $aclOptions = ['0' => 'none', '1' => 'read', '2' => 'update', '3' => 'delete'];
    protected $gradeOptions = ['TEMPLATE', 'NORMAL', 'GOOD', 'BEST'];
    protected $timeIntervalOptions =  ['', 'year', 'quarter', 'month', 'week', 'weekday', 'day', 'hour', 'minute', 'second'];// corresponds to intersection of php strToTime & dojo.date supported intervals
    protected $useItemsCache = true;
    const optionalCols = ['worksheet', 'custom', 'history'];

    public static function translationSets(){// for cases where an object need specific translation sets (e.g. 'countrycodes' for the people object
        return [];
    }
    function __construct ($objectName, $translator, $tableName, $idColsObjects, $jsonCols, $colsDefinition, $colsIndexes = [], $colsToTranslate = [], $optionalCols=['custom'], $extendedNameCols = ['name']) {
    	$this->configStatusOptions = array_keys(Directory::configStatusRange());
    	$this->store  = $store = Tfk::$registry->get('store');
        $this->user  = Tfk::$registry->get('user');
        $this->tukosModel = Tfk::$registry->get('tukosModel');
        parent::__construct($objectName, $translator);
        $this->objectName = $objectName;
        $this->tableName = $tableName;
        $this->idColsObjects = Utl::array_merge_recursive_replace($this->tukosModel->idColsObjects, array_merge(['id' => $objectName],$idColsObjects));
        $this->idCols = array_keys($this->idColsObjects);
        $this->gridsIdCols = ['acl' => ['userid']];
        $this->colsDescription = empty($colsDefinition) ? [] : array_merge([ 'id'  =>  'INT(11) PRIMARY KEY'], $colsDefinition);
        if (!empty($this->colsDescription)){
            if (!$store->tableExists($this->tableName)){
                $store->createTable($this->tableName, $this->colsDescription, $colsIndexes);
            }else if ($this->user->isSuperAdmin()){
                $store->addMissingColsIfNeeded ($this->colsDescription, $tableName);
            }
        }
        $this->extendedNameCols = $extendedNameCols;
        $this->tukosExtendedNameCols  = array_intersect($this->extendedNameCols, $this->tukosModel->allCols);
        $this->objectExtendedNameCols = array_diff($this->extendedNameCols, $this->tukosExtendedNameCols);
        
        $this->absentOptionalCols = array_diff(self::optionalCols, $optionalCols);
        $this->jsonCols = array_merge(['acl'], array_intersect(['worksheet', 'custom'], $optionalCols), $jsonCols);
        $this->jsonColsFilters = ['acl' => true, 'worksheet' => true];
        
        $this->textColumns = array_merge(
            array_diff($this->tukosModel->textColumns, $this->absentOptionalCols),
            array_keys(array_filter($colsDefinition, function($def){return in_array(strtolower(substr($def, 0, 4)), $this->tukosModel->_textColumns);}))
            );
        $this->maxSizeCols = array_merge(
            array_diff($this->tukosModel->maxSizeCols, $this->absentOptionalCols),
            array_diff(array_keys(array_filter($colsDefinition, function($def){return in_array(strtolower(substr($def, 0, 8)), $this->tukosModel->_largeColumns);})), $jsonCols)
        );
        $this->objectCols = array_keys($colsDefinition);
        $this->allCols = array_merge(array_diff($this->tukosModel->sharedObjectCols, $this->absentOptionalCols), $this->objectCols);
        $this->colsToTranslate = array_merge(['permission', 'grade', 'configstatus'], $colsToTranslate);
    }
    public function setUseItemsCache($newValue){
        $this->useItemsCache = $newValue;
    }
    public static function emptyItemsCache(){// empties the cache for all objects, not only for this object
        ItemsCache::emptyCache();
    }
    public function setInits(){
        if (!property_exists($this, 'init')){
            $this->init = ['permission' =>  'RO', 'contextid' => $this->user->getContextId($this->objectName)/*, 'comments' => ''*/, 'grade' => 'NORMAL', 'configstatus' => 'users'];
        }
    }
    public function initialize($init=[]){
        $this->setInits();
        return array_merge ($this->init, $init);
    }
    public function initializeExtended($init=[]){
        $result = $this->initialize($init, true);
        $this->addItemIdCols($result);
        return $result;
    }
    function options($property){
        $name = $property . 'Options';
        if (isset($this->$name)){
            return $this->$name;
        }else{
            if (!isset($this->staticDomainClassName)){
                $pathArray = explode('\\', get_Class($this));
                array_splice($pathArray, 3, count($pathArray), [$pathArray[2]]);
                $this->staticDomainClassName = implode('\\', $pathArray);
            }
            return $this->$name = $this->staticDomainClassName::$$name;
        }
    }
    
    public function errorModelAction(){
        Feedback::add($this->tr('errormodelaction'));
    }
    public function getOne ($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
    	$atts = ['table' => $this->tableName] + $atts;
		$missingCols = ($this->useItemsCache ? ItemsCache::missingCols($atts) : $atts['cols']);
		if (empty($missingCols)){
            $result = ItemsCache::getOne($atts);
            //Feedback::add('Avoided store->getOne; ' . json_encode($atts));
        }else{
            $requiredCols = $atts['cols'];
            $atts['cols'] = $missingCols;

            $result =  $this->getItem($atts);

            if ($result){
                if ($jsonColsPaths){
                    $this->jsonDecode($result, array_keys($jsonColsPaths));
                }
                if (!empty($result['history'])){
                    $result['history'] = $this->expandHistory($result, $atts);
                }
            	if ($this->useItemsCache){
                    $atts['cols'] = $requiredCols;
                    $result = ItemsCache::mergeOne($result, $atts);
                }
            }else{
            	//Feedback::add($this->tr('itemnotfound'));
            	return [];
            }
        }
        if ($jsonColsPaths){
            $this->drilldown($result, $jsonColsPaths, $jsonNotFoundValue);
        }
        return $result;
    }
    public function getOneExtended($atts, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $value = $this->getOne($atts, $jsonColsPaths, $jsonNotFoundValue);
        if (!empty($value)){
            $this->addItemIdCols($value);
        }
        return $value;
    }
    public function foundRows(){
        return $this->foundRows;
    }
    public function getAll ($atts, $jsonColsPaths = [], $jsonNotFoundValues = null){
    	$atts = ['table' => $this->tableName] + $atts;
    	if ($allDescendants = Utl::extractItem('allDescendants', $atts, false)){
    		if (isset($atts['range'])){
            	Tfk::error_message('info', 'combining range and allDescendants is not supported. Continuing at your own risks - atts: ', $atts);
    		}
    		if ($allDescendants === 'hasChildrenOnly' && empty ($atts['where']['parentid'])){
    			$atts['where'][] = ['opr' => 'NOT EXISTS', 'values' => 'SELECT 1 FROM tukos as t1 WHERE tukos.parentid = t1.id AND object="' . $this->tableName . '"'];
    		}
        }
        $values = $this->getItems($atts);
        $this->foundRows = $this->store->foundRows();
        if (!empty($values)){
            if ($allDescendants){
                if ($allDescendants === 'hasChildrenOnly' && empty($atts['where']['parentid'])){// for items with children matching $atts, set the 'hasChildren' property to true for processing at the StoreDgrid level
                    array_pop($atts['where']);
                	$this->setHasChildren($values, $atts);
                }else{// add all descendants items to $salue
                    $this->addDescendants($values, $atts);
                }
            }
        }
        if ($jsonColsPaths){
            $jsonCols = array_keys($jsonColsPaths);
            forEach ($values as &$value){
                $this->jsonDecode($value, $jsonCols);
                $this->drillDown($value, $jsonColsPaths, $jsonNotFoundValues);
            }
        }
        return $values;
    }
    
    public function getAllExtended($atts){
        if ($atts['cols'] !== '*'){
            if (!empty($fieldsMaxSize = $this->user->fieldsMaxSize())){
                $presentMaxSizeCols = array_intersect($atts['cols'], $this->maxSizeCols);
                foreach ($presentMaxSizeCols as $key => $col){
                    //$atts['cols'][$key] = 'if(length(' . $col . ') > ' . $fieldsMaxSize . ', concat("#tukos{id:", tukos.id, ",object:' . $this->objectName . ',col:' . $col . '}"),' . $col . ') ' . $col;
                    //$extCol = "tukos.$col";
                    $extCol = SUtl::colsPrefix($col, $this->tableName) . "$col";
                    $atts['cols'][$key] = "if(length($extCol) > $fieldsMaxSize , concat(\"#tukos{id:\", tukos.id, \",object:$this->objectName,col:$col}\"),$extCol) as $col";
                }
            }
        }
        $items = $this->getAll($atts);
        $this->addItemsIdCols($items);
        return $items;
    }

    public function translateOne(&$item){
        $cols = array_intersect(array_keys($item), $this->colsToTranslate);
    	foreach ($cols as $col){
    		$item[$col] = $this->tr($item[$col]);
    	}
    	return $item;
    }
    public function translateAll($items){
    	foreach ($items as &$item){
    		$this->translateOne($item);
    	}
    	return $items;
    }
    
    public function completeUpdateAtts($values, $atts){
        if ((empty($atts) || empty($atts['where'])) && !empty($values['id'])){
            $atts['where'] = ['id' => $values['id']];
        }
        $defCols = ['id', 'permission', 'acl', 'updator', 'updated'];
        if (in_array('history', $this->allCols)){
            $defCols[] = 'history';
        }
        $changedCols = array_keys($values);
        if ($this->extendedNameCols !== ['name'] && !empty(array_intersect($this->extendedNameCols, $changedCols))){
            $this->extendedNameChange = true;
            $defCols = array_merge($defCols, $this->extendedNameCols);
        }else{
            $this->extendedNameChange = false;
        }
        if (empty($atts['cols'])){
                $atts['cols'] = array_unique(array_merge($defCols, $changedCols));
        }else{
            $atts['cols'] = array_unique(array_merge($atts['cols'], $defCols, $changedCols));
        }
        return $atts;
    }
    
    protected function activeJsonColsDefaultPath($presentCols, $newItem){
        $presentJsonCols = array_intersect($presentCols, $this->jsonCols);
        $activeJsonCols = [];
        foreach ($presentJsonCols as $jsonCol){
            if (!is_string(Utl::getItem($jsonCol, $newItem))){
                $activeJsonCols[$jsonCol] = [];
            }
        }
        return $activeJsonCols;
    }

    public function updateOne($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        if (isset($newValues['configstatus'])){
            unset($newValues['configstatus']);
        }
        $atts = $this->completeUpdateAtts($newValues, $atts);
        $oldValues = $this->getOne($atts, $this->activeJsonColsDefaultPath($atts['cols'], $newValues));
        if (empty($oldValues)){
            if ( $insertIfNoOld){
                return $this->insert($newValues, $init);
            }else{
                Feedback::add($this->tr('objectNotFound') . ': ' . json_encode($atts['where']));
                return false;
            }
        }else{
            if (!empty($newValues['updated']) && $newValues['updated'] < $oldValues['updated']){
                Feedback::add([[$this->tr('theuser') => $oldValues['updator']], [$this->tr('updatedat') => $oldValues['updated']], [$this->tr('afteryouredit') => null]]);
                return false;
            }
            if ($this->user->hasUpdateRights($oldValues)){
                return $this->_update($oldValues, $newValues, $jsonFilter);
            }else{
                Feedback::add($this->tr('Noupdaterightsfor') . ' ' . $oldValues['id']);
                return false;
            }
        }
    }
    
    public function updateOneExtended($newValues, $atts=[], $insertIfNoOld = false, $jsonFilter=false, $init = true){
        $this->processLargeCols($newValues);
        return $this->updateOne(array_intersect_key($newValues, array_flip($this->allCols)), $atts, $insertIfNoOld, $jsonFilter, $init);
    }
    
    public function processLargeCols(&$newValues){
        $presentMaxSizeCols = array_intersect($this->maxSizeCols, array_keys($newValues));
        $colsToGet = []; $colsToProcess = [];
        foreach($presentMaxSizeCols as $presentMaxSizeCol){
            $matches = [];
            $outcome = preg_match_all("/#tukos{id:([^,]*),object:([^,]*),col:([^}]*)}/", $newValues[$presentMaxSizeCol], $matches, PREG_SET_ORDER);
            if (!empty($matches)){
                $colsToProcess[] = $presentMaxSizeCol;
                foreach ($matches as $match){
                    $id = $match[1]; $object = $match[2];  $col = $match[3];
                    if (!isset($colsToGet[$object])){
                        $colsToGet[$object] = [$col => [$id]];
                    }else if (!isset($colsToGet[$object][$col])){
                        $colsToGet[$object][$col] = [$id];
                    }else{
                        $colsToGet[$object][$col][] = $id;
                    }
                }
            }
        }
        if (!empty($colsToGet)){
            $objectsStore = Tfk::$registry->get('objectsStore');
            $results = [];
            foreach($colsToGet as $object => $cols){
                $results[$object] = [];
                foreach($cols as $col => $ids){
                    $results[$object][$col] = Utl::toAssociative($objectsStore->objectModel($object)->getAll(['where' => [['col' => 'id', 'opr' => 'IN', 'values' => array_unique($ids)]], 'cols' => ['id', $col]]), 'id');
                }
            }
            foreach ($colsToProcess as $col){
                $newValues[$col] = preg_replace_callback("/#tukos{id:([^,]*),object:([^,]*),col:([^}]*)}/", function($matches) use ($results){
                    return $results[$matches[2]][$matches[3]][$matches[1]][$matches[3]];
                },
                $newValues[$col]
                );
            }
        }
        
    }
    

    public function updateAll ($newValues, $atts=[]){
        $atts['where'] = $this->user->filterContext($this->user->filterReadOnly($atts['where']));
        $atts = $this->completeUpdateAtts($newValues, $atts);
        $oldValuesArray = $this->getAll($atts);

        $updatedRows = [];
        foreach ($oldValuesArray as $key => $oldValues){
            if (!empty($oldValues['history'])){
                $oldValues['history'] = $this->expandHistory($oldValues, ['where' => $oldValues['id'], 'table' => $this->tableName]);
            }
            $result = $this->_update($oldValues, $newValues);
            if ($result){
                $updatedRows[] = $result;
            }
        }
        return $updatedRows;
    }
    
    private function _update($oldValues, $newValues, $jsonFilter = false){
        $oldUpdator = Utl::extractItem('updator', $oldValues);
        $oldHistory = Utl::extractItem('history', $oldValues);

        $this->jsonNewValues($oldValues, $newValues);
        
        $differences = array_udiff_assoc($newValues, $oldValues, function($left, $right){return ($left === $right ? 0 : 1);});
        if (!empty($differences)){
            $updated = $differences['updated'] = date('Y-m-d H:i:s');
            $updator = $this->user->id();
            if ($updator != $oldUpdator){
                $differences['updator'] = $updator;
                $oldValues['updator'] = $oldUpdator;
            }

            if (in_array('history', $this->allCols)){
                $differences['history'] = $this->addHistory($oldHistory, Utl::getItems(array_keys($differences), $oldValues));
                //$differences['history'] = $this->compressHistory($differences, $oldValues['id']);
            }
            if ($this->useItemsCache){
                ItemsCache::updateOne($differences, $oldValues['id']);
            }
            if (!empty($differences['history'])){
                $differences['history'] = $this->compressHistory($differences, $oldValues['id']);
            }
            
            $this->jsonEncode($differences, $jsonFilter);
            
            $this->updateItems($differences, ['table' =>  $this->tableName, 'where' => ['id' => $oldValues['id']]]);

            $updatedRow = ['id' => $oldValues['id'], 'updated' => $updated, 'updator' => $updator];

            return $updatedRow;

        }else{
            return false;
        }
    }
    function append($values, $cols=false, $separator="\n"){
        if (! empty($values['id'])){
            $id = Utl::extractItem('id', $values);
            $valueCols = array_keys($values);
            $colsToAppend = ($cols ? $cols : $valueCols);
            $newValues = $this->getOne(['where' => ['id' => $id], 'cols' => $colsToAppend]);
            $newValues['id'] = $id;
            $needsUpdate = false;
            foreach ($colsToAppend as $col){
                if (!empty($values[$col])){
                    $needsUpdate = true;
                    $newValues[$col] .= $separator . $values[$col];
                }
            }
            if ($needsUpdate){
                $this->updateOne($newValues);
            }
        }
    }
    public function setReference(&$values, $reference){
	    $dateCol = $reference['dateCol'];
	    $refCol = $reference['referenceCol'];
	    $refPrefix = $reference['prefix'];
    	if (!empty($values[$dateCol])){
	    	$refDate = str_replace('-', '', $values[$dateCol]);
	    	$latest = $this->getOne([
	    			'where' => [['col' => 'id', 'opr' => '>', 'values' => 0], ['col' => $refCol, 'opr' => 'like', 'values' => $refPrefix . $refDate . '%']],
	    			'cols' => [$refCol], 'orderBy' => [$refCol => 'DESC']
	    	]);
	    	if (empty($latest)){
	    		$values[$refCol] = $refPrefix . $refDate . '01';
	    	}else{
	    		$number = substr($latest['reference'], -2);
	    		$values[$refCol] = $refPrefix . $refDate . sprintf('%02d', $number+1);
	    	}
	    }
    }
    public function insert($values, $init = false, $jsonFilter = false, $reference = null){
        if (is_array($init)){
            $values = array_merge($this->initialize(), $init, $values);
        }else if ($init){
            $values = array_merge($this->initialize(), $values);
        }
        if (isset($values['configstatus'])){
            $configStatus = empty($values['configstatus']) ? 'users' : $values['configstatus'];
            unset($values['configstatus']);
        }else{
            $configStatus = 'users';
        }
        if (isset($reference)){
        	$this->setReference($values, $reference);
        }
        $values['id']      = $this->tukosModel->nextId($configStatus);
        $values['created'] = date('Y-m-d H:i:s');
        $values['creator'] = $this->user->id();
        $values['updated'] = date('Y-m-d H:i:s');
        $values['updator'] = $this->user->id();
        foreach ($this->idCols as $idCol){
            if (!isset($values[$idCol])){
                $values[$idCol] = 0;
            }
        }
        if ($this->useItemsCache){
            ItemsCache::insert($values);
        }
        $this->jsonEncode($values, $jsonFilter);
        $this->insertItem($values);

        return $values;
    }
    public function insertExtended($values, $init=false, $jsonFilter = false){
        if (is_array($init)){
            $values = array_merge($this->initializeExtended(), $init, $values);
        }else if ($init){
            $values = array_merge($this->initializeExtended(), $values);
        }
        $this->processLargeCols($values);
        return $this->insert(array_intersect_key($values, array_flip($this->allCols)), false, $jsonFilter);        
    }
    public function duplicate($ids, $cols=['*']){
        $result = [];
        foreach ($ids as $id){
            $values = $this->getOne(['where' => ['id' => $id], 'cols' => $cols]);
            $result[] = $this->insert($values)['id'];
        }
        return $result;
    }
    public function duplicateOneExtended($id, $cols, $jsonColsPaths = [], $jsonNotFoundValue=null){
        $duplicate =  $this->getOne(['where' => ['id' => $id], 'cols' => array_diff($cols, ['id', 'created', 'creator', 'updated', 'updator', 'history'])], $jsonColsPaths, $jsonNotFoundValue);
        $initial = array_intersect_key($this->initialize(), $duplicate);
        foreach($initial as $col => $value){
        	if ($duplicate[$col] === null){
        		$duplicate[$col] = $initial[$col];
        	}
        }
        $this->addItemIdCols($duplicate);
        return $duplicate;
    }
    public function delete ($where, $item = []){
        $oldItems = $this->getAll(['where' => $where, 'cols' => ['id', 'updator', 'permission', 'updated']]);
        foreach($oldItems as $old){
            if ($this->user->hasDeleteRights($old)){
                $toDelete[] = $old['id'];
            }else{
                $noRightToDelete[] = $old['id'];
            }
        }
        if (!empty($noRightToDelete)){
            Feedback::add($this->tr('nodeleterightsfor') . ': ' . json_encode($noRightToDelete));
        }
        if (empty($toDelete)){
            Feedback::add($this->tr('noitemwasdeleted'));
            return false;
        }else{
            return $this->updateItems([], ['where' => [['col' => 'id', 'opr' => 'in', 'values' => $toDelete]], 'set' => ['id' => '-id', 'updated' => "'" . date('Y-m-d H:i:s') . "'", 'updator' => $this->user->id()]]);
        }
    }
    public function summary($activeUserFilters = null){
        return [
        		'filteredrecords' => is_null($activeUserFilters) ? $this->foundRows() : $this->getAll(['where' => $this->user->filter($activeUserFilters, $this->objectName), 'cols' => ['count(*)']])[0]['count(*)'],
        		'totalrecords' => $this->getAll(['where' => $this->user->filter([], $this->objectName), 'cols' => ['count(*)']])[0]['count(*)']
        ];
    }
}
?>
