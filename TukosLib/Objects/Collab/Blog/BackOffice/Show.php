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
        //$this->peopleModel = $this->objectsStore->objectModel('people');
        $this->view  = $this->objectsStore->objectView('blog');
        $this->dataWidgets = [
            'posttitle' => ViewUtils::htmlContent($this->view, 'postTitle', ['atts' => ['edit' => ['widgetCellStyle' => ['backgroundColor' => '#d0e9fc']]]]),
            'postedbyandwhen' => ViewUtils::htmlContent($this->view, 'postedby',  ['atts' => ['edit' => ['widgetCellStyle' => ['textAlign' =>'right', 'backgroundColor' => '#d0e9fc']]]]),
            'comments' => ViewUtils::htmlContent($this->view, 'post', ['atts' => ['edit' => ['style' => ['backgroundColor' => 'white', 'color' => 'black', 'paddingTop' => '1em', 'paddingTop' => '1em']]]]),
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
        $values = $this->blogModel->getOne(['where' => $query, 'cols' => ['id', 'parentid', 'name', 'comments', 'updated', 'updator']]);
/*
        $peopleId = $this->usersModel->getOne(['where' => ['id' => $values['updator']], 'cols' => ['parentid']])['parentid'];
        if(empty($peopleId)){
            $updator = $this->user->userName();
        }else{
            $people = $this->peopleModel->getOne(['where' => ['id' => $peopleId], 'cols' => ['name', 'firstname']]);
            $updator = "{$people['firstname']} {$people['name']}";
        }
*/
        $updator = $this->user->peoplefirstAndLastNameOrUserName($values['updator']);
        return ['id' => $values['id'], 'name' => $values['name'],  'posttitle' => "<h2>{$values['name']}</h2>", 'postedbyandwhen' => "<i>{$this->view->tr('postedby')}</i>: $updator <i>{$this->view->tr('postedon')}</i> " . DUtl::toUTC($values['updated']), 'comments' => $values['comments']];
    }
    function save($query, $valuesToSave){
        return false;
    }
    function getToTranslate(){
        return $this->view->getToTranslate();
    }
}
?>
