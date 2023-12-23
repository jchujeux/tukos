<?php
namespace TukosLib\Objects\Sports\Plans\BackOffice;

use TukosLib\Objects\ObjectTranslator;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\Objects\Sports\Plans\WorkoutsFeedbackUtils;
use TukosLib\Utils\Feedback;

class WorkoutFeedback extends ObjectTranslator{
    use WorkoutsFeedbackUtils;
    function __construct($query){
        parent::__construct('sptplans');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->plansModel = $this->objectsStore->objectModel('sptplans');
        $this->workoutsModel = Tfk::$registry->get('objectsStore')->objectModel('sptworkouts');
        $this->view  = $this->objectsStore->objectView('sptworkouts');
        $this->instantiateVersion(Utl::getItem('version', $query, 'V2', 'V2'));
        $this->synchroFields = '["' .  implode('", "', $this->version->synchroWidgets) . '"]';
        $this->weeklyFields = '["' .  implode('", "', $this->version->formWeeklyCols) . '"]';
        $this->dataWidgets = $this->version->getFormDataWidgets();
        $this->dataWidgets['athleteweeklyfeeling'] = ViewUtils::textArea($this, 'Athleteweeklyfeeling', ['atts' => ['edit' => ['style' => ['width' => '100%']]]]);
        $this->dataWidgets['coachweeklycomments'] = ViewUtils::textArea($this, 'CoachWeeklyComments', ['atts' => ['edit' => ['style' => ['color' => 'grey', 'fontweight' => 'bolder', 'width' => '100%']]]]);
        foreach ($this->version->hideIfEmptyWidgets as $name){
            $this->dataWidgets[$name]['atts']['edit'] = array_merge($this->dataWidgets[$name]['atts']['edit'], ['hidden' => true, 'disabled' => true, 'onWatchLocalAction' => ['value' => [$name => ['hidden' => ['triggers' => ['server' => true, 'user' => false], 'action' => "return newValue ? false : true;"]]]]]);
        }
        $this->dataWidgets['startdate']['atts']['edit']['onChangeLocalAction']['startdate']['localActionStatus'] = $this->onDateOrTimeChangeLocalAction($query);
        $this->dataWidgets['starttime']['atts']['edit']['onChangeLocalAction']['starttime']['localActionStatus'] = $this->onDateOrTimeChangeLocalAction($query);
        $this->onOpenAction = $this->getOnOpenAction(Utl::getItem('synchroflag', $query, false), 'this');
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
                            'widgets' => ['id', 'sportsman', 'startdate', 'starttime'],
                        ]
                    ]
                ],
                'row2' =>[
                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => $this->version->row2LayoutWidgets()
                ],
                'row3' =>[
                    'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                    'widgets' => $this->version->row3LayoutWidgets()
                ],
                'row4' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'orientation' => 'vert'],
                    'widgets' => ['athletecomments', 'coachcomments']
                ],
                'row5' => [
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
        $title = $this->tr('workouttrackingformtitle');
        $actionWidgets['title'] = ['type' => 'HtmlContent', 'atts' => ['value' => $this->isMobile ?  $title : '<h1>' . $title . '</h1>']];
        Feedback::suspend();
        $actionWidgets['logo'] = ['type' => 'HtmlContent', 'atts' => ['value' => 
            '<img alt="logo" src="' . $this->workoutsModel->getLogoUrl($query['parentid']) . '" style="height: ' . ($isMobile ? '40' : '80') . 'px; maxWidth: ' . ($isMobile ? '100' : '200') . 'px;' . ($isMobile ? 'float: right;' : '') . '">']];
        Feedback::resume();
        $query['targetdb'] = rawurlencode($query['targetdb']);
        $actionWidgets['send'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        $actionWidgets['showsynchrofields'] = ['type' => 'TukosButton', 'atts' => ['hidden' => (empty($query['synchroflag'])) ? true : false, 'onClickAction' => $this->showSynchroFieldsOnClickAction()]];
        $actionWidgets['showweeklies'] = ['type' => 'TukosButton', 'atts' => ['onClickAction' => $this->showWeekliesOnClickAction()]];
        $this->view->addToTranslate(['showsynchrofields', 'hidesynchrofields', 'showweeklies', 'hideweeklies']);
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('workouttrackingformtitle');
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
        return $this->getPerformedWorkout($query);
    }
    function save($query, $valuesToSave){
        return $this->updatePerformedWorkout($query, $valuesToSave);
    }
    public function getPerformedWorkout($query){
        $programId = $query['parentid'];
        $programInformation = $this->getProgramInformation($programId);
        if (!empty($id = Utl::getItem('id', $query))){
            $performedWorkout = $this->workoutsModel->getOne(['where' => $this->user->filterPrivate(['id' => $id], 'sptworkouts'), 'cols' => $this->version->formObjectWidgets()]);
            if (empty($performedWorkout)){
                Feedback::reset();
                Feedback::add($this->tr('workoutnotfounddeleted') . "(id = $id)");
                return [];
            }else if(empty($performedWorkout['starttime'])){
                Feedback::suspend();
                $this->plansModel->stravaProgramSynchronize([
                    'id' => $programId, 'parentid' => $programInformation['parentid'], 'ignoreworkoutflag' => false, 'synchrostart' => $query['date'], 'synchroend' => $query['date'], 'synchrostreams' => Utl::getItem('synchrostreams', $query), 
                    'updator' => $programInformation['updator'], 'googlecalid' => $programInformation['googlecalid']]);
                $performedWorkout = $this->workoutsModel->getOne(['where' => $this->user->filterPrivate(['id' => $id], 'sptworkouts'), 'cols' => $this->version->formObjectWidgets()]);
                Feedback::resume();
            }
            Feedback::add($this->tr('Updateworkoutfeedback'));
            $performedWorkout['sportsman'] = $programInformation['parentid'];
        }else{
            $performedWorkout = $this->workoutsModel->getOne(['where' => $this->user->filterPrivate(array_filter(['parentid' => $programId, 'startdate' => $query['date'], 'starttime' => $query['starttime'], 'mode' => 'performed']), 'sptworkouts'), 'cols' => $this->version->formObjectWidgets()]);
            if (empty($performedWorkout) || empty($performedWorkout['starttime'])){
                Feedback::suspend();
                $this->plansModel->stravaProgramSynchronize([
                    'id' => $programId, 'parentid' => $programInformation['parentid'], 'ignoreworkoutflag' => false, 'synchrostart' => $query['date'], 'synchroend' => $query['date'], 'synchrostreams' => Utl::getItem('synchrostreams', $query), 
                    'updator' => $programInformation['updator'], 'googlecalid' => $programInformation['googlecalid']]);
                $performedWorkout = $this->workoutsModel->getOne(['where' => $this->user->filterPrivate(array_filter(['parentid' => $programId, 'startdate' => $query['date'], 'starttime' => $query['starttime'],  'mode' => 'performed']), 'sptworkouts'), 'cols' => $this->version->formObjectWidgets()]);
                Feedback::resume();
                if (empty($performedWorkout)){
                    $performedWorkout = ['id' => '', 'sportsman' => $programInformation['parentid'], 'startdate' => $query['date'], 'starttime' => $query['starttime'], 'name' => rawurldecode($query['name']), 'sport' => rawurldecode($query['sport']), 'duration' => '0', 'distance' => 0, 'elevationgain' => 0];
                }
            }
            Feedback::add($this->tr('Provideworkoutfeedback'));
        }
        $performedWorkout['sportsman'] = SUtl::translatedExtendedNames([$performedWorkout['sportsman']])[$performedWorkout['sportsman']];
        if ($weeklies = $programInformation['weeklies']){
            $weekOf = Dutl::mondayThisWeek($performedWorkout['startdate']);
            foreach($weeklies as $item){
                if (Utl::getItem('weekof', $item) === $weekOf){
                    foreach ($this->version->formWeeklyCols as $col){
                        if (!empty($item[$col])){
                            $performedWorkout[$col] = $item[$col];
                        }
                    }
                    break;
                }
            }
        }
        return $performedWorkout;
    }
    public function updatePerformedWorkout($query, $values){
        $programInformation = $this->getProgramInformation($programId = $query['parentid']);
        $weeklyValues = Utl::extractItems($this->version->formWeeklyCols, $values);
        $savedCount = count($values);
        $id = $query['id'];
        if ($savedCount){
            $users = Tfk::$registry->get('objectsStore')->objectModel('users')->getAll(['where' => [['col' => 'parentid', 'opr' => 'IN', 'values' => [$programInformation['coach'], $programInformation['parentid']]]], 'cols' => ['id', 'parentid']]);
            $values['acl'] = ['1' => ['userid' => Tfk::tukosBackOfficeUserId, 'permission' => '3']];
            foreach($users as $user){
                $values['acl'][] = ['userid' => $user['id'], 'permission' => '3'];
            }
            if (empty($id)){
                $where = Utl::getItems(['parentid', 'startdate', 'starttime', 'mode', 'contextid'], array_merge($query, $values, ['mode' => 'performed', 'contextid' => $programInformation['contextid']]));
                $id = $this->workoutsModel->updateOne(array_merge(['contextid' => $programInformation['contextid']], $values), ['where' => $this->user->filterPrivate($where, 'sptworkouts')], true, false, 
                    ['name' => $query['name'], 'sport' => $query['sport'], 'startdate' => $query['date'], 'mode' => 'performed', 'parentid' => $query['parentid'], 'sportsman' => $programInformation['parentid']])['id'];
                    if (!$id){
                        $id = $this->workoutsModel->lastUpdateOneOldId();
                    }
            }else{
                $newDate = Utl::getItem('startdate', $values); $newstarttime = Utl::getItem('starttime', $values); $dateHasChanged = false; $starttimeHasChanged = false;
                if ($newDate || $newstarttime){
                    $existingWorkout = $this->workoutsModel->getOne(['where' => $this->user->filterPrivate(['id' => $id], 'sptworkouts'), 'cols' => ['id', 'starttime']]);
                    $existingWorkoutsAtNewDate = $this->workoutsModel->getAll(['where' => $this->user->filterPrivate(['parentid' => $programId, 'startdate' => $newDate, 'mode' => 'performed'], 'sptworkouts'), 'cols' => ['id', 'starttime']]);
                    $dateHasChanged = true; $starttimeHasChanged = true; $starttimeAlreadyExists = false;
                    foreach($existingWorkoutsAtNewDate as $workout){
                        if ($workout['id'] === $id){
                            $dateHasChanged = false;
                            if ($newstarttime === $workout['starttime']){
                                $starttimeHasChanged = false;
                                break;
                            }
                        }
                        if ($newstarttime === $workout['starttime'] || $existingWorkout['starttime'] === $workout['starttime']){
                            $starttimeAlreadyExists = true;
                        }
                    }
                }
                if (!$this->workoutsModel->updateOne($values, ['where' => $this->user->filterPrivate(['id' => $id], 'sptworkouts')])){
                    $savedCount = 0;
                }
            }
        }
        if (!empty($weeklyValues)){
            $weeklies = Utl::getItem('weeklies', [], []);
            //if ($weeklies = $programInformation['weeklies']){
                $workoutDate = $this->workoutsModel->getOne(['where' => ['id' => $id], 'cols' => ['startdate']])['startdate'];
                $weeklyValues['weekof'] = $weekOf = Dutl::mondayThisWeek($workoutDate);
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
                if ($this->plansModel->updateOne(['id' => $programId, 'weeklies' => $weeklies])){
                    $savedCount += count($weeklyValues) - 1;
                }
            //}
        }
        if ($savedCount){
            $this->plansModel->googleSynchronizeOne($programId, $programInformation['googlecalid'], $id, Utl::getItem('synchroflag', $query), Utl::getItem('synchrostreams', $query), Utl::getItem('logo', $query), Utl::getItem('presentation', $query),
                Utl::getItem('version', $query));
            Feedback::add($this->tr('workoutsaved'));
        }else{
            Feedback::add($this->tr('noworkoutchange'));
        }
        return $id;
    }
    function getProgramInformation($programId){
        $programInformation = $this->plansModel->getOne(['where' => ['id' => $programId], 'cols' => ['parentid', 'coach', 'googlecalid', 'weeklies', 'contextid', 'updator']], ['weeklies' => []]);
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
    function getOnOpenAction($synchroflag, $form){
        return <<<EOT
var form = $form, hasSomeValue;
if ('$synchroflag'){
    hasSomeValue = {$this->synchroFields}.some(function(name){
        var value = form.valueOf(name);
        return name === 'duration' ? (value != 'T00:00:00') : (value ? true : false); 
    });
    {$this->synchroFields}.forEach(function(name){
        form.getWidget(name).set('hidden', !hasSomeValue);
    });
    form.getWidget('showsynchrofields').set('label', Pmg.message(hasSomeValue ? 'hidesynchrofields' : 'showsynchrofields', 'backoffice'));
    form.synchroFieldsAreShown = hasSomeValue;
}
hasSomeValue = {$this->weeklyFields}.some(function(name){
    return form.valueOf(name) ? true : false;
});
{$this->weeklyFields}.forEach(function(name){
    form.getWidget(name).set('hidden', !hasSomeValue);
});
form.getWidget('showweeklies').set('label', Pmg.message(hasSomeValue ? 'hideweeklies' : 'showweeklies', 'backoffice'));
form.weekliesAreShown = hasSomeValue;
form.resize();
EOT
        ;
    }
    function onDateOrTimeChangeLocalAction($query){
        $urlArgs = json_encode($query);
        return <<<EOT
var urlArgs = {$urlArgs};
if (urlArgs.id){
    return true;
}else{
    urlArgs.starttime = sWidget.form.valueOf('starttime');
    urlArgs.date = sWidget.form.valueOf('startdate');
    sWidget.form.checkChangesDialog(function(){
            sWidget.form.serverDialog({action: 'Reset', query: urlArgs}, {}, sWidget.form.get('dataElts'), null, true).then(function(){
                {$this->getOnOpenAction($query['synchroflag'], 'sWidget.form')}
            });
        }, true, [sWidget.name]);
    return true;
} 
EOT
        ;
    }
}
?>
