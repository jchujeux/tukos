<?php
namespace TukosLib\Objects\Sports\Programs;

use TukosLib\Utils\XlsxInterface;
use TukosLib\Objects\ObjectTranslator;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Dropbox as Dropbox;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\DateTimeUtilities as Dutl;
use TukosLib\TukosFramework as Tfk;

trait SessionsFeedbackUtils{

    public function instantiateVersion($version){
        $versionClass = 'TukosLib\\Objects\\Sports\\Programs\\SessionsFeedback' . $version;
        $this->version = new $versionClass();
    }
    public function setWorksheetRow($sessionDate){
        $this->workbook = new XlsxInterface();
        $this->workbook->open($this->localFilePath);
        $this->sheet = $this->workbook->getSheet('1');
        $synchroDay = XlsxInterface::dateInDays($sessionDate);
        
        $dateCol = 3; $row = 2;
        while (($date = $this->workbook->getCellValue($this->sheet, $row, $dateCol)) <= $synchroDay) {
            $row += 1;
        }
        $this->row = $row;
        $this->rowDate = $sessionDate;
        return (($date-1) == $synchroDay) ? $date : false;
    }    
}
?>
