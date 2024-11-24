<?php
namespace TukosLib\Objects\Collab\Blog\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class GetContextsAndItems extends ObjectTranslator{
    function __construct($query){
        parent::__construct('blog');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->blogModel = $this->objectsStore->objectModel('blog');
        $this->dataWidgets = [];
    }
    function get($query){
        ['storeatts' => $storeAtts, 'params' => $params] = $query; $type = $params['type']; $where = $storeAtts['where'];
        $blogModel = $this->objectsStore->objectModel('blog');
        if ($type === 'contexts'){// the parent object is a context, return all contexts whch parentid is $parentid, and all blog items which parentid is $parentid
            if ($where['parentid'] === 0){
                $where['parentid'] = $this->user->getRootId();
            }
            //$language = Tfk::$registry->get('translatorsStore')->getLanguage();
            //$blogItems = $blogModel->getAll(['where' => $this->user->filterPrivate(['contextid' => $where['parentid'], 'language' => $language]), 'cols' => ['id', 'name', 'object', 'published'], 'orderBy' => ['published' => 'DESC']]);
            $blogItems = $blogModel->getPosts(['contextid' => $where['parentid']], 1000, false);
            $contextsModel = $this->objectsStore->objectModel('contexts');
            $contextsItems = Utl::toAssociative($contextsModel->getAll(['where' => $where, 'cols' => ['id', 'name', 'object']]), 'id');
            /*$blogsCount = Utl::toAssociative(SUtl::$store->getAll([
                'cols' => ['contextid, count(*) as children'],
                'table' => SUtl::$tukosTableName,
                'where' => [['col' => 'id', 'opr' => '>', 'values' => 0], ['col' => 'permission', 'opr' => 'NOT IN', 'values' => ['PL', 'PR']], 'object' => 'blog'],
                'groupBy' => ['contextid']
                
            ]), 'contextid');*/
            $whereCount = [['col' => 'id', 'opr' => '>', 'values' => 0], ['col' => 'permission', 'opr' => 'NOT IN', 'values' => ['PL', 'PR']], 'object' => 'blog'];
            if ($language = $blogModel->getPostsLanguage()){
                $whereCount['language']= $language;
            }
            $blogsCount = Utl::toAssociative($blogModel->getAll(['cols' => ['contextid, count(*) as children'], 'where' => $whereCount, 'groupBy' => ['contextid']]), 'contextid');
            foreach($contextsItems as $contextid => &$item){
                $item['children'] = Utl::getItem('children', Utl::extractItem($contextid, $blogsCount, []), 0);
            }
            unset($item);
            foreach($blogsCount as $contextid => $count){
                $ancestors = $contextsModel->getAncestorsId($contextid);
                foreach($ancestors as $ancestor){
                    if (isset($contextsItems[$ancestor])){
                        $contextsItems[$ancestor]['children'] += $count['children'];
                    }
                }
            }
            $contextsItemsWithBlogs = [];
            $contextsItems = Utl::toNumeric($contextsItems, 'id');
            foreach ($contextsItems as &$contextsItem){
                $contextsItem['name'] = $this->tr($contextsItem['name']);
                if ($contextsItem['children']){
                    $contextsItemsWithBlogs[] = $contextsItem;
                }
            }
            unset($contextsItem);
            /*foreach($blogItems as &$blogItem){
                $blogItem['onClickGotoTab'] = 'edit';
                $blogItem['published'] =  substr($blogItem['published'], 0, 10);
            }*/
            $items = array_merge($blogItems, $contextsItemsWithBlogs);
        }else{//the parent object is a blog - currently should not happen
            $items = $blogModel->getPosts($where);
        }
        return ['items' => $items];
    }
}
?>
