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
            $customDataWidgets['adminpwd'] = ViewUtils::textBox($this, 'Admin Password', ['atts' => ['edit' =>  ['type' => 'password']], 'editToObj' => ['encrypt' => ['class' => $this->user, 'shared']]]);
            $this->customize($customDataWidgets, [], ['get' => ['adminpwd'], 'grid' => ['adminpwd']]);
            $this->customContentAtts = [
                'edit' => ['actionLayout' => ['contents' => ['actions' => ['tableAtts' => ['cols' => 7], 'widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit']]]]],
            ];
        }else{
            $this->customize($customDataWidgets);
        }

    }
}
?>
