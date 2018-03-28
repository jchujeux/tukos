<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Help;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parenthelp', 'Description');
        $customDataWidgets = [
            'language'  => ViewUtils::storeSelect('language', $this, 'Language'),
            ];
        $subObjects['help'] = ['atts' => ['title'     => $this->tr('Relatedhelp')], 'filters'   => ['parentid' => '@id'], 'allDescendants' => true];
        $this->customize($customDataWidgets, $subObjects);
        $this->paneWidgets = [
            'userContext' => [
                'title'   => $this->tr('User Context'),
                'paneContent' => [
            		'widgetsDescription' => ['contextid' => Widgets::contextTree(
                    	array_merge($this->user->contextTreeAtts($this->tr), 
                                ['title' => $this->tr('treetitle'),  'urlArgs'   => ['object' => 'help', 'view' => 'pane', 'action' => 'save'], 'userid' => $this->user->id()]
                    	)
                	)],
                	'layout' => [ 'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['contextid' ]],
                	'style' => ['padding' => "0px"], 'id' => 'tukos_userContext'
                ],
                'style' => ['padding' => '0px'],
            ],
/*
        		'search' => [
        		'title' => $this->tr('search'),
        		'paneContent' => [
        			'widgetsDescription' => [
        				'id' => ['type' => 'TextBox', 'atts' => ['label' => $this->tr('search')]],
        				'pattern' => ['type' => 'TextBox', 'atts' => ['label' => $this->tr('pattern')]],
        				'contextid' => Widgets::description($this->dataWidgets['contextid']),
        				'reset' => ['type' => 'TukosButton', 'atts' => ['label' => $this->tr('reset'), 'onClickAction' => 'this.pane.getWidget("tree").reset();']],
        				'search' => ['type' => 'TukosButton', 'atts' => ['label' => $this->tr('save'), 'onClickAction' => 'this.pane.getWidget("tree").save();']],
        				'overview' => ['type' => 'OverviewDgrid', 'atts' => [
        					'label' => $this->tr('founditems'), 'colsDescription' => $this->widgetsDescription(['id', 'name', 'parentid', 'comments', 'updated', 'updator']), 
        					'objectIdCols' => ['id', 'parentid', 'updator'], 'storeArgs' => ['view' => 'overview', 'mode' => 'accordion', action' => 'gridselect'], 'object' => 'tukos', 'style' => ['maxHeight' => '600px']
        				]],
        				'totalrecords' => ['type' => 'TextBox','atts' => ['title' => $this->tr('totalentries'), 'style' => ['width' => '5em'], 'disabled' => true]],
        				'filteredrecords' => ['type' => 'TextBox','atts' => ['title' => $this->tr('totalfilteredentries'), 'style' => ['width' => '5em'], 'disabled' => true]],
        			],
        			'layout' => [
        				'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false], 
        				'contents' => [
        					'row1' => ['tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['id', 'pattern', 'search', 'reset', 'contextid']],
        					'row2' => ['tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['overview']],
        					'row3' => ['tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['filteredrecords', 'totalrecords']],
        				]
        			],
        			'style' => ['padding' => "0px"], 'id' => 'tukos_search'
        		],
        		'style' => ['padding' => "0px"]
      		]
*/
        ];
    }
}
?>
