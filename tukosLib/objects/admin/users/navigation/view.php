<?php
namespace TukosLib\Objects\Admin\Users\Navigation;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Associated User', 'Description');
        $this->customize();
        $this->paneWidgets = [
        	'navigationTree' => [
        		'title'   => $this->tr('Objects Navigator'),
        		'id'      => 'accordion_navigator',
        		'paneContent' => [
        			'widgetsDescription' => [
        				'reset' => ['type' => 'TukosButton', 'atts' => ['label' => $this->tr('reset'), 'onClickAction' => 'this.pane.getWidget("tree").reset();']],
        				'save' => ['type' => 'TukosButton', 'atts' => ['label' => $this->tr('save'), 'onClickAction' => 'this.pane.getWidget("tree").save();']],
        				'contextid' => Widgets::description($this->dataWidgets['contextid']),
        				'tree' => Widgets::navigationTree(['id'    => 'tree', 'storeArgs' => ['object' => 'navigation', 'view' => 'pane', 'action' => 'get'], 'showRoot' => false])
        			],
        			'layout' => [
        				'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false], 
        				'contents' => [
        					'row1' => ['tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['reset', 'save', 'contextid']],
        					'row2' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['tree']]
        				]
        			],
        			'style' => ['padding' => "0px", 'overflow' => 'auto']
        		],
        	]
        ];
    }    
}
?>
