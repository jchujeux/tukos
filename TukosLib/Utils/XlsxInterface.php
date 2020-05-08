<?php
namespace TukosLib\Utils;

class XlsxInterface{

    private static $refDate = false;
    
    
    private function getSharedStrings(){
        if (empty($this->sharedStrings)){
            $this->sharedStrings = simplexml_load_string($this->zip->getFromName('xl/sharedStrings.xml'));
            foreach ($this->sharedStrings->getDocNamespaces() as $strPrefix => $strNamespace){
                if (strlen($strPrefix) == 0){
                    $strPrefix = 'a';
                }
                $this->sharedStrings->registerXPathNamespace($strPrefix, $strNamespace);
            }
        }
        return $this->sharedStrings;
    }
    private function updateSharedString(){
        return $this->zip->addFromString('xl/sharedStrings.xml', $this->sharedStrings->asXml());
    }
    public static function refDate(){
        return self::$refDate ? self::$refDate : self::$refDate = new \DateTime('1899-12-30'); // excel date format uses number of days since this $refDate
    }
    public function dateInDays($date){
        return substr(XlsxInterface::refDate()->diff(new \DateTime($date))->format('%R%a'), 1);
        
    }
    public static function date($dateInDays){
        return (clone(self::refDate()))->add(new \DateInterval('P' . $dateInDays . 'D'))->format('Y-m-d');
    }
    public function open($pFilename){      
        $this->zip = new \ZipArchive();
        $this->zip->open($pFilename);
    }
    public function close(){
        if (!empty($this->sharedStringIsModified)){
            $this->updateSharedString();
        }
        $this->zip->close();
    }
    public function getSheet($sheetNumber){
        $sheetFilePath = 'xl/worksheets/sheet' . $sheetNumber . '.xml';
        $sheetContent = $this->zip->getFromName($sheetFilePath);
        $xmlObject = simplexml_load_string($sheetContent);        
        return $xmlObject;
    }
    public function updateSheet($sheetNumber, $sheetXmlObject){
        return $this->zip->addFromString('xl/worksheets/sheet' . $sheetNumber . '.xml', $sheetXmlObject->asXml());
    }
    public function numberOfRows($sheet){
        return count($sheet->sheetData->row);
    }
    public function getCell($sheet, $row, $col){
        $xmlRow = $row - 1;
        while($sheet->sheetData->row[$xmlRow]['r'][0] != $row){
            $xmlRow += -1;
        }
        return $sheet->sheetData->row[$xmlRow]->c[$col-1];
    }
    public function getCellValue($sheet, $row, $col){
        $cell = $this->getCell($sheet, $row, $col);
        if ( (string) $cell['t'] === 's'){
            return (string) $this->getSharedStrings()->si[intval((string)$cell->v)]->t;
        }else{
            return (string) $cell->v;
        }
    }
    public function setCellValue($sheet, $value, $row, $col){
        $cell = $this->getCell($sheet, $row, $col);
        if (is_string($value)){// shared string handling
            $i = 0;
            foreach ($this->getSharedStrings()->xpath('//a:t') as $text){
                if ((string) $text === $value){
                    $match = true;
                    break;
                }else{
                    $i += 1;
                }
            }
            if (empty($match)){
                $this->sharedStringIsModified = true;
                $si = $this->sharedStrings->addChild('si');
                $t = $si->addChild('t', $value);
                //$t->addAttribute('xml:space', 'preserve');
            }
            dom_import_simplexml($cell)->setAttribute('t', 's');
            return $cell->v = $i;
        }else{
            dom_import_simplexml($cell)->setAttribute('t', 'n');
            return $cell->v = str_replace(',', '.', (string)$value);
        }
    }
}
?>