<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Collab\Teams;

use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');
        $customDataWidgets = [
            'name'      => ['atts' => ['edit' =>  ['style' => ['width' => '20em']]]],
            'leader'    => ViewUtils::objectSelectMulti('leader', $this, 'Leader'),
            'emailcontact' => ViewUtils::textBox($this, 'email'),
            'telcontact' => ViewUtils::textBox($this, 'Phone contact'),
        ];
        $subObjects['objrelations'] = [
            'atts' => [
                'title' => $this->tr('Team members'),
                'colsDescription' => [
                    'relatedid' => ViewUtils::objectSelect($this, 'Team member', 'people'),
                    'name'      => ['atts' => ['storeedit' => ['hidden' => true]]],
                    'parentid'  => ['atts' => ['storeedit' => ['hidden' => true]]],
                ]
            ],
            'initialRowValue' => ['name' => 'belongsTo'],
            'filters' => ['parentid' => '@id'],
            'allDescendants' => true,
        ];
        $subObjects['teams'] = ['atts' => ['title' => $this->tr('Sub teams')], 'filters' => ['parentid' => '@id'], 'allDescendants' => true,];
        $this->customize($customDataWidgets, $subObjects);
    }    
}
?>
