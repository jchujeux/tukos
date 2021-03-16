<?php
namespace TukosLib\Objects\Collab\Blog;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'language'  => ViewUtils::storeSelect('language', $this, 'Language'),
            ];
        $this->customize($customDataWidgets);
        $this->paneWidgets = [
            'rightPane' => [
                'title'   => $this->tr('User Context'),
                'paneContent' => [
                    'widgetsDescription' => [
                        //'recentposts' => Widgets::htmlContent(['title' => $this->tr('recentposts'),  'widgetCellStyle' => ['backgroundColor' => '#d0e9fc'], 'value' => $this->model->getRecentPosts()]),
                        'recentposts' => Widgets::storeTree(['title' => $this->tr('recentposts'), 'colInLabel' => 'updated', 'showRoot' => false,  'widgetCellStyle' => ['backgroundColor' => '#d0e9fc'], 'parentProperty' => 'parentid', 'parentDataProperty' => 'parentid',
                            'style' => ['overflow' => 'visible'],  'onClickAction' => $this->onClickAction(),  'storeArgs' => ['data' => $this->model->getRecentPosts()], 'root' => $this->user->getRootId()]),
                    'categories' => Widgets::storeTree(Utl::array_merge_recursive_replace(['title' => $this->tr('categories'), 'colInLabel' => false, 'showRoot' => false,  'widgetCellStyle' => ['backgroundColor' => '#d0e9fc'], 'parentProperty' => 'contextid', 'parentDataProperty' => 'parentid',
                        'style' => ['overflow' => 'visible'],  'onClickAction' => $this->onClickAction(),  'storeArgs' => ['object' => 'BackOffice', 'view' => 'NoView', 'mode' => 'Pane', 'action' => 'Get', 'params' => ['actionModel' => 'GetItems', 'object' => 'Blog', 'form' => 'GetItems']]],
                        $this->user->contextTreeAtts($this->tr, true)))
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
                            ]
                        ]
                    ],
                    'style' => ['padding' => "0px"]
                ],
            ],
        ];
    }
    function onClickAction(){
        return <<<EOT
if (item.onClickGotoTab){
    Pmg.tabs.gotoTab({object: 'backoffice', view:item.onClickGotoTab, mode: 'Tab', action: 'Tab', query:{object: 'blog', form: 'Show', name: item.name}});
}
EOT
        ;
    }
}
?>
