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
        $parentId = $storeAtts['where']['contextid'];
        $storeAtts['where'] = [[['col' => 'contextid', 'opr' => '=', 'values' => $parentId], ['col' => 'parentid', 'opr' => '=', 'values' => $parentId, 'or' => true]]];
        $items = $this->blogModel->getAll(['where' => $this->user->filterPrivate($storeAtts['where']), 'cols' => ['id', 'parentid', 'name', 'comments', 'published'/*, 'updated', 'updator'*/]]);
        $parentIds = array_column($items, 'parentid');
        $itemsToReturn = [];
        foreach($items as $item){
            if ($item['parentid'] == '' || $item['parentid'] === '0' || $item['parentid'] === $parentId){
                $item['onClickGotoTab'] = 'edit';
                $item['hasChildren'] = in_array($item['id'], $parentIds);
                $item['published'] =  substr($item['published'], 0, 10);
                $itemsToReturn[] = $item;
            }
        }
        return ['items' => $itemsToReturn];
    }
}
?>
