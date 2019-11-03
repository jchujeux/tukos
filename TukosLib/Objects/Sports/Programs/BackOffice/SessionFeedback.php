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
use TukosLib\Utils\Dropbox;

class SessionFeedback extends ObjectTranslator{
    use SessionsFeedbackUtils;
    function __construct($query){
        parent::__construct('sptprograms');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->view  = $this->objectsStore->objectView('sptsessions');
        $this->instantiateVersion($query['version']);
        $this->dataWidgets = $this->version->getFormDataWidgets();
        foreach ($this->version->hideIfEmptyWidgets as $name){
            $this->dataWidgets[$name]['atts']['edit']['disabled'] = true;
            /*$this->dataWidgets[$name]['atts']['edit']['onWatchLocalAction'] = ['value' => [
                $name => ['hidden'=> ['triggers' => ['server' => true, 'user' => true], 'action' => 'return true;']],
            ]];*/
        }
        if ($presentation = Utl::getItem('presentation', $query)){
            switch($presentation){
                case 'MobileTextBox':
                    foreach($this->version->numberWidgets() as $name){
                        $this->dataWidgets[$name]['atts']['edit']['mobileWidgetType'] = 'MobileTextBox';
                    }
                    foreach($this->version->ratingWidgets() as $name){
                        $this->dataWidgets[$name]['atts']['edit']['mobileWidgetType'] = 'MobileStoreSelect';
                    }
                    $this->dataWidgets['duration']['atts']['edit']['mobileWidgetType'] = 'TimeTextBox';
            }
        }
        $this->dataElts = array_values(array_diff(array_keys($this->dataWidgets), ['sportsman', 'startdate']));
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => true],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('Athletefeedback') . '</b>'],
                    'contents' => [
                        'col2' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['sportsman', 'startdate'],
                            
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
            //['form' => $query['form'], 'version' => $query['version'], 'object' => $query['object'], 'parentid' => $query['parentid'], 'date' => $query['date'], 'targetdb' => rawurlencode($query['targetdb'])]]]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => $query]]];
            //['form' => $query['form'], 'version' => $query['version'], 'object' => $query['object'], 'parentid' => $query['parentid'], 'date' => $query['date'], 'targetdb' => rawurlencode($query['targetdb'])]]]];
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('sessiontrackingformtitle');
    }
    function sendOnSave(){
        return [/*'startdate'*/];
    }
    function sendOnReset(){
        return [/*'startdate'*/];
    }
    function get($query, $formValues = []){
        $query = array_merge($query, $formValues);
        Feedback::add($this->tr('Pleaseprovidesessionfeedback'));
        return $this->getPerformedSession($query);
    }
    function save($query, $valuesToSave){
        $this->updatePerformedSession($query, $valuesToSave);
        return $valuesToSave;
    }
    public function getPerformedSession($query){
        $programId = $query['parentid'];
        $sessionDate = $query['date'];
        $programInformation = $this->getProgramInformation($programId);
        $sportsmanName = SUtl::translatedExtendedName(Tfk::$registry->get('objectsStore')->objectModel('people'), $programInformation['parentid']);
        $performedSession = ['id' => 'xxx', 'sportsman' => $sportsmanName, 'startdate' => $sessionDate];
        if (!empty($dropboxFilePath = $programInformation['dropboxFilePath'])){
            if ($this->downloadDropboxFile($dropboxFilePath, $programInformation['updator'])){
                if ($this->setWorksheetRow($sessionDate)){
                    $performedSession = array_merge($performedSession, $this->version->sheetRowToForm($this)); 
                }
                $this->workbook->close();
            }
        }else{
            $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
            $performedSession = array_merge($performedSession, 
                $sessionsModel->getOne(['where' => $this->user->filter(['parentid' => $programId, 'startdate' => $sessionDate, 'mode' => 'performed'], 'sptsessions'), 'cols' => $this->version->formCols()]),
                $sessionsModel->getOne(['where' => $this->user->filter(['parentid' => $programId, 'startdate' => Dutl::mondayThisWeek($sessionDate), 'mode' => 'performed'], 'sptsessions'), 'cols' => $this->version->formWeeklyCols]));
            if (!$duration = Utl::getItem('duration', $performedSession)){
                $performedSession['duration'] = 0;
            }
        }
        $query['name'] = rawurldecode($query['name']);
        foreach(['name', 'sport'] as $property){
            if (empty($performedSession[$property])&& !empty($query[$property])){
                $performedSession[$property] = $query[$property];
            }
        }
        return $performedSession;
    }
    public function updatePerformedSession($query, $values){
        $programInformation = $this->getProgramInformation($query['parentid']);
        if (!empty($dropboxFilePath = $programInformation['dropboxFilePath'])){
            if ($this->downloadDropboxFile($dropboxFilePath, $programInformation['updator'])){
                if ($this->setWorksheetRow($query['date'])){
                    $cellsUpdated = $this->version->formToSheetRow($values, $this);
                    if (!empty($cellsUpdated)){
                        $this->workbook->updateSheet(1, $this->sheet);
                        $this->workbook->close();
                        $returnData = Dropbox::uploadFile($dropboxFilePath, $this->dropboxAccessToken);
                        Feedback::add($this->tr('nbcellsupdated') . ': ' . $cellsUpdated);
                    }else{
                        $this->workbook->close();
                        Feedback::add($this->tr('Noupdateneeded'));
                    }
                }
            }
        }else{
            $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
            /*$values['mode'] = 'performed';
            $values['parentid'] = $programId;
            $values['sportsman'] = $programInformation['parentid'];*/
            $weeklyValues = Utl::extractItems($this->version->formWeeklyCols, $values);
            $savedCount = 0;
            if (!empty($values)){
                $sessionsModel->updateOne($values, ['where' => $this->user->filter(['parentid' => $query['parentid'], 'startdate' => $query['date'], 'mode' => 'performed'])], true, false, 
                    ['name' => $query['name'], 'sport' => $query['sport'], 'permission' => 'PU', 'startdate' => $query['date'], 'mode' => 'performed', 'parentid' => $query['parentid'], 'sportsman' => $programInformation['parentid']]);
                $savedCount += count($values);
            }
            if (!empty($weeklyValues)){
                $weeklySessionDate = Dutl::mondayThisWeek($query['date']);
                $sessionsModel->updateOne($weeklyValues, ['where' => $this->user->filter(['parentid' => $query['parentid'], 'startdate' => $weeklySessionDate, 'mode' => 'performed'])], true, false, 
                    ['sport' => 'rest', 'permission' => 'PU', 'startdate' => $weeklySessionDate, 'mode' => 'performed', 'parentid' => $query['parentid']]);
                $savedCount += count($weeklyValues);
            };
            Feedback::add($this->tr('nbcellsupdated') . ': ' . $savedCount);
        }
    }
    function getProgramInformation($programId){
        $programsModel = $this->objectsStore->objectModel('sptprograms');
        $programInformation = $programsModel->getOne(['where' => ['id' => $programId], 'cols' => ['parentid', 'custom', 'updator']],
            ['custom' => ['edit', 'tab', 'widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'filepath', 'atts', 'value']]);
        $programInformation['dropboxFilePath'] = $programsModel->getCombinedCustomization(['id' => $programId], 'edit', null,
            ['widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'filepath', 'atts', 'value']);
        return $programInformation;
    }
    function downloadDropboxFile($dropboxFilePath, $userId){
        return ($this->dropboxAccessToken = $this->user->getDropboxUserAccessToken($userId)) && !empty($this->localFilePath = Dropbox::downloadFile($dropboxFilePath, $this->dropboxAccessToken));
    }
}
?>
