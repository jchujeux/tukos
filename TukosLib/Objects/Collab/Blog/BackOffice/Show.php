<?php
namespace TukosLib\Objects\Collab\Blog\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
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
        Utl::extractItems(['object', 'form'], $query);
        $values = $this->blogModel->getOne(['where' => $this->user->filterPrivate($query), 'cols' => ['id', 'parentid', 'name', 'comments', 'published', 'updated', 'creator', 'updator'], 'orderby' => ['blog.published DESC']]);
        $publisher = $this->user->peoplefirstAndLastNameOrUserName($values['creator']);
        $postedByAndWhen = "<i>{$this->view->tr('postedby')}</i>: $publisher <i>{$this->view->tr('postedon')}</i> " . DUtl::toUTC($values['published']) . '<br><a href="' . $_SERVER['SCRIPT_URI'] . '?id=' . $values['id'] . '" target="_blank">direct link</a>';
        if ($values['updated'] > $values['published']){
            $updator = $this->user->peoplefirstAndLastNameOrUserName($values['updator']);
            $postedByAndWhen .= "&nbsp;&nbsp;&nbsp;&nbsp;<i>{$this->view->tr('updatedon')}</i> " . DUtl::toUTC($values['updated']);
        }
        return ['id' => $values['id'], 'name' => $values['name'],  'posttitle' => '<h2 style="margin-bottom: 0px; margin-top: 0px;">' . $values["name"] . '</h2>', 'postedbyandwhen' => $postedByAndWhen, 'comments' => $values['comments']];
    }
    function save($query, $valuesToSave){
        return false;
    }
    function getToTranslate(){
        return $this->view->getToTranslate();
    }
}
?>
