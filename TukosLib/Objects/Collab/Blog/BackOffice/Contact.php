<?php
namespace TukosLib\Objects\Collab\Blog\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\contentExporter;

class Contact extends ObjectTranslator{
    use ContentExporter;
    function __construct($query){
        parent::__construct('blog');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->blogModel = $this->objectsStore->objectModel('blog');
        $this->usersModel = $this->objectsStore->objectModel('users');
        $this->view  = $this->objectsStore->objectView('blog');
        $this->dataWidgets = [
            'explanation' => ViewUtils::htmlContent($this->view, 'explanation', ['atts' => ['edit' => ['widgetCellStyle' => ['backgroundColor' => '#d0e9fc'], 'style' => ['color' => 'black', 'font-size' => '12px']]]]),
            'sendto' => ViewUtils::textBox($this, 'Sendto', ['atts' => ['edit' => ['disabled' => true]]]),
            'subject' => ViewUtils::textBox($this, 'subject', ['atts' => ['edit' => ['style' => ['width' => '100%'], 'onChangeLocalAction' => ['subject' => ['localActionStatus' => "sWidget.form.getWidget('send').set('disabled', false);"]]]]]),
            'senderemail' => ViewUtils::textBox($this, 'senderemail', ['atts' => ['edit' => ['onChangeLocalAction' => ['senderemail' => ['localActionStatus' => "sWidget.form.getWidget('send').set('disabled', false);"]]]]]),
            'comments'  => ViewUtils::simpleEditor($this, 'Yourcontactformcomments', ['atts' => ['edit' => ['style' => ['height' => '400px', 'color' => 'black', 'fontWeight' => 'normal'], 'onChangeLocalAction' => ['comments' => ['localActionStatus' => "sWidget.form.getWidget('send').set('disabled', false);"]]]]]),
        ];
        $this->dataElts = array_keys($this->dataWidgets);
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => false, 'spacing' => 0, 'widgetCellStyle' => ['backgroundColor' => '#d0e9fc']],
            'contents' => [
                'row0' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
                    'widgets' => Tfk::$registry->isMobile ? ['feedback', 'explanation'] : ['explanation']
                ],
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['sendto', 'senderemail']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['subject']
                ],
                'row3' => [
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                    'widgets' => ['comments']
                ],
            ]
        ];
        $desktopActionLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
                    'widgets' => ['logo', 'title']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert',  'content' => ''],
                    'contents' => [
                        'actions' => [
                            'tableAtts' => ['cols' => 5, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Actions') . ':</b>'],
                            'widgets' => ['send', 'reset'],
                        ],
                        'feedback' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false,  'label' => '<b>' . $this->view->tr('Feedback') . ':</b>'],
                            'widgets' => ['feedback'],
                        ],
                    ],
                ]
            ]
        ];
        $mobileActionLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                 'row2' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert',  'content' => ''],
                    'contents' => [
                        'actions' => [
                            'tableAtts' => ['cols' => 5, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => $this->view->tr($query['formtitle'])],
                            'widgets' => ['send', 'reset'],
                        ]/*,
                        'feedback' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false,  'label' => '<b>' . $this->view->tr('Feedback') . ':</b>'],
                            'widgets' => ['feedback'],
                        ]*/
                    ],
                ]
            ]
        ];
        $this->actionLayout = Tfk::$registry->isMobile ? $mobileActionLayout : $desktopActionLayout;
        $this->hideBottomContainer = true;
    }
    function getActionWidgets($query){
        $isMobile = $this->isMobile;
        $title = $this->tr($query['formtitle']);//$this->tr('Tukosblogcontactform');
        $actionWidgets['title'] = ['type' => 'HtmlContent', 'atts' => ['value' => $this->isMobile ?  $title : '<h1>' . $title . '</h1>']];
        Feedback::suspend();
        $actionWidgets['logo'] = ['type' => 'HtmlContent', 'atts' => ['value' =>
            '<img alt="logo" src="' . Tfk::$publicDir . 'images/tukosswissknife.png" style="height: ' . ($isMobile ? '40' : '80') . 'px; maxWidth: ' . ($isMobile ? '100' : '200') . 'px;' . ($isMobile ? 'float: right;' : '') . '">']];
        Feedback::resume();
        $actionWidgets['send'] = ['atts' => ['urlArgs' => ['query' => $query], 'disabled' => true, 'style' => ['float' => 'left']]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => $query], 'style' => ['float' => 'left'], 'afterActions' => ['postAction' => "this.form.getWidget('send').set('disabled', false);"]]];
        if ($isMobile){
            $actionWidgets['feedback'] = ['atts' => ['style' => ['backgroundColor' => 'DarkGrey', 'width' => '100%', 'font-size' => '14px', 'height' => '15px', 'display' => 'none']]];
        }
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('Contactformtitle');
    }
    function sendOnSave(){
        return ['senderemail', 'subject', 'commments'];
    }
    function sendOnReset(){
        return [];
    }
    function get($query){
        $formTitle = Tfk::$registry->get('translatorsStore')->getTranslations([$title = $query['formtitle']], 'blog')[$title];//$this->tr($query['formtitle']);
        $formExplanation = $this->tr($query['formexplanation']);
        return ['id' => 1/*needed so markIfChange is false*/, 'explanation' => $formExplanation, 'sendto' => $query['sendto'], 'name' => $formTitle, 'subject' => Utl::getItem('subject', $query), 'comments' => Utl::getItem('comments', $query)];
    }
    function save($query, $valuesToSave){
        return $this->sendToContact($query, $valuesToSave);
    }
    function getToTranslate(){
        return $this->view->getToTranslate();
    }
    function sendToContact($query, $values){
        $contactEmail = empty($query['creator']) ? Tfk::$registry->get('tukosModel')->getOption('blogcontact')['email'] : $this->user->peopleEmail($query['creator']);
        ;
        $atts = ['to' => $contactEmail,  'cc' => Utl::getItem('senderemail', $values), 'subject' => Utl::getItem('subject', $values), 'content' => Utl::getItem('comments', $values), 'sendas' => 'appendtobody'];
        $this->sendContent([], $atts, ['name' => 'tukosBackOffice']);
        return ['data' => ['value' => array_merge($this->get($query), $values), 'disabled' => ['send' => true]]];
    }
}
?>
