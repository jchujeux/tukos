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
use TukosLib\Utils\XlsxInterface;

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
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('SessionFeedbackForm') . '</b>'],
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
                'row4' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['athletecomments', 'athleteweeklyfeeling']
                ],
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
        $actionWidgets['send'] = ['atts' => ['urlArgs' => ['query' => ['form' => $query['form'], 'version' => $query['version'], 'object' => $query['object'], 'parentid' => $query['parentid'], 'date' => $query['date']]]]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => ['form' => $query['form'], 'version' => $query['version'], 'object' => $query['object'], 'parentid' => $query['parentid'], 'date' => $query['date']]]]];
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('sessiontrackingformtitle');
    }
    function sendOnSave(){
        return ['startdate'];
    }
    function sendOnReset(){
        return ['startdate'];
    }
    function get($query, $formValues = []){
        $query = array_merge($query, $formValues);
        Feedback::add($this->tr('Pleaseprovidesessionfeedback'));
        return $this->getPerformedSession($query);
    }
    function save($query, $valuesToSave){
        $programId = Utl::getItem('parentid', $query);
        $this->updatePerformedSession($programId, $valuesToSave);
        return $valuesToSave;
    }
    public function getPerformedSession($query){
        $programId = $query['parentid'];
        $sessionDate = $query['date'];
        $programInformation = $this->getProgramInformation($programId);
        $sportsmanName = SUtl::translatedExtendedName(Tfk::$registry->get('objectsStore')->objectModel('people'), $programInformation['parentid']);
        $performedSession = ['sportsman' => $sportsmanName, 'startdate' => $sessionDate];
        if (!empty($dropboxFilePath = $programInformation['dropboxFilePath'])){
            if ($this->downloadDropboxFile($dropboxFilePath, $programInformation['updator'])){
                if ($this->setWorksheetRow($sessionDate)){
                    $performedSession = array_merge($performedSession, $this->version->sheetRowToForm($this->workbook, $this->sheet, $this->row));
                    //$performedSession = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode($performedSession)), true);
                }
                $this->workbook->close();
            }
        }else{
            $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
            $performedSession = array_merge($performedSession, 
                $sessionsModel->getOne(['where' => $this->user->filter(['startdate' => $sessionDate, 'mode' => 'performed'], 'sptsessions'), 'cols' => array_merge($this->version->formObjectWidgets(), ['duration'])]));
            if ($duration = Utl::getItem('duration', $performedSession)){
                $performedSession['duration'] = Dutl::seconds($duration) / 60;
            }else{
                $performedSession['duration'] = 0;
            }
        }
        return $performedSession;
    }
    public function updatePerformedSession($programId, $values){
        $programInformation = $this->getProgramInformation($programId);
        if (!empty($dropboxFilePath = $programInformation['dropboxFilePath'])){
            if ($this->downloadDropboxFile($dropboxFilePath, $programInformation['updator'])){
                if ($this->setWorksheetRow(Utl::extractItem('startdate', $values))){
                    $cellsUpdated = $this->version->formToSheetRow($values, $this->workbook, $this->sheet, $this->row);
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
            $values['mode'] = 'performed';
            $values['parentid'] = $programId;
            $values['sportsman'] = $programInformation['parentid'];
            if ($duration = Utl::getItem('duration', $values)){
                $values['duration'] = '[' . floatval($values['duration']) . ',"minute"]';
            }
            $sessionsModel->updateOne($values, ['where' => $this->user->filter(['startdate' => $values['startdate'], 'mode' => 'performed'])], true);
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
