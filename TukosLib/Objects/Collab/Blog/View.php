<?php
namespace TukosLib\Objects\Collab\Blog;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'language'  => ViewUtils::storeSelect('language', $this, 'Language'),
            'published'   => ViewUtils::dateTimeBoxDataWidget($this, 'PublishedDate'),
        ];
        $this->customize($customDataWidgets);
        $this->paneWidgets = [
            'rightPane' => [
                //'title'   => $this->tr('User Context'),
                'paneContent' => [
                    'widgetsDescription' => [
                        //'recentposts' => Widgets::htmlContent(['title' => $this->tr('recentposts'),  'widgetCellStyle' => ['backgroundColor' => '#d0e9fc'], 'value' => $this->model->getRecentPosts()]),
                        'recentposts' => Widgets::storeTree(['title' => $this->tr('recentposts'), 'colInLabel' => 'updated', 'showRoot' => false,  'noDnd' => true, 'widgetCellStyle' => ['backgroundColor' => '#d0e9fc', 'color' => 'black'], 'parentProperty' => 'parentid', 'parentDataProperty' => 'parentid',
                            'style' => ['overflow' => 'visible'],  'onClickAction' => $this->onClickAction(),  'storeArgs' => ['data' => $this->model->getRecentPosts()], 'root' => $this->user->getRootId(), 'openOnClick' => true]),
                        'categories' => Widgets::storeTree(Utl::array_merge_recursive_replace(['title' => $this->tr('categories'), 'colInLabel' => false, 'showRoot' => false,  'noDnd' => true, 'widgetCellStyle' => ['backgroundColor' => '#d0e9fc', 'color' => 'black'], 'parentProperty' => 'contextid',
                            'parentDataProperty' => 'parentid', 'style' => ['overflow' => 'visible'],  'onClickAction' => $this->onClickAction(),  
                            'storeArgs' => ['object' => 'BackOffice', 'view' => 'NoView', 'mode' => 'Pane', 'action' => 'Get', 'params' => ['actionModel' => 'GetItems', 'object' => 'Blog', 'form' => 'GetItems']], 'openOnClick' => true],
                            $this->user->contextTreeAtts($this->tr, true))),
                        'searchbox' => ['type' => 'SearchTextBox', 'atts' => Widgets::complete(['title' => $this->tr('pattern'), 'style' => ['width' => '10em'], 'searchAction' => $this->searchAction('this.pane')])],
                        'searchresults' => Widgets::storeTree(['title' => $this->tr('searchresults'), 'colInLabel' => 'updated', 'showRoot' => false,  'noDnd' => true, 'hidden' => true, 'widgetCellStyle' => ['backgroundColor' => '#d0e9fc', 'color' => 'black'], 'parentProperty' => 'parentid',
                            'parentDataProperty' => 'parentid', 'style' => ['overflow' => 'visible'],  'onClickAction' => $this->onClickAction(),  'storeArgs' => ['data' => [['id' => 1, 'hasChildren' => true, 'name' => 'tukos']]], 'root' => $this->user->getRootId(), 'openOnClick' => true])
                    ],
                    'layout' => [
                        'tableAtts' => ['cols' => 1,  'customClass' => 'labelsAndValues', 'showLabels' => false, 'resizeOnly' => true],
                        'contents' => [
                            'row1' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['tableLayout' => 'fixed'], 'resizeOnly' => true],
                                'widgets' => [ 'recentposts' ]
                            ],
                            'row2' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['tableLayout' => 'fixed'], 'resizeOnly' => true],
                                'widgets' => ['categories' ]
                            ],
                            'row3' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert', 'style' => ['tableLayout' => 'fixed'], 'resizeOnly' => true],
                                'widgets' => ['searchbox']
                            ],
                            'row4' => [
                                'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert', 'style' => ['tableLayout' => 'fixed'], 'resizeOnly' => false],
                                'widgets' => [ 'searchresults' ]
                            ],
                        ]
                    ],
                    'style' => ['padding' => "0px"]
                ],
            ],
        ];
    }
    function onClickAction(){
        if (Tfk::$registry->isMobile){
            return <<<EOT
var form = this.pane.form, onBlur = this.onBlur;
if (item.onClickGotoTab){
    Pmg.tabs.gotoTab({action: 'Tab', mode: 'Tab', object: 'backoffice', view: 'edit', query: {form: 'Show', object: 'blog', name: item.name}}).then(function(){
        onBlur && onBlur();
        window.scrollTo(0,0);
    });
}
EOT
            ;
        }else{
            return <<<EOT
if (item.onClickGotoTab){
    Pmg.tabs.gotoTab({object: 'backoffice', view:item.onClickGotoTab, mode: 'Tab', action: 'Tab', query:{object: 'blog', form: 'Show', name: item.name}});
}
EOT
            ;
        }
    }
    function searchAction($pane){
        return <<<EOT
var pane = $pane, searchResults = pane.getWidget('searchresults');
pane.serverAction({action: 'Process', object: 'Blog', view: 'Edit', query: {params: {process: 'searchPosts', noget: true}}}, {includeWidgets: ['searchbox']}).then(function(response){
    searchResults.set('hidden', false);
    searchResults.model.store.setData(response.data);     
    searchResults.update();
    pane.resize();
    if (pane.focusOnResults){
        focusUtil.focus(searchResults.domNode);
    }
    return true;
});
EOT
        ;
    }
}
?>
