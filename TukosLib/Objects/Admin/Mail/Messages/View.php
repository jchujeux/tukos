<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Admin\Mail\Messages; 

use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Widgets;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends AbstractView {

    function __construct($objectName, $translator=null){

        parent::__construct($objectName, $translator, 'Mail account', 'Subject');

        $this->dataWidgets = [
            'id' => ViewUtils::textBox($this, 'Id', [
                    'atts' => ['edit' =>  ['style' => ['width' => '12em'],],
                        'storeedit' => ['onClickFilter' => ['parentid', 'mailboxname', 'uid'], 'rowsFilters' => Utl::idsNamesStore(['Contains'], $this->tr)],
                         'overview' => ['onClickFilter' => ['parentid', 'mailboxname', 'uid']],
                    ],
                ]
            ),
            'parentid'  => ViewUtils::objectSelectMulti('parentid', $this, 'MailAccount', [
                    'atts' => ['edit' => [
                            'onChangeServerAction' => [
                                'inputWidgets' => ['parentid'],
                                //'outputWidgets' => ['parentid', 'from', 'mailmessages'],
                                'urlArgs' => ['query' => ['params' => json_encode(['getOne' => 'getAccountIdChanged'])]],
                            ],
                            'onChangeLocalAction' => [
                                'save'  => ['hidden' =>"if(newValue.id === ''){return true;}else{return false;}"],
                                'send'  => ['hidden' =>"var toW =registry.byId(self.form.id+'to');if (newValue.id === '' || !toW.get('value')){return true;}else{return false;}"],
                            ],
                        ],
                    ],
                ]
            ),
            'mailboxname' =>  [
                'type' => 'textBox',
                'atts' => [
                    'edit' => [
                        'title' =>$this->tr('Mailbox'), 'style' => ['width' => '10em'],
                    ],
                    'storeedit' => ['onClickFilter' => ['parentid', 'mailboxname']],
                    'overview' => ['onClickFilter' => ['parentid', 'mailboxname']],
                ],
            ],
            'name'  => ViewUtils::textBox($this, 'Mailbox', [
                    'atts' => [
                        'edit' => ['style' => ['width' => '20em']],
                        'storeedit' => ['onClickFilter' => ['parentid', 'mailboxname', 'uid'], 'rowsFilters' => Utl::idsNamesStore(['Contains'], $this->tr)],
                        'overview' => ['onClickFilter' => ['parentid', 'mailboxname', 'uid']],
                    ],
                ]
            ),
            'from' =>  ViewUtils::textBox($this, 'From', ['atts' => ['edit' => ['style' => ['width' => '20em']]]]),
            'to'   =>  ViewUtils::textBox($this, 'To'  , ['atts' => ['edit' => [
                            'style' => ['width' => '20em'],
                            'onChangeLocalAction' => [
                                'send'  => ['hidden' =>"var aW = registry.byId(self.form.id+'parentid');if (!newValue||!aW.get('value').id){return true;}else{return false;}"],
                            ],
                        ],
                    ],
                ]
            ),
            'date'  =>  ViewUtils::textBox($this, 'Date', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '15em']]]]),
            'size'  =>  ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Size'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'uid'   =>  ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Uid'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'msgno' =>  ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Msgno'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'recent'=>  ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Recent'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'flagged'=> ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Flagged'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'answered'=>['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Answered'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'deleted'=> ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Deleted'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'seen'  =>  ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Seen'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'draft' =>  [
                'type' => 'numberTextBox',  
                'atts' => [
                    'edit' =>  ['title' => $this->tr('Draft'), 'disabled' => true, 'style' => ['width' => '9em'],
                        'onChangeLocalAction' => [
                            'name' => ['disabled' =>"if (newValue == 1){return false;}else{return true;}"],
                            'from'    => ['disabled' =>"if (newValue == 1){return false;}else{return true;}"],
                            'to'      => ['disabled' =>"if (newValue == 1){return false;}else{return true;}"],
                            'body'    => ['disabled' =>"if (newValue == 1){return false;}else{return true;}"],
                            'save'    => ['hidden'   =>"if (newValue == 1){return false;}else{return true;}"],
                            'send'    => ['hidden'   =>"if (newValue != 1){return true;}"],
                        ],
                    ],
                ],
            ],
            'udate' =>  ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('Udate'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'body'  =>  ['type' => 'editor',         'atts' => ['edit' =>  ['title' => $this->tr('Message'), 'colspan' => '6', 'height' => '400px']],
                              'objToEdit' => ['nullToBlank' => ['class' => 'TukosLib\Utils\Utilities']],
                              'objToStoreEdit' => ['nullToBlank' => ['class' => 'TukosLib\Utils\Utilities']],
                            ],
        ];

        $subObjects['mailmessages']   = ['atts' => ['title' => $this->tr('MailMessages'), 'maxHeight' => '200px'],
                                          'view' => $this,
                                       'filters' => ['parentid' => '@parentid', 'mailboxname' => '@mailboxname'],
                                'allDescendants' => false];

        $this->customize([], $subObjects);
    }    

    function allowedGetCols(){
        return ['id', 'parentid', 'mailboxname', 'name', 'from', 'to', 'date', 'size', 'uid', 'msgno', 'recent', 'flagged', 'answered', 'deleted', 'seen', 'draft', 'udate', 'body'];
    }                            

    function gridCols(){
        return ['id', 'parentid', 'mailboxname', 'name', 'from', 'to', 'date', 'size', 'uid', 'msgno', 'recent', 'flagged', 'answered', 'deleted', 'seen', 'draft', 'udate'];
    }                            

    function overviewProcess($idsToProcess){
        return $this->model->process($idsToProcess);
    }
}
?>
