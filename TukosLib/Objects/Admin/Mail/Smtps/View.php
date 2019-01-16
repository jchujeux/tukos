<?php
/**
 *
 * class for viewing methods and properties for the $users model object
 */
namespace TukosLib\Objects\Admin\Mail\Smtps;

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){
        parent::__construct($objectName, $translator, 'Parent', 'Description');

        $customDataWidgets = [
            'hostname'  => ViewUtils::textBox($this, 'Host name', ['atts' => ['edit' =>  ['placeHolder' => 'xxx.yyy.zzz']]]),
            'port'      => ViewUtils::storeSelect('port', $this, 'Port'),
            'security'  => ViewUtils::storeSelect('security', $this, 'Security'),
            'auth'      => ViewUtils::storeSelect('auth', $this, 'Auth'),
            'smtpuser'  => ViewUtils::textBox($this, 'Smtp user'),
            'smtppwd'   => ViewUtils::textBox($this, 'Smtp Password', ['atts' => ['edit' =>  ['type' => 'password']], 'editToObj' => ['encrypt' => ['class' => $this->user, 'shared']]]),
        ];            ;
        $this->customize($customDataWidgets, [], ['get' => ['smtppwd'], 'grid' => ['smtppwd']]);
    }
}
?>
