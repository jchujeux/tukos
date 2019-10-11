<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\XlsxInterface;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Dropbox as Dropbox;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\Objects\Sports\Programs\SessionsFeedbackUtils;
use TukosLib\TukosFramework as Tfk;

class SessionsFeedback extends ObjectTranslator{
    use SessionsFeedbackUtils;
    function __construct($version){
        parent::__construct('sptprograms');
        $this->instantiateVersion($version);
        $this->user = Tfk::$registry->get('user');
    }
    public function downloadPerformedSessions($query, $atts){
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        if (!empty($this->localFilePath = Dropbox::downloadFile($atts['filepath'], $this->user->dropboxAccessToken()))){
            $dateInDays = $this->setWorksheetRow($atts['synchrostart']);
            $downloadedSessions = 0; $dateCol = 3;
            $synchroEndDays = XlsxInterface::dateInDays($atts['synchroend']) + 1;
            while ($dateInDays <= $synchroEndDays){
                $performedSession = array_merge(
                    ['parentid' => $query['id'], 'sportsman' => $atts['parentid'], 'startdate' => XlsxInterface::date($dateInDays), 'mode' => 'performed'],
                    $this->version->sheetRowToStore($this->workbook, $this->sheet, $this->row)
                );
                $performedSession = $this->version->sheetRowToStore($this->workbook, $this->sheet, $this->row);
                if (!empty(array_filter($performedSession))){
                    $performedSession = array_merge(['parentid' => $query['id'], 'sportsman' => $atts['parentid'], 'startdate' => XlsxInterface::date($dateInDays), 'mode' => 'performed'], $performedSession);
                    $sessionsModel->updateOne($performedSession, ['where' => $this->user->filter(['startdate' => $performedSession['startdate'], 'mode' => 'performed'])], true);
                    $downloadedSessions += 1;
                }
                //$performedSession = json_decode(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode($performedSession)), true);
                $this->row += 1;
                $dateInDays = $this->workbook->getCellValue($this->sheet, $this->row, $dateCol);
            }
            Feedback::add($downloadedSessions . ' ' . $this->tr('sessionsweredownloaded'));
            $this->workbook->close();
        }
    }
    public function uploadPerformedSessions($query, $atts){
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        $sessions = $sessionsModel->getAll(['where' => $this->user->filter(['parentid' => $query['id'], 'mode' => 'performed', ['col' => 'startdate', 'opr' => '>=', 'values' => $atts['synchrostart']],
                ['col' => 'startdate', 'opr' => '<=', 'values' => $atts['synchroend']] ], 'sptsessions'),
            'orderBy' => ['startdate' => ' ASC'],  'cols' => array_merge($this->version->sheetCols(), ['startdate'])]);//['startdate', 'coachcomments', 'coachweeklycomments']]);
        if (empty($sessions)){
            Feedback::add($this->tr('nosessiontoupdate'));
        }else{
            if (!empty($this->localFilePath = Dropbox::downloadFile($atts['filepath'], $this->user->dropboxAccessToken()))){
                $dateInDays = $this->setWorksheetRow($atts['synchrostart']);
                $synchroEndDays = XlsxInterface::dateInDays($atts['synchroend']) + 1;
                $cellsUpdated = 0; $dateCol = 3;
                foreach ($sessions as $session){
                    $startDate = XlsxInterface::date($dateInDays);
                    $sessionDate = Utl::extractItem('startdate', $session);
                    while($startDate < $sessionDate){
                        $this->row += 1;
                        $dateInDays = $this->workbook->getCellValue($this->sheet, $this->row, $dateCol);
                        $startDate = XlsxInterface::date($dateInDays);
                    }
                    $cellsUpdated += $this->version->storeToSheetRow($session, $this->workbook, $this->sheet, $this->row);
                    $this->row += 1;
                    $dateInDays = $this->workbook->getCellValue($this->sheet, $this->row, $dateCol);
                }
                if (!empty($cellsUpdated)){
                    $this->workbook->updateSheet(1, $this->sheet);
                    $this->workbook->close();
                    $returnData = Dropbox::uploadFile($atts['filepath'], $this->user->dropboxAccessToken());
                    Feedback::add($this->tr('nbcellsupdated') . ': ' . $cellsUpdated);
                }else{
                    Feedback::add($this->tr('Noupdateneeded'));
                    $this->workbook->close();
                }
            }
        }
    }
    public function removePerformedSessions($query, $atts){
        $sessionsModel = Tfk::$registry->get('objectsStore')->objectModel('sptsessions');
        $count = $sessionsModel->delete($this->user->filter(
            ['parentid' => $query['id'], 'mode' => 'performed', ['col' => 'startdate', 'opr' => '>=', 'values' => $atts['synchrostart']], ['col' => 'startdate', 'opr' => '<=', 'values' => $atts['synchroend']] ], 'sptsessions')
        );
        Feedback::add($this->tr('doneEntriesDeleted') . $count);
    }
    
}
?>
