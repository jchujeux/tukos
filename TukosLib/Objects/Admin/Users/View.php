<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Users;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Widgets;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Associated People', 'Username');
        $modules = Tfk::$registry->get('appConfig')->objectModules;
        $moduleOptions = [];
        foreach ($modules as $module){
            $moduleOptions[$module] = $this->tr($module);
        }
        $customDataWidgets = [
            'password'   => ViewUtils::textBox($this, 'Password', ['atts' => ['edit' =>  ['type' => 'password']], 'editToObj' => ['md5' => []]]),
            'rights'     => ViewUtils::storeSelect('rights', $this, 'Rights'),
            'modules'    => ['type' => 'multiSelect',  
                'atts' => ['edit' =>  ['title' => $this->tr('UnallowedModules'), 'options' => $moduleOptions, 'style' => ['height' => '500px']], 'storeedit' => ['style' => ['height' => '5em']]],
                'objToEdit' => ['json_decode' => [true]], 'editToObj' => ['json_encode' => []]
            ], 
            'language'   => ViewUtils::storeSelect('language', $this, 'Language'),
            'environment'   => ViewUtils::storeSelect('environment', $this, 'Environment'),
            'targetdb' => ViewUtils::textBox($this, 'Targetdb'),
            'tukosorganization' => ViewUtils::textBox($this, 'Tukosorganization', ['atts' => ['disabled' => !$this->user->isAdmin()]]),
            'customviewids' => [
                'type' => 'objectEditor', 
                'atts' => ['edit' => ['title' => $this->tr('Custom views'), 'keyToHtml' => 'capitalToBlank', 'style' => ['maxHeight' =>  '500px', 'maxWidth' => '400px',  'overflow' => 'auto']]],
                'objToEdit' => ['json_decode' => [true],  'map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]],
            ],
            'customcontexts' => ['type' => 'objectEditor',
                'atts' => ['edit' =>  ['title' => $this->tr('Custom contexts'), 'keyToHtml' => 'capitalToBlank', 'style' => ['maxHeight' =>  '500px', 'maxWidth' => '400px', 'overflow' => 'auto']]],
                'objToEdit' => ['json_decode' => [true],  'map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]
            ]],
            'pagecustom' => ['type' => 'objectEditor',    
                 'atts' => ['edit' =>  ['title' => $this->tr('Pagecustom'), 'keyToHtml' => 'capitalToBlank', 'style' => ['maxHeight' =>  '500px', 'maxWidth' => '400px', 'overflow' => 'auto']]],
                'objToEdit' => ['json_decode' => [true],  'map_array_recursive' => ['class' => 'TukosLib\Utils\Utilities', $this->tr]
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
