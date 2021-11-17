<?php
namespace TukosLib\Objects\Sports\Programs\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\Objects\Sports\Programs\SessionsFeedbackUtils;
use TukosLib\Utils\Feedback;

class SessionFeedback extends ObjectTranslator{
    use SessionsFeedbackUtils;
    function __construct($query){
        parent::__construct('sptprograms');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->programsModel = $this->objectsStore->objectModel('sptprograms');
        $this->sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        $this->view  = $this->objectsStore->objectView('sptsessions');
        $this->instantiateVersion($query['version']);
        $this->synchroFields = '["' .  implode('", "', $this->version->synchroWidgets) . '"]';
        $this->weeklyFields = '["' .  implode('", "', $this->version->formWeeklyCols) . '"]';
        $this->dataWidgets = $this->version->getFormDataWidgets();
        $this->dataWidgets['athleteweeklyfeeling'] = ViewUtils::textArea($this, 'Athleteweeklyfeeling', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]);
        $this->dataWidgets['coachweeklycomments'] = ViewUtils::textArea($this, 'CoachWeeklyComments', ['atts' => ['edit' => ['style' => ['color' => 'grey', 'fontweight' => 'bolder', 'width' => '100%']]]]);
        foreach ($this->version->hideIfEmptyWidgets as $name){
            $this->dataWidgets[$name]['atts']['edit'] = array_merge($this->dataWidgets[$name]['atts']['edit'], ['hidden' => true, 'disabled' => true, 'onWatchLocalAction' => ['value' => [$name => ['hidden' => ['triggers' => ['server' => true, 'user' => false], 'action' => "return newValue ? false : true;"]]]]]);
        }
        $this->onOpenAction = $this->getOnOpenAction(Utl::getItem('gcflag', $query, false));
        if ($presentation = Utl::getItem('presentation', $query)){
            switch($presentation){
                case 'MobileTextBox':
                    foreach($this->version->numberWidgets() as $name){
                        $this->dataWidgets[$name]['atts']['edit']['mobileWidgetType'] = 'MobileTextBox';
                    }
                    $this->dataWidgets['duration']['atts']['edit']['mobileWidgetType'] = 'TimeTextBox';
                    break;
                default:
                    foreach($this->version->ratingWidgets() as $name){
                        $this->dataWidgets[$name]['atts']['edit']['mobileWidgetType'] = 'MobileSliderSelect';
                    }
            }
        }
        $this->dataElts = array_values(array_diff(array_keys($this->dataWidgets), ['sportsman'/*, 'startdate'*/]));
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => true],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Athletefeedback') . '</b>'],
                    'contents' => [
                        'col2' => [
                            'tableAtts' => ['cols' => 4, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['id', 'sportsman', 'startdate', 'sessionid'],
                        ]
                    ]
                ],
                'row2' =>[
                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => $this->version->row2LayoutWidgets()
                ],
                'row3' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['athletecomments', 'coachcomments']
                ],
                'row4' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'label' => "<b>{$this->view->tr('weeklyfeedback')}</b>"],
                    'widgets' => ['athleteweeklyfeeling', 'coachweeklycomments']
                ]
            ]
        ];
        $this->actionLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => false],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert',  'content' => ''],
                    'widgets' => Tfk::$registry->isMobile ? ['logo'] : ['logo', 'title']
                ],
                'row2' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'orientation' => 'vert',  'content' => ''],
                    'contents' => [
                        'actions' => [
                            'tableAtts' => ['cols' => 5, 'customClass' => 'actionTable', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Actions') . ':</b>'],
                            'widgets' => ['send', 'reset', 'showsynchrofields', 'showweeklies'],
                        ],
                        'feedback' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'actionTable', 'showLabels' => false,  'label' => '<b>' . $this->view->tr('Feedback') . ':</b>'],
                            'widgets' => ['feedback'],
                        ],
                    ],
                ]
            ]
        ];
    }
    function getActionWidgets($query){
        $isMobile = $this->isMobile;
        $title = $this->tr('sessiontrackingformtitle');
        $actionWidgets['title'] = ['type' => 'HtmlContent', 'atts' => ['value' => $this->isMobile ?  $title : '<h1>' . $title . '</h1>']];
        if ($logo = Utl::getItem('logo', $query)){
            $actionWidgets['logo'] = ['type' => 'HtmlContent', 'atts' => ['value' => 
                '<img alt="logo" src="' . Tfk::$publicDir . 'images/' . $logo . '" style="height: ' . ($isMobile ? '40' : '80') . 'px; width: ' . ($isMobile ? '100' : '200') . 'px;' . ($isMobile ? 'float: right;' : '') . '">']];
        }
        $query['targetdb'] = rawurlencode($query['targetdb']);
        $actionWidgets['send'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        $actionWidgets['showsynchrofields'] = ['type' => 'TukosButton', 'atts' => [/*'label' => $this->view->tr('showsynchrofields'), */'hidden' => empty($query['gcflag']), 'onClickAction' => $this->showSynchroFieldsOnClickAction()]];
        $actionWidgets['showweeklies'] = ['type' => 'TukosButton', 'atts' => ['onClickAction' => $this->showWeekliesOnClickAction()]];
        $this->view->addToTranslate(['showsynchrofields', 'hidesynchrofields', 'showweeklies', 'hideweeklies']);
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('sessiontrackingformtitle');
    }
    function getToTranslate(){
        return $this->view->getToTranslate();
    }
    function sendOnSave(){
        return [];
    }
    function sendOnReset(){
        return [];
    }
    function get($query){
        return $this->getPerformedSession($query);
    }
    function save($query, $valuesToSave){
        return $this->updatePerformedSession($query, $valuesToSave);
    }
    public function getPerformedSession($query){
        $programId = $query['parentid'];
        $programInformation = $this->getProgramInformation($programId);
        if (!empty($id = Utl::getItem('id', $query))){
            $performedSession = $this->sessionsModel->getOne(['where' => $this->user->filterPrivate(['id' => $id], 'sptsessions'), 'cols' => $this->version->formObjectWidgets()]);
            if (empty($performedSession)){
                Feedback::reset();
                Feedback::add($this->tr('sessionnotfounddeleted') . "(id = $id)");
                return [];
            }
            Feedback::add($this->tr('Updatesessionfeedback'));
            $performedSession['sportsman'] = $programInformation['parentid'];
        }else{
            $existingSessionsSessionId = array_column($this->sessionsModel->getAll(['where' => $this->user->filterPrivate(array_filter(['parentid' => $programId, 'startdate' => $query['date'], 'mode' => 'performed']), 'sptsessions'), 'cols' => ['sessionid']]), 'sessionid');
            $sessionId = empty($existingSessionsSessionId) ?  Utl::getItem('sessionid', $query) : (max($existingSessionsSessionId) + 1);
             
            $performedSession = ['id' => '', 'sportsman' => $programInformation['parentid'], 'startdate' => $query['date'], 'sessionid' => $sessionId, 'name' => rawurldecode($query['name']), 'sport' => rawurldecode($query['sport']), 'duration' => '0', 'distance' => 0, 'elevationgain' => 0];
            Feedback::add($this->tr('Providesessionfeedback'));
        }
        $performedSession['sportsman'] = SUtl::translatedExtendedNames([$performedSession['sportsman']])[$performedSession['sportsman']];
        if ($weeklies = $programInformation['weeklies']){
            $weekOf = Dutl::mondayThisWeek($performedSession['startdate']);
            foreach($weeklies as $item){
                if ($item['weekof'] === $weekOf){
                    foreach ($this->version->formWeeklyCols as $col){
                        if (!empty($item[$col])){
                            $performedSession[$col] = $item[$col];
                        }
                    }
                    break;
                }
            }
        }
        return $performedSession;
    }
    public function updatePerformedSession($query, $values){
        $programInformation = $this->getProgramInformation($programId = $query['parentid']);
        $weeklyValues = Utl::extractItems($this->version->formWeeklyCols, $values);
        $savedCount = count($values);
        $id = $query['id'];
        if ($savedCount){
            $values['acl'] = ['1' => ['rowId' => 1, 'userid' => $programInformation['updator'], 'permission' => '3'], '2' => ['rowId' => 2, 'userid' => Tfk::tukosBackOfficeUserId, 'permission' => '3']];
            if (empty($id)){
                $where = Utl::getItems(['parentid', 'startdate', 'sessionid', 'mode', 'contextid'], array_merge($query, $values, ['mode' => 'performed', 'contextid' => $programInformation['contextid']]));
                $id = $this->sessionsModel->updateOne(array_merge(['contextid' => $programInformation['contextid']], $values), ['where' => $this->user->filterPrivate($where, 'sptsessions')], true, false, 
                    ['name' => $query['name'], 'sport' => $query['sport'], 'startdate' => $query['date'], 'mode' => 'performed', 'parentid' => $query['parentid'], 'sportsman' => $programInformation['parentid']])['id'];
                    if (!$id){
                        $id = $this->sessionsModel->lastUpdateOneOldId();
                    }
            }else{
                if (!$this->sessionsModel->updateOne($values, ['where' => $this->user->filterPrivate(['id' => $id], 'sptsessions')])){
                    $savedCount = 0;
                }
            }
        }
        if (!empty($weeklyValues)){
            $weeklies = Utl::getItem('weeklies', [], []);
            //if ($weeklies = $programInformation['weeklies']){
                $sessionDate = $this->sessionsModel->getOne(['where' => ['id' => $id], 'cols' => ['startdate']])['startdate'];
                $weeklyValues['weekof'] = $weekOf = Dutl::mondayThisWeek($sessionDate);
                $wasUpdated = false;
                $maxRowId = 0;
                foreach($weeklies as &$item){
                    if ($item['weekof'] ===  $weekOf){
                        $item = array_merge($item, $weeklyValues);
                        $wasUpdated = true;
                        break;
                    }
                    if ($item['rowId'] > $maxRowId){
                        $maxRowId = $item['rowId'];
                    }
                }
                if (!$wasUpdated){
                    $weeklyValues['rowId'] = $maxRowId + 1;
                    $weeklies[] = $weeklyValues;
                }
                if ($this->programsModel->updateOne(['id' => $programId, 'weeklies' => $weeklies])){
                    $savedCount += count($weeklyValues) - 1;
                }
            //}
        }
        if ($savedCount){
            $this->programsModel->googleSynchronizeOne($programId, $programInformation['googlecalid'], $id, Utl::getItem('gcflag', $query), Utl::getItem('logo', $query), Utl::getItem('presentation', $query), Utl::getItem('version', $query));
            Feedback::add($this->tr('sessionsaved'));
        }else{
            Feedback::add($this->tr('nosessionchange'));
        }
        return $id;
    }
    function getProgramInformation($programId){
        $programInformation = $this->programsModel->getOne(['where' => ['id' => $programId], 'cols' => ['parentid', 'googlecalid', 'weeklies', 'contextid', 'updator']], ['weeklies' => []]);
        return $programInformation;
    }
    function showSynchroFieldsLocalAction(){
        $hiddenList = implode(': !newValue, ', $this->version->synchroWidgets) . ': !newValue';
        return  <<<EOT
sWidget.form.setWidgets({hidden: {{$hiddenList}}});
EOT
        ;
    }
    function showSynchroFieldsOnClickAction(){
        return  <<<EOT
var form = this.form, hide = !(form.synchroFieldsAreShown = !form.synchroFieldsAreShown);
{$this->synchroFields}.forEach(function(name){
    form.getWidget(name).set('hidden', hide);
    form.resize();
});
this.set('label', Pmg.message(hide ? 'showsynchrofields' : 'hidesynchrofields', 'backoffice'));
EOT
        ;
    }
    function showWeekliesOnClickAction(){
        return  <<<EOT
var form = this.form, hide = !(form.weekliesAreShown = !form.weekliesAreShown);
{$this->weeklyFields}.forEach(function(name){
    form.getWidget(name).set('hidden', hide);
});
this.set('label', Pmg.message(hide ? 'showweeklies' : 'hideweeklies', 'backoffice'));
form.resize();
EOT
        ;
    }
    function getOnOpenAction($gcflag){
        return <<<EOT
var self = this, hasSomeValue;
if ('$gcflag'){
    hasSomeValue = {$this->synchroFields}.some(function(name){
        var value = self.valueOf(name);
        console.log('name = ' + name + ' - value = ', value);
        return name === 'duration' ? (value != 'T00:00:00') : (value ? true : false); 
    });
    console.log('hasSomeValue = ' + hasSomeValue);
    {$this->synchroFields}.forEach(function(name){
        self.getWidget(name).set('hidden', !hasSomeValue);
    });
    this.getWidget('showsynchrofields').set('label', Pmg.message(hasSomeValue ? 'hidesynchrofields' : 'showsynchrofields', 'backoffice'));
    this.synchroFieldsAreShown = hasSomeValue;
}
hasSomeValue = {$this->weeklyFields}.some(function(name){
    return self.valueOf(name) ? true : false;
});
{$this->weeklyFields}.forEach(function(name){
    self.getWidget(name).set('hidden', !hasSomeValue);
});
this.getWidget('showweeklies').set('label', Pmg.message(hasSomeValue ? 'hideweeklies' : 'showweeklies', 'backoffice'));
this.weekliesAreShown = hasSomeValue;
this.resize();
EOT
        ;
    }
}
?>
