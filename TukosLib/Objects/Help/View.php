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
        parent::__construct($objectName, $translator, 'Parent', 'Description');
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
                                ['title' => $this->tr('treetitle'),  'urlArgs'   => ['object' => 'help', 'view' => 'Pane', 'action' => 'Save'], 'userid' => $this->user->id()]
                    	)
                	)],
                	'layout' => [ 'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false], 'widgets' => ['contextid' ]],
                	'style' => ['padding' => "0px"], 'id' => 'tukos_userContext'
                ],
                'style' => ['padding' => '0px'],
            ],
        ];
    }
}
?>
