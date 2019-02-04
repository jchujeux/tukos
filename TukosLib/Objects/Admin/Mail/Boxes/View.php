<?php
/**
 *
 * class for viewing methods and properties for the $wineinputs model object
 */
namespace TukosLib\Objects\Admin\Mail\Boxes; 

use TukosLib\Objects\ObjectTranslator;
//use TukosLib\Objects\AbstractView;
use TukosLib\Utils\Widgets;
use TukosLib\Objects\ViewUtils;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class View extends ObjectTranslator{

    function __construct($objectName, $translator, $parentWidgetTitle='parent', $nameWidgetTitle='name'){
        parent::__construct($objectName, $translator);
        $this->objectName = $objectName;
        $this->model = Tfk::$registry->get('objectsStore')->objectModel($this->objectName, $this->tr);
        $this->user  = Tfk::$registry->get('user');
        $this->sendOnSave = $this->sendOnDelete = [];
        
        $this->dataWidgets = [
            'id' => ViewUtils::textBox($this, 'Id', [
                    'atts' => [
                        'edit' =>  [
                            'disabled' => true, 'style' => ['width' => '12em'],
                            'onChangeLocalAction' => [
                                'parentid' => ['disabled' =>"if (newValue === ''){return false;}else{return true}"],
                                'name'     => ['disabled' =>"if (newValue === ''){return false;}else{return true}"],
                                'get'      => ['hidden'   =>"if (newValue === ''){return false;}else{return true}"],
                                'create'   => ['hidden'   =>"if (newValue === ''){return false;}else{return true}"],
                            ],
                        ],
                        'storeedit' => ['onClickFilter'=> ['id'], 'rowsFilters' => Utl::idsNamesStore(['', 'Contains'], $this->tr)],
                        'overview' => ['onClickFilter' => ['id'], 'rowsFilters' => Utl::idsNamesStore(['', 'Contains'], $this->tr)],
                    ]
                ]
            ), 
            'parentid'  => ViewUtils::objectSelectMulti('parentid', $this, 'Mailaccount'),
            'name'      => ViewUtils::textBox($this, 'Mailbox', [
                        'atts' => ['edit' => [
                            'onChangeLocalAction' => [
                                'get'    => ['hidden' =>"var idW = registry.byId(self.form.id+'id');if (newValue === '' || idW.get('value') !== ''){return true;}else{return false}"],
                                'create' => ['hidden' =>"var idW = registry.byId(self.form.id+'id');if (newValue === '' || idW.get('value') !== ''){return true;}else{return false}"],
                            ],
                        ],
                    ],
                ]
            ),
            'Nmsgs'  => [
                'type' => 'numberTextBox',
                'atts' => [
                    'edit' =>  [
                        'title' => $this->tr('NbMessages'), 'disabled' => true, 'style' => ['width' => '9em'],
                        'onChangeLocalAction' => [
                            'delete'   => ['hidden'   =>"var idW = registry.byId(self.form.id+'id');if (newValue == 0 && idW.get('value') != ''){return false;}else{return true}"],
                        ],
                    ],
                ],
            ],
            'Recent' => ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('NbRecentMsgs'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'Unread' => ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('NbUnreadMsgs'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'Deleted'=> ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('NbDeletedMsgs'), 'disabled' => true, 'style' => ['width' => '9em']]]],
            'Size'   => ['type' => 'numberTextBox',  'atts' => ['edit' =>  ['title' => $this->tr('SizeMsgs'), 'disabled' => true, 'style' => ['width' => '9em']]]],
        ];

        $this->subObjects = ['mailmessages' => ['atts' => ['title' => $this->tr('MailMessages')], 'filters' => ['parentid' => '@parentid', 'mailboxname' => '@name'], 'allDescendants' => false]];

        $this->overviewActionLayout = [
            'attributes' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
            'elements' => ['actionsArea' => 
                            ['attributes' => ['cols' => 1, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->tr('For all items') . '<b>'],
                               'elements' => ['reset'],
                            ],
                        'selectionArea' =>
                            ['attributes' => ['cols' => 1, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->tr('For selected items') . '<b>'],
                               'elements' => ['delete']
                            ],
                       'feedbackArea' => 
                            ['attributes' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->tr('Feedback') . ':<b>'],
                              'elements' => ['clearFeedback', 'feedback']
                            ],
            ],
        ];
        $this->mustGetCols = [];

    }
       
    function allowedGetCols(){
        return  ['parentid', 'name', 'Nmsgs', 'Recent', 'Unread', 'Deleted', 'Size'];
    }                            

    function sendOnSave(){
        return $this->sendOnSave;
    }
    
    function sendOnDelete(){
        return $this->sendOnDelete;
    }
    function gridCols(){
        return  ['id', 'parentid', 'name', 'Nmsgs', 'Recent', 'Unread'];
    }                            

    function widgetsDescription($elements, $editOnly = true){
        $result = [];
        foreach ($elements as $id){
            $result[$id] = Widgets::description($this->dataWidgets[$id], $editOnly);
        }
        return $result;
    }

    function tabEditTitle ($values){
        return (empty($values['id']) 
            ? $this->tr($this->objectName) . ' (' . $this->tr('new') . ')'
            : Utl::concat(Utl::getItems($this->model->extendedNameCols, $values),' ', 25)  . ' (' . ucfirst($this->tr($this->objectName)) . '  '  . $values['id'] . ')');
    }

}
?>
