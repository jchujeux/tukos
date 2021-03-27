<?php
namespace TukosLib\Objects\Collab\Blog;

use TukosLib\Objects\AbstractModel;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    protected $languageOptions = ['en-us', 'fr-fr','es-es'];
    function __construct($objectName, $translator=null){
        $this->languageOptions = Tfk::$registry->get('appConfig')->languages['supported'];

        $colsDefinition = [
            'language' =>  "ENUM ('" . implode("','", $this->languageOptions) . "')",
        ];
        parent::__construct($objectName, $translator, 'blog', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], [], $colsDefinition, [['language']]);
    }
    function gotoTabString($view, $queryString){
        return     "tukos.Pmg.tabs.gotoTab({object: 'backoffice', view:'{$view}', mode: 'Tab', action: 'Tab', query:{object: 'blog', form: 'Show', {$queryString}}});";
    }
    function onClickGotoTabString($view, $queryString){
        return " onclick=\"{$this->gotoTabString($view, $queryString)}\"";
    }
    function getRecentPosts(){
        $posts = $this->getAll(['where' => $this->user->filterPrivate([], $this->objectName), 'cols' => ['id', 'name', 'comments', 'updated', 'updator'], 'orderBy' => ['updated' => 'DESC'], 'limit' => 5]);
/*
        $rows = [];
        foreach($posts as $post){
            $rows[] = ['tag' => 'tr', 'content' => [['tag' => 'td', 'content' => $post['name']]]];
        }
        return HUtl::buildHtml(['tag' => 'table', 'atts' => 'style=border: solid;width:100%', 'content' => $rows]);
*/
        $rootId = $this->user->getRootId();
        foreach($posts as &$post){
            $post['parentid'] = $rootId;
            $post['hasChildren'] = false;
            $post['onClickGotoTab'] = 'edit';
            $post['updated'] =  substr($post['updated'], 0, 10);
            //$post['name'] = $post['name'] . ' (' . substr($post['updated'], 0, 10) . ')';//" ({$this->user->peoplefirstAndLastNameOrUserName($post['updator'])}, {$post['updated']})";
        }
        $posts[] = ['id' => $rootId, 'name' => 'tukos', 'hasChildren' => true];
        return $posts;
    }
}
?>
