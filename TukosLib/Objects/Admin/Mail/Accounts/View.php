<?php
namespace TukosLib\Objects\Admin\Mail\Accounts; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){

        parent::__construct($objectName, $translator, 'Associated User', 'Displayed Name');

        $customDataWidgets = [
            'eaddress'  => ViewUtils::textBox($this, 'Electronic address', ['atts' => ['edit' => ['placeHolder' => 'xxx@yyx.zzx']]]),
            'username'  => ViewUtils::textBox($this, 'Username'),
            'password'  => ViewUtils::textBox($this, 'Password', [/* when password changes we want to send current 'privacy' as well, hence the onChangeLocalAction'*/
                    'atts' => ['edit' =>  ['type' => 'password', 'onChangeLocalAction' => ['privacy' => ['hasChanged' => "tWidget.form.changedWidgets[tWidget.widgetName] = tWidget; return true;"]]]],
                    'editToObj' => ['encrypt' => ['class' => $this->user, '@privacy']],
                ]
            ),
            'privacy' => ViewUtils::storeSelect('privacy', $this, 'Privacy'),
            'smtpserverid' => ViewUtils::objectSelectMulti('smtpserverid', $this, 'Smtp server'),
            'mailserverid' => ViewUtils::objectSelectMulti('mailserverid', $this, 'Mail server'),
            'draftsfolder' => ViewUtils::textBox($this, 'Drafts Folder'),
        ];


        $subObjects = [];
        //$subObjects['mailboxes'] = ['atts' => ['title' => $this->tr('Mailboxes'), 'disabled' => true], 'filters' => ['parentid' => '@id'], 'allDescendants' => false];

        $this->customize($customDataWidgets, $subObjects, ['get' => ['password'], 'grid' => ['password']]);


        if ($this->user->isAtLeastAdmin()){
            $this->customContentAtts = [
                'edit' => ['actionLayout' => ['contents' => ['actions' => ['tableAtts' => ['cols' => 7], 'widgets' => ['save', 'reset', 'delete', 'duplicate', 'new', 'edit']]]]],
            ];
        }
    }    

    function overviewProcess($idsToProcess){
        return $this->model->process($idsToProcess);
    }


}
?>
