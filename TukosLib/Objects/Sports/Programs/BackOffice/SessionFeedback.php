<?php
namespace TukosLib\Objects\Sports\Programs\BackOffice;

//use TukosLib\Objects\AbstractView;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\ViewUtils;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Dropbox;
use TukosLib\Utils\XlsxInterface;

class SessionFeedback extends ObjectTranslator{
    
    function __construct(){
        parent::__construct('sptprograms');
        $this->isMobile = Tfk::$registry->isMobile; 
        $this->user     = Tfk::$registry->get('user');
        $this->objectsStore     = Tfk::$registry->get('objectsStore');
        $this->view  = $this->objectsStore->objectView('sptsessions');
        $this->dataWidgets = array_merge(
            ['athlete'      => ViewUtils::textBox($this, 'name', ['atts' => ['edit' => ['disabled' => true, 'style' => ['width' => '100%']]]]), 'date'   => ViewUtils::tukosDateBox($this, 'date', ['atts' => ['edit' => ['disabled' => true]]]), 
             'parentid' => ViewUtils::textBox($this, 'parentid', ['atts' => ['edit' => ['hidden' => true]]]), 
                //'name' => ViewUtils::textArea($this, 'theme'),
                'duration' => ViewUtils::textBox($this, [['tr', 'duration'], ['no', ' ('], ['tr', 'minute'], ['no', 's)']], ['atts' => ['edit' => ['style' => ['width' => '8em']]]]),
            ],
            array_intersect_key($this->view->dataWidgets, array_flip(['name', 'sport', 'distance', 'elevationgain'/*, 'sensations', 'perceivedeffort', 'mood'*/, 'feeling', 'athletecomments', 'coachcomments', 'athleteweeklyfeeling', 'coachweeklycomments']))
        );
        $this->dataElts = array_values(array_diff(array_keys($this->dataWidgets), ['athlete', 'date']));
        $this->dataWidgets['coachcomments']['atts']['edit']['disabled'] = true;
        $this->dataWidgets['coachweeklycomments']['atts']['edit']['disabled'] = true;
        $this->dataLayout = [
            'tableAtts' => ['cols' => 1, 'customClass' => 'labelsAndValues', 'orientation' => 'vert', 'showLabels' => true],
            'contents' => [
                'row1' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => false, 'label' => '<b>' . $this->view->tr('SessionFeedbackForm') . '</b>'],
                    'contents' => [
                        'col2' => [
                            'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                            'widgets' => ['athlete', 'date', 'parentid'],
                            
                        ]
                    ]
                ],
                'row2' =>[
                    'tableAtts' => ['cols' => 5, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['name'/*, 'sport'*/, 'duration', 'distance', 'elevationgain', 'feeling']
                ],
/*
                'row3' =>[
                    'tableAtts' => ['cols' => 3, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['sensations', 'perceivedeffort', 'mood']
                ],
*/
                'row4' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true],
                    'widgets' => ['athletecomments', 'athleteweeklyfeeling']
                ],
/*
                'row5' => [
                    'tableAtts' => ['cols' => 2, 'customClass' => 'labelsAndValues', 'showLabels' => true, 'label' => '<b>' . $this->view->tr('CoachComments') . '</b>'],
                    'widgets' => ['coachcomments', 'coachweeklycomments']
                ]
*/
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
        $actionWidgets['send'] = ['atts' => ['urlArgs' => ['query' => ['form' => 'SessionFeedback', 'object' => 'sptprograms']]]];
        $actionWidgets['reset'] = ['atts' => ['urlArgs' => ['query' => ['form' => 'SessionFeedback', 'object' => 'sptprograms']], 'includeWidgets' => ['parentid', 'date']]];
        return $actionWidgets;
    }
    function getTitle(){
        return $this->tr('sessiontrackingformtitle');
    }
    function sendOnSave(){
        return ['parentid', 'date'];
    }
    function sendOnReset(){
        return ['parentid', 'date'];
    }
    function get($query, $formValues = []){
        $query = array_merge($query, $formValues);
        Feedback::add($this->tr('Pleaseprovidesessionfeedback'));
        return $this->getPerformedSession($query);
    }
    function save($valuesToSave){
        $programId = Utl::extractItem('parentid', $valuesToSave);
        $this->updatePerformedSession($programId, $valuesToSave);
        return $valuesToSave;
    }
    public function getPerformedSession($query){
        $programId = $query['parentid'];
        $sessionDate = $query['date'];
        $programsModel = $this->objectsStore->objectModel('sptprograms');
        $programInformation = $programsModel->getOne(['where' => ['id' => $programId], 'cols' => ['parentid', 'custom', 'updator']]/*,
            ['custom' => ['edit', 'tab', 'widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'filepath', 'atts', 'value']]*/);
        $dropboxFilePath = $programsModel->getCombinedCustomization(['id' => $programId], 'edit', null, ['widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'filepath', 'atts', 'value']);
        $dropboxAccessToken = $this->user->getDropboxUserAccessToken($programInformation['updator']);
        if ($dropboxAccessToken && !empty($filePath = Dropbox::downloadFile($dropboxFilePath, $dropboxAccessToken))){
            $workbook = new XlsxInterface();
            $workbook->open($filePath);
            $sheet = $workbook->getSheet('1');
            $refDate = new \DateTime('1899-12-30');
            $synchroDay = substr($refDate->diff(new \DateTime($sessionDate))->format('%R%a'), 1);
            
            $dateCol = 3; $row = 2; $downloadedSessions = 0;
            $cols = ['date' => 3, 'theme' => 4, 'duration' => 5, 'distance' => 6, 'elevationgain' => 7, 'feeling' => 8, 'athletecomments' => 9, 'weeklyFeeling' => 10, 'coachComments' => 11, 'coachWeeklyComments' => 12];
            while (($date = $workbook->getCellValue($sheet, $row, $dateCol)) <= $synchroDay) {
                $row += 1;
            }
            if (($date-1) == $synchroDay){
                foreach ($cols as $key => $col){
                    $values[$key] = $workbook->getCellValue($sheet, $row, $col);
                };
                $sportsmanName = SUtl::translatedExtendedName(Tfk::$registry->get('objectsStore')->objectModel('people'), $programInformation['parentid']);
                $performedSession = ['parentid' => $programId, 'athlete' => $sportsmanName, 'startdate' => (new \DateTime('1899-12-30'))->add(new \DateInterval('P' . ($date -1) . 'D'))->format('Y-m-d'),
                    'duration' => $values['duration'], 'distance' =>$values['distance'], 'elevationgain' => $values['elevationgain'],
                    'athletecomments' => $values['athletecomments'], 'athleteweeklyfeeling' => $values['weeklyFeeling'],
                    'coachcomments' => $values['coachComments'], 'coachweeklycomments' => $values['coachWeeklyComments'],
                    'feeling' => $values['feeling'],
                    'name' => $values['theme'], 'date' => $sessionDate
                ];
                $performedSession = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode($performedSession)), true);
            }else{
                $performedSession = $query;
            }
            $workbook->close();
            return $performedSession;
        }
    }
    public function updatePerformedSession($programId, $values){
        $programModel = $this->objectsStore->objectModel('sptprograms');
        $sessionDate = Utl::extractItem('date', $values);
        $programInformation = $programModel->getOne(['where' => ['id' => $programId], 'cols' => ['parentid', 'custom', 'updator']],
            ['custom' => ['edit', 'tab', 'widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'filepath', 'atts', 'value']]);
        $dropboxFilePath = $programModel->getCombinedCustomization(['id' => $programId], 'edit', null, ['widgetsDescription', 'sessionstracking', 'atts', 'dialogDescription', 'paneDescription', 'widgetsDescription', 'filepath', 'atts', 'value']);
        $dropboxAccessToken = $this->user->getDropboxUserAccessToken($programInformation['updator']);
        if ($dropboxAccessToken && !empty($localFilePath = Dropbox::downloadFile($dropboxFilePath, $dropboxAccessToken))){
            $cellsUpdated = 0;
            $workbook = new XlsxInterface();
            $workbook->open($localFilePath);
            $sheet = $workbook->getSheet('1');
            $refDate = new \DateTime('1899-12-30');
            $synchroDay = substr($refDate->diff(new \DateTime($sessionDate))->format('%R%a'), 1);
            
            $dateCol = 3; $row = 2; $updatedCells = 0;
            $cols = ['name' => 4, 'duration' => 5, 'distance' => 6, 'elevationgain' => 7, 'feeling' => 8, 'athletecomments' => 9, 'weeklyFeeling' => 10, 'coachComments' => 11, 'coachWeeklyComments' => 12];
            while (($date = $workbook->getCellValue($sheet, $row, $dateCol)) <= $synchroDay) {
                $row += 1;
            }
            if (($date-1) == $synchroDay){
                foreach ($values as $key => $value){
                    $col = $cols[$key];
                    if ($value != $workbook->getCellValue($sheet, $row, $col)){
                        $workbook->setCellValue($sheet, $values[$key], $row, $col);
                        $cellsUpdated += 1;
                    }
                };
                if (!empty($cellsUpdated)){
                    $workbook->updateSheet(1, $sheet);
                    $workbook->close();
                    $returnData = Dropbox::uploadFile($dropboxFilePath, $dropboxAccessToken);
                    Feedback::add($this->tr('nbcellsupdated') . ': ' . $cellsUpdated);
                }else{
                    $workbook->close();
                    Feedback::add($this->tr('Noupdateneeded'));
                }
            }
        }
    }
}
?>
