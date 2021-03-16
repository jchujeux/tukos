<?php
namespace TukosLib\Objects\Collab\Blog\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\Utils\DateTimeUtilities as DUtl;
use TukosLib\TukosFramework as Tfk;

class GetOverviewItems extends ObjectTranslator{
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
        $items = $this->blogModel->getAll(['where' => Utl::getItem('where', $storeAtts, []), 'cols' => ['id', 'parentid', 'name', 'comments', 'updated', 'updator']]);
        foreach($items as &$item){
            $item['onClickGotoTab'] = 'edit';
            $item['comments'] = "<div><h3>{$item['name']}</h3></div><div style=\"text-align:right;\"><i>{$this->tr('postedby')}</i>: {$item['updator']} <i>{$this->tr('postedon')}</i>" .  DUtl::toUTC($item['updated']) . "</div>" . HUtl::cut($item['comments'], 500);
        }
        return (empty($storeAtts['range'])) ? ['items' => $items] : ['items' => $items, 'total' => $this->blogModel->foundRows()];
    }
}
?>
