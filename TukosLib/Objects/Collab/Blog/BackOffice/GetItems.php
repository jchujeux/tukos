<?php
namespace TukosLib\Objects\Collab\Blog\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Web\BlogView;
use TukosLib\TukosFramework as Tfk;

class GetItems extends ObjectTranslator{
    function __construct($query){
        parent::__construct('blog');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->blogModel = $this->objectsStore->objectModel('blog');
        $this->dataWidgets = [];
    }
    function get($query){
        $storeAtts = $query['storeatts'];
        $items = $this->blogModel->getAll(['where' => $storeAtts['where'], 'cols' => ['id', 'parentid', 'name', 'comments', 'published'/*, 'updated', 'updator'*/]]);
        foreach($items as &$item){
            $item['onClickGotoTab'] = 'edit';
            $item['hasChildren'] = false;
            $item['published'] =  substr($item['published'], 0, 10);
        }
        if (! empty($storeAtts['range'])){
            return ['items' => $items, 'total' => $this->blogModel->foundRows()];
        }else{
            return ['items' => $items];
        }
        return (!empty($storeAtts['range'])) ?  ['items' => $items, 'total' => $this->blogModel->foundRows()] : ['items' => $items];
    }
}
?>
