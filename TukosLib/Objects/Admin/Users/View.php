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
use TukosLib\Objects\ObjectTranslator;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        $utl = 'TukosLib\Utils\Utilities';
        parent::__construct($objectName, $translator, 'Associated People', 'Username');
        /*$modules = Directory::getObjs();
        $moduleOptions = [];
        foreach ($modules as $module){
            $moduleOptions[$module] = $this->tr($module);
        }*/
        $untranslator = new ObjectTranslator($objectName, null, 'untranslator');
        $moduleOptions = Directory::getTranslatedModules();
        $customDataWidgets = [
            'parentid' => ['atts' => ['edit' => ['storeArgs' => ['cols' => ['email']], 'onChangeLocalAction' => ['name' => ['value' => "return sWidget.getItemProperty('email');"]]]]],
            'password'   => ViewUtils::textBox($this, 'Password', ['atts' => ['edit' =>  ['type' => 'password', 'hidden' => true]], 'editToObj' => ['md5' => []]]),
            'rights'     => ViewUtils::storeSelect('rights', $this, 'Usertype'),
            'modules'    => ['type' => 'multiSelect',
                'atts' => ['edit' =>  ['title' => $this->tr('UnallowedModules'), 'options' => $moduleOptions, 'style' => ['height' => '500px']], 'storeedit' => ['style' => ['height' => '5em']]]
            ],
            'restrictedmodules'    => ['type' => 'multiSelect',
                'atts' => ['edit' =>  ['title' => $this->tr('RestrictedModules'), 'options' => $moduleOptions, 'style' => ['height' => '500px']], 'storeedit' => ['style' => ['height' => '5em']]]
            ],
            'language'   => ViewUtils::storeSelect('language', $this, 'Language'),
            'environment'   => ViewUtils::storeSelect('environment', $this, 'Environment'),
            'targetdb' => ViewUtils::textBox($this, 'Targetdb'),
            'tukosorganization' => ViewUtils::textBox($this, 'Tukosorganization'),
            'dropboxaccesstoken'   => ViewUtils::textBox($this, 'Dropboxaccesstoken', ['atts' => ['edit' =>  ['type' => 'password']], 'editToObj' => ['encrypt' => ['class' => $this->user, 'private']]]),
            'dropboxbackofficeaccess' => ViewUtils::storeSelect('yesOrNo', $this, 'dropboxbackofficeaccess'),
            'googletranslationaccesskey'   => ViewUtils::textBox($this, 'Googletranlationaccesskey', ['atts' => ['edit' =>  ['type' => 'password']], 'editToObj' => ['encrypt' => ['class' => $this->user, 'private']]]),
            'enableoffline' => ViewUtils::storeSelect('yesOrNo', $this, 'enableoffline'),
            'customviewids' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('Custom views'), 'keyToHtml' => 'capitalToBlank', 'hasCheckboxes' => true, 'isEditTabWidget' => true, 'checkedServerValue' => '~delete', 'onCheckMessage' => $this->tr('checkedleaveswillbedeletedonsave'),
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto']]],
                'objToEdit' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
                'editToObj' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $untranslator->tr]],
            ],
            'customcontexts' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('Custom contexts'), 'keyToHtml' => 'capitalToBlank', 'hasCheckboxes' => true, 'isEditTabWidget' => true, 'checkedServerValue' => '~delete', 'onCheckMessage' => $this->tr('checkedleaveswillbedeletedonsave'),
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto']]],
                'objToEdit' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
                'editToObj' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $untranslator->tr]],
            ],
            'pagecustom' => [
                'type' => 'objectEditor',
                'atts' => ['edit' => ['title' => $this->tr('Pagecustom'), 'keyToHtml' => 'capitalToBlank', 'hasCheckboxes' => true, 'isEditTabWidget' => true, 'checkedServerValue' => '~delete', 'onCheckMessage' => $this->tr('checkedleaveswillbedeletedonsave'),
                    'style' => ['maxHeight' =>  '500px'/*, 'maxWidth' => '400px'*/,  'overflow' => 'auto']]],
                'objToEdit' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
                'editToObj' => ['map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $untranslator->tr]],
            ],
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
