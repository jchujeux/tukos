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
        $this->dataWidgets = $this->version->getFormDataWidgets();
        foreach ($this->version->hideIfEmptyWidgets as $name){
            $this->dataWidgets[$name]['atts']['edit']['disabled'] = true;
        }
        if (empty($query['sessionid'])){
            $this->dataWidgets['sessionid']['atts']['edit']['hidden'] = true;
        }
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
                    'widgets' => ['athletecomments', 'athleteweeklyfeeling']
                ],
                'row4' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'label' => "<b>{$this->view->tr('Coachcomments')}</b>"],
                    'widgets' => ['coachcomments', 'coachweeklycomments']
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
    }
    function getActionWidgets($query){
        $isMobile = $this->isMobile;
        $title = $this->tr('sessiontrackingformtitle');
        $actionWidgets['title'] = ['type' => 'HtmlContent', 'atts' => ['value' => $this->isMobile ?  $title : '<h1>' . $title . '</h1>']];
        if ($logo = Utl::getItem('logo', $query)){
            $actionWidgets['logo'] = ['type' => 'HtmlContent', 'atts' => ['value' => 
                '<img alt="logo" src="' . Tfk::publicDir . 'images/' . $logo . '" style="height: ' . ($isMobile ? '40' : '80') . 'px; width: ' . ($isMobile ? '100' : '200') . 'px;' . ($isMobile ? 'float: right;' : '') . '">']];
        }
        $query['targetdb'] = rawurlencode($query['targetdb']);
        $actionWidgets['send'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => $query]]];
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('sessiontrackingformtitle');
    }
    function sendOnSave(){
        return [/*'startdate', 'sessionid'*/];
    }
    function sendOnReset(){
        return [/*'startdate', 'sessionid'*/];
    }
    function get($query){
        Feedback::add($this->tr('Pleaseprovidesessionfeedback'));
        return $this->getPerformedSession($query);
    }
    function save($query, $valuesToSave){
        return $this->updatePerformedSession($query, $valuesToSave);
    }
    public function getPerformedSession($query){
        $programId = $query['parentid'];
        $programInformation = $this->getProgramInformation($programId);
        if (!empty($id = Utl::getItem('id', $query))){
            $performedSession = $this->sessionsModel->getOne(['where' => $this->user->filter(['id' => $id], 'sptsessions'), 'cols' => $this->version->formObjectWidgets()]);
            $performedSession['sportsman'] = $programInformation['parentid'];
        }else{
            $sessionId = Utl::getItem('sessionid', $query, '');
            $performedSession = ['id' => '', 'sportsman' => $programInformation['parentid'], 'startdate' => $query['date'], 'sessionid' => $sessionId, 'name' => rawurldecode($query['name']), 'sport' => rawurldecode($query['sport'])];
            $performedSession = array_merge($performedSession,
                $this->sessionsModel->getOne(['where' => $this->user->filter(array_filter(['parentid' => $programId, 'startdate' => $query['date'], 'sessionid' => $sessionId, 'mode' => 'performed']), 'sptsessions'), 'cols' => $this->version->formObjectWidgets()]));
        }
        $performedSession['sportsman'] = SUtl::translatedExtendedNames([$performedSession['sportsman']])[$performedSession['sportsman']];
        if (!Utl::getItem('duration', $performedSession)){
            $performedSession['duration'] = 0;
        }
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
            if (empty($id)){
                $where = Utl::getItems(['parentid', 'startdate', 'sessionid', 'mode'], array_merge($query, $values, ['mode' => 'performed']));
                $id = $this->sessionsModel->updateOne($values, ['where' => $this->user->filter($where, 'sptsessions')], true, false, 
                    ['name' => $query['name'], 'sport' => $query['sport'], 'permission' => 'PU', 'startdate' => $query['date'], 'mode' => 'performed', 'parentid' => $query['parentid'], 'sportsman' => $programInformation['parentid']])['id'];
            }else{
                if (!$this->sessionsModel->updateOne($values, ['where' => $this->user->filter(['id' => $id], 'sptsessions')])){
                    $saveCount = 0;
                }
            }
        }
        if (!empty($weeklyValues)){
            if ($weeklies = $programInformation['weeklies']){
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
                if ($this->programsModel->updateOne(['weeklies' => json_encode($weeklies)], ['where' => ['id' => $programId]])){
                    $savedCount += count($weeklyValues) - 1;
                }
            }
        }
        Feedback::add($this->tr('nbcellsupdated') . ': ' . $savedCount);
        return $id;
    }
    function getProgramInformation($programId){
        $programInformation = $this->programsModel->getOne(['where' => ['id' => $programId], 'cols' => ['parentid', 'weeklies']], ['weeklies' => []]);
        return $programInformation;
    }
}
?>
