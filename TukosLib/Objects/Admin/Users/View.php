<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Users;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\Directory;
use TukosLib\Utils\Widgets;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        $utl = 'TukosLib\Utils\Utilities';
        parent::__construct($objectName, $translator, 'Associated People', 'Username');
        $modules = Directory::getObjs();
        $moduleOptions = [];
        foreach ($modules as $module){
            $moduleOptions[$module] = $this->tr($module);
        }
        $customDataWidgets = [
            'parentid' => ['atts' => ['edit' => ['storeArgs' => ['cols' => ['email']], 'onChangeLocalAction' => ['name' => ['value' => "return sWidget.getItemProperty('email');"]]]]],
            'password'   => ViewUtils::textBox($this, 'Password', ['atts' => ['edit' =>  ['type' => 'password', 'hidden' => true]], 'editToObj' => ['md5' => []]]),
            'rights'     => ViewUtils::storeSelect('rights', $this, 'Rights'),
            'modules'    => ['type' => 'multiSelect',
                'atts' => ['edit' =>  ['title' => $this->tr('UnallowedModules'), 'options' => $moduleOptions, 'style' => ['height' => '500px']], 'storeedit' => ['style' => ['height' => '5em']]]
            ],
            'restrictedmodules'    => ['type' => 'multiSelect',
                'atts' => ['edit' =>  ['title' => $this->tr('RestrictedModules'), 'options' => $moduleOptions, 'style' => ['height' => '500px']], 'storeedit' => ['style' => ['height' => '5em']]]
            ],
            'language'   => ViewUtils::storeSelect('language', $this, 'Language'),
            'environment'   => ViewUtils::storeSelect('environment', $this, 'Environment'),
            'targetdb' => ViewUtils::textBox($this, 'Targetdb'),
            'tukosorganization' => ViewUtils::textBox($this, 'Tukosorganization', ['atts' => ['disabled' => !$this->user->isAdmin()]]),
            'dropboxaccesstoken'   => ViewUtils::textBox($this, 'Dropboxaccesstoken', ['atts' => ['edit' =>  ['type' => 'password']], 'editToObj' => ['encrypt' => ['class' => $this->user, 'private']]]),
            'dropboxbackofficeaccess' => ViewUtils::storeSelect('yesOrNo', $this, 'dropboxbackofficeaccess'),
            'enableoffline' => ViewUtils::storeSelect('yesOrNo', $this, 'enableoffline'),
            'customviewids' => [
                'type' => 'objectEditor', 
                'atts' => ['edit' => ['title' => $this->tr('Custom views'), 'keyToHtml' => 'capitalToBlank', 'hasCheckboxes' => true, 'isEditTabWidget' => true, 
                    'style' => ['maxHeight' =>  '500px', 'maxWidth' => '400px',  'overflow' => 'auto']]],
//                'objToEdit' => ['jsonDecode' => ['class' => $utl],  'map_array_recursive' => ['class' => $utl, $this->tr]],
            ],
            'customcontexts' => ['type' => 'objectEditor',
                'atts' => ['edit' =>  ['title' => $this->tr('Custom contexts'), 'keyToHtml' => 'capitalToBlank'/*, 'hasCheckboxes' => true, 'isEditTabWidget' => true*/,
                    'style' => ['maxHeight' =>  '500px', 'maxWidth' => '400px', 'overflow' => 'auto']]],
                'objToEdit' => ['jsonDecode' => ['class' => $utl],  'map_array_recursive' => ['class' => $utl, $this->tr]
            ]],
            'pagecustom' => ['type' => 'objectEditor',    
                'atts' => ['edit' =>  ['title' => $this->tr('Pagecustom'), 'keyToHtml' => 'capitalToBlank'/*, 'hasCheckboxes' => true, 'isEditTabWidget' => true*/, 
                    'style' => ['maxHeight' =>  '500px', 'width' => '100%', 'overflow' => 'auto']]],
                'objToEdit' => ['jsonDecode' => ['class' => $utl],  'map_array_recursive' => ['class' => $utl, $this->tr]
            ]],
        ];

        $this->customize($customDataWidgets, []/*$subObjects*/, ['get' => ['password'], 'grid' => ['password']]);
        
        $this->paneWidgets = [
            'log' => [
                'title' => $this->tr('Activitylog'),
                	'paneContent' => [
            			'widgetsDescription' =>[ 'log' => Widgets::textArea(['title' => $this->tr('Activitylog'), 'style' => ['maxHeight' => '20em'], 'readonly' => true]),],
                		'layout' => ['widgets' => ['log']],
                		'style' => ['padding' => "0px"], 'id' => 'tukos_log'
                	],
                'style' => ['padding' => '0px']
            ],
        ];
    }    
}
?>
