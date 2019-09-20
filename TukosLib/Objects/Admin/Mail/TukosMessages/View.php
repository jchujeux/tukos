<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Admin\Mail\TukosMessages; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){

        parent::__construct($objectName, $translator, 'Parent', 'Subject');

        $customDataWidgets = [
            'accountid' =>  ViewUtils::objectSelectMulti('accountid', $this, 'Mail Account', ['atts' => ['edit' => [
                        'style' => ['width' => '13em'],
                        'onChangeServerAction' => [
                            'inputWidgets' => ['accountid'],
                            //'outputWidgets' => ['accountid', 'from', 'mailmessages'],
                            'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getAccountIdChanged'])]],
                        ],
                        'onChangeLocalAction' => [
                            'process'  => ['hidden' =>"var toW =registry.byId(self.form.id+'tos');if (newValue.id === '' || !toW.get('value')){return true;}else{return false;}"],
                        ],
                    ],
                ],
            ]),
            'name'  => ['atts' => ['edit' => ['style' => ['width' => '48em']]]],
            'sender'=> ViewUtils::textBox($this, 'From', ['atts' => ['edit' => ['style' => ['width' => '20em']]]]),
            'tos'  =>  [
                'type' => 'textArea',
                'atts' => ['edit' => [
                        'title' =>$this->tr('To'), 'colspan' => '6',
                        'onChangeLocalAction' => [
                            'process'  => ['hidden' =>"var pW = registry.byId(self.form.id+'parentid');if (!newValue||!pW.get('value').id){return true;}else{return false;}"],
                        ],
                    ],
                ],
            ],
            'ccs'   => ['type' => 'textArea', 'atts' => ['edit' => ['title' => $this->tr('cc'), 'colspan' => '6']]],
            'bccs'  => ['type' => 'textArea', 'atts' => ['edit' => ['title' => $this->tr('bcc'), 'colspan' => '6']]],
            'status'=> ViewUtils::storeSelect('status', $this, 'Status', null, [
                    'atts' => ['edit' =>  [
                            'disabled' => true,
                            'onChangeLocalAction' => [
                                'name'      => ['disabled' => "if(newValue=='sent'){return true;}else{return false}"],
                                'accountid' => ['disabled' => "if(newValue=='sent'){return true;}else{return false}"],
                                'sender'    => ['disabled' => "if(newValue=='sent'){return true;}else{return false}"],
                                'tos'       => ['disabled' => "if(newValue=='sent'){return true;}else{return false}"],
                                'ccs'       => ['disabled' => "if(newValue=='sent'){return true;}else{return false}"],
                                'bccs'      => ['disabled' => "if(newValue=='sent'){return true;}else{return false}"],
                                'smtpserverid'=>['disabled'=> "if(newValue=='sent'){return true;}else{return false}"],
                                'comments'  => ['disabled' => "if(newValue=='sent'){return true;}else{return false}"],
                                'save'      => ['hidden'   => "if(newValue=='sent'){return true;}else{return false}"],
                                'process'   => ['hidden'   => "if(newValue=='sent'){return true;}else{return false}"],
                            ],
                        ],
                    ],
                    'objToOverview' => ['tr' => ['class' => $this]]
                ]
            ),
            'statusdate'   => ViewUtils::timeStampDataWidget($this, 'Status date', ['atts' => ['edit' => ['disabled' => true]]]),
            'smtpserverid' => ViewUtils::objectSelectMulti('smtpserverid', $this, 'Smtp server'),

            'comments'  =>  ['atts' => ['edit' =>  ['title' => $this->tr('Message'), 'height' => '400px']],],
        ];

        $subObjects['mailtukosmessages'] = ['atts' => ['title' => $this->tr('MailMessages'), 'maxHeight' => '200px'],
                                          'view' => $this,
                                       'filters' => ['parentid' => '@parentid'],
                                'allDescendants' => true];

        $this->customize($customDataWidgets, $subObjects);
        $this->customContentAtts = [
            'edit' => [
                'actionLayout' => ['contents' => ['actions' => ['tableAtts' => ['cols' => 7], 'widgets' => [ 'save', 'reset', 'delete', 'duplicate', 'new', 'edit', 'process']]]],
                'widgetsDescription' => ['process' => ['atts' => ['label' => $this->tr('send')]]],
            ],
        ];
    }    
}
?>
