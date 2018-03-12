<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Users\Navigation;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Associated User', 'Description');
        $customDataWidgets = [
        ];
        $this->customize($customDataWidgets);

        $this->paneWidgets = [
            'navigationTree' => [
                'title'   => $this->tr('Objects Navigator'),
                'id'      => 'accordion_navigator',
            		'paneContent' => [
               			'widgetsDescription' => [
                      		'treehandle' => Widgets::navigationTree([
                        	'content'  => $this->tr('ClickAnywhere'),
                        	'postClickContent' => '',
                        	'resetButtonArgs'  => ['label' => $this->tr('Reset')],
                        		'widgetArgs' => [
                            		'id'    => 'tree',
                            		'storeArgs' => ['object' => 'navigation', 'view' => 'pane', 'action' => 'get'],
                            		'showRoot' => false,
                        		],
                    		])
                	],
               		'layout' => [ 'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['treehandle' ]],
                	'style' => ['padding' => "0px"], 'id' => 'tukos_navigator'
            		],
               //'style' => ['padding' => "0px"]
            ]
        ];

    }    
}
?>
