<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class SpreadsheetReader {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        try{
            $options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
            //$reader->setLoadSheetsOnly(["Feuille4"]);
            //$spreadsheet = $reader->load("f:/documents/private/impots/regul2017/Calcul plus value et dividendes Schwab.ods");
            $spreadsheet = $reader->load("f:/tukos/tmp/test.ods");
            $toto = 'toto';
            
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
