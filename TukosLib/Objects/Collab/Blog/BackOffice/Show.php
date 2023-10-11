<?php
namespace TukosLib\Objects\Collab\Blog\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\Utils\DateTimeUtilities as DUtl;

class Show extends ObjectTranslator{
    function __construct($query){
        parent::__construct('blog');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->blogModel = $this->objectsStore->objectModel('blog');
        $this->usersModel = $this->objectsStore->objectModel('users');
        $this->view  = $this->objectsStore->objectView('blog');
        $this->dataWidgets = [
            'posttitle' => ViewUtils::htmlContent($this->view, 'postTitle', ['atts' => ['edit' => ['widgetCellStyle' => ['backgroundColor' => '#d0e9fc'], 'style' => ['color' => 'black']]]]),
            'postedbyandwhen' => ViewUtils::htmlContent($this->view, 'postedby',  ['atts' => ['edit' => ['widgetCellStyle' => ['textAlign' =>'right', 'backgroundColor' => '#d0e9fc'], 'style' => ['fontSize' => 'smaller', 'color' => 'black']]]]),
            'comments' => ViewUtils::htmlContent($this->view, 'post', ['atts' => ['edit' => ['style' => ['backgroundColor' => 'white', 'color' => 'black', 'paddingTop' => '1em']]]]),
        ];
        $this->dataElts = array_keys($this->dataWidgets);
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => false, 'spacing' => 0, 'widgetCellStyle' => ['backgroundColor' => '#d0e9fc']],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false/*, 'style' => ['tableLayout' => 'fixed']*/, 'resizeOnly' => true],
                    'widgets' => ['posttitle']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'style' => ['tableLayout' => 'fixed'], 'resizeOnly' => true],
                    'widgets' => ['postedbyandwhen']
                ],
                'row3' =>[
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'style' => ['tableLayout' => 'fixed'], 'resizeOnly' => true],
                    'widgets' => ['comments']
                ],
            ]
        ];
        $this->actionLayout = false;
    }
    function getActionWidgets($query){
        return [];
    }
    function getTitle(){
        return [];
    }
    function sendOnSave(){
        return [];
    }
    function sendOnReset(){
        return [];
    }
    function get($query){
        $directLinkTemplate = '<a nohref="" onclick="parent.tukos.Pmg.editorGotoUrl(\'' . Tfk::$registry->blogUrl . '/post?id=${id}\', event)" style="color:blue; cursor:pointer; text-decoration:underline;" target="_blank">${linkText}</a>';
        Utl::extractItems(['object', 'form'], $query);
        $values = $this->blogModel->getOne(['where' => $this->user->filterPrivate($query), 'cols' => ['id', 'parentid', 'name', 'comments', 'published', 'updated', 'creator', 'updator'], 'orderby' => ['blog.published DESC']]);
        if (empty($values) && $name = Utl::getItem('name', $query)){
            $values = $this->blogModel->getOne(['where' => $this->user->filterPrivate([['col' => 'name', 'opr' => 'RLIKE', 'values' => $name]]), 'cols' => ['id', 'parentid', 'name', 'comments', 'published', 'updated', 'creator', 'updator'], 'orderby' => ['blog.published DESC']]);
        }
        if (!empty($values)){
            $blogModel = Tfk::$registry->get('objectsStore')->objectModel('blog');
            $publisherName = $this->user->peoplefirstAndLastNameOrUserName($values['creator']);
            $subject = addslashes("article: {$values['name']} ({$values['id']})"); 
            $directLink = Utl::substitute($directLinkTemplate, ['id' => $values['id'], 'linkText' => $this->view->tr('directlink')]);
            $onContactClickString = $blogModel->onClickGotoContactTabString('edit', "sendto: '$publisherName', formtitle: 'postcontactform', formexplanation: 'postcontactformexplanation', creator: '{$values['creator']}', subject: '$subject'");
            $publisher = '<span style="' . HUtl::urlStyle() . '" ' . $onContactClickString . ">{$publisherName}</span>";
            $postedByAndWhen = "<i>{$this->view->tr('postedby')}</i>: $publisher <i>{$this->view->tr('postedon')}</i> " . DUtl::toUTC($values['published']) . '<br>' . $directLink .
                '&nbsp; (id: ' . $values['id'] . ')';
            if ($values['updated'] > $values['published']){
                //$updator = $this->user->peoplefirstAndLastNameOrUserName($values['updator']);
                $postedByAndWhen .= "&nbsp;&nbsp;<i>{$this->view->tr('updatedon')}</i> " . DUtl::toUTC($values['updated']);
            }
            return ['id' => $values['id'], 'name' => $values['name'],  'posttitle' => '<h2 style="margin-bottom: 0px; margin-top: 0px;">' . $values["name"] . '</h2>', 'postedbyandwhen' => $postedByAndWhen, 'comments' => $values['comments']];
        }else{
            $postNotFound = $this->view->tr('postnotfound');
            return ['id' => 0, 'name' => '', 'posttitle' => $postNotFound, 'postedbyandwhere' => '', 'comments' => ''];
        }
    }
    function save($query, $valuesToSave){
        return false;
    }
    function getToTranslate(){
        return $this->view->getToTranslate();
    }
}
?>
