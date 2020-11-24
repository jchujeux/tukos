<?php
namespace TukosLib\Objects\Admin\Translations;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\Directory;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Objects\ItemsExporter;
use TukosLib\Objects\ItemsImporter;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends ObjectTranslator {
    use itemsExporter, itemsImporter;
	public static function translationSets(){
        return [];
    }

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator);
        $this->objectName = $objectName;
        $this->user  = Tfk::$registry->get('user');
        $this->allCols = ['id', 'name', 'setname', 'en_us', 'fr_fr', 'es_es'];        
        $this->extendedNameCols = ['name'];        
        $this->setNameOptions = array_merge(['tukosApp', 'tukosLib', 'countrycodes'], Directory::getDomains(), ['page', 'common']);
		$this->store = Tfk::$registry->get('configStore');
		$this->idCols = $this->idColsObjects = [];
		$this->user->forceContextId($objectName, $this->user->contextModel->getRootId());
		$this->colsToTranslate = [];
    }
    
    public function initialize($init=[]){
        return $init;
    }

    public function initializeExtended($init=[]){
        return $this->initialize($init);
    }
    public function getItemCustomization($where, $keys){
        return [];
    }
    public function getCombinedCustomization($where, $view, $paneMode, $keys){
        return [];
    }
   
    function options($property){
        $name = $property . 'Options';
        return $this->$name;
    }
   
    public function translateOne($item){
    	return $item;
    }

    public function translateAll($items){
    	return $items;
    }

    public function getOne ($atts){
        $atts['table'] = $this->objectName;
        return $this->store->getOne($atts);
    }

    public function getOneExtended($atts){
        return $this->getOne($atts);
    }
    
    public function getAll($atts){
    	Utl::extractItem('allDescendants', $atts);
    	$atts['table'] = $this->objectName;
    	$atts['where'] = SUtl::transformWhere($atts['where'], 'translations', true);
    	$result = $this->store->getAll($atts);
    	$this->foundRows = $this->store->foundRows();
    	return $result;
    }
    
    public function getAllExtended($atts){
        return $this->getAll($atts);
    }

    public function foundRows(){
        return $this->foundRows;/* to be set by $this->getAll*/
    }
    
    public function insert ($values){
        $this->store->insert($values, ['table' => $this->objectName]);
        $values['id'] = $this->store->lastInsertId();
        return $values;
    }
    public function insertExtended ($values){
        return $this->insert($values);
    }
    
    public function updateOne ($values){
        $this->store->update($values, ['table' => $this->objectName, 'where' => ['id' => $values['id']]]);
        return ['id' => $values['id']];
    }
    public function updateOneExtended ($values, $atts=[]){
        return $this->updateOne($values, $atts);
    }
    public function updateAll ($values, $atts){
        $atts['table'] = $this->objectName;
        return $this->store->update($values, $atts);
    }
    public function duplicate($ids, $cols=['*']){
        Feedback::add('TranslationsDuplicateNotImplemented');
        return false;
    }
    public function duplicateOneExtended($id, $cols){
    	return $this->getOne(['where' => ['id' => $id], 'cols' => array_diff($cols, ['id'])]);
    }
    
    public function delete ($where){
        return $this->store->delete(['table' => $this->objectName, 'where' => $where]);
    }
    public function summary($storeAtts){
        return [
            'filteredrecords' => empty(Utl::getItem('where', $storeAtts)) ? $this->foundRows() : $this->getAll(['where' => $this->user->filter($storeAtts['where'], $this->objectName), 'cols' => ['count(*)']])[0]['count(*)'],
            'totalrecords' => $this->getAll(['where' => $this->user->filter([], $this->objectName), 'cols' => ['count(*)']])[0]['count(*)']
        ];
    }
}
?>
