<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Mail\Servers;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');

        $customDataWidgets = [
            'hostname'  => ViewUtils::textBox($this, 'Host name', ['atts' => ['edit' =>  ['placeHolder' => 'xxx.yyy.zzz']]]),
            'protocol' => ViewUtils::storeSelect('protocol', $this, 'Protocol'),
            'port'     => ViewUtils::storeSelect('port', $this, 'Port'),
            'security' => ViewUtils::storeSelect('security', $this, 'Security'),
            'auth'     => ViewUtils::storeSelect('auth', $this, 'Auth'),
        ];
        if ($this->user->isSuperAdmin()){
            $tukosMailHost = Tfk::$registry->get('appConfig')->mailConfig['host'];
            $customDataWidgets['software'] = ViewUtils::storeSelect('software', $this, 'Software');
            $customDataWidgets['adminpwd'] = ViewUtils::textBox($this, 'Admin Password', ['atts' => ['edit' =>  ['type' => 'password']], 'editToObj' => ['encrypt' => ['class' => $this->user, 'shared']]]);
            $customDataWidgets['hostname']['atts']['edit']['onChangeLocalAction'] = [
                'software' => ['hidden' =>"if (newValue === '" . $tukosMailHost . "'){return false;}else{return true}"],
                'adminpwd' => ['hidden' =>"if (newValue === '" . $tukosMailHost . "'){return false;}else{return true}"],
                'process'  => ['hidden' =>"if (newValue === '" . $tukosMailHost . "'){return false;}else{return true}"],
            ];
            $this->customize($customDataWidgets, [], ['get' => ['adminpwd'], 'grid' => ['software', 'adminpwd']]);
            $this->customContentAtts = [
                'edit' => ['actionLayout' => ['contents' => ['actions' => ['tableAtts' => ['cols' => 7], 'widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit', 'process']]]]],
            ];
        }else{
            $this->customize($customDataWidgets);
        }

    }
}
?>
