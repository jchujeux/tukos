<?php
namespace TukosLib\Objects\Collab\Blog;

use TukosLib\Objects\AbstractModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    protected $languageOptions = ['en-us', 'fr-fr','es-es'];
    function __construct($objectName, $translator=null){
        $this->languageOptions = Tfk::$registry->get('appConfig')->languages['supported'];

        $colsDefinition = [
            'language' =>  "ENUM ('" . implode("','", $this->languageOptions) . "')",
            'published' => "timestamp DEFAULT NULL"
        ];
        parent::__construct($objectName, $translator, 'blog', ['parentid' => Tfk::$registry->get('user')->allowedNativeObjects()], [], $colsDefinition, [['language']]);
    }
    function gotoTabString($view, $queryString, $form){
        return     "tukos.Pmg.tabs.gotoTab({object: 'backoffice', view:'{$view}', mode: 'Tab', action: 'Tab', query:{object: 'blog', form: '$form', {$queryString}}});";
    }
    function onClickGotoTabString($view, $queryString){
        return " onclick=\"{$this->gotoTabString($view, $queryString, 'Show')}\"";
    }
    function onClickGotoContactTabString($view, $queryString){
        return " onclick=\"{$this->gotoTabString($view, $queryString, 'Contact')}\"";
    }
    function getRecentPosts(){
        return $this->getPosts([], 5);
    }
    function searchPosts($query, $atts){
        return ['data' => $this->getPosts([[['col' => 'name', 'opr' => 'RLIKE', 'values' => $atts['searchbox']], ['col' => 'comments', 'opr' => 'RLIKE', 'values' => $atts['searchbox'], 'or' => true]]])];
    }
    function getPosts($where, $limit = 1000){
        $posts = $this->getAll(['where' => $this->user->filterPrivate($where), 'cols' => ['id', 'name', 'published'], 'orderBy' => ['published' => 'DESC'], 'limit' => $limit]);
        $rootId = $this->user->getRootId();
        foreach($posts as &$post){
            $post['parentid'] = $rootId;
            $post['hasChildren'] = false;
            $post['onClickGotoTab'] = 'edit';
            $post['published'] =  substr($post['published'], 0, 10);
        }
        $posts[] = ['id' => $rootId, 'name' => 'tukos', 'hasChildren' => true];
        return $posts;
    }
}
?>
