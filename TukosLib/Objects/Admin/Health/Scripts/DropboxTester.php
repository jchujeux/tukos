<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class DropboxTester {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        try{
            $options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $dropbox = new \Dropbox\Dropbox('K-JmlaWwaxsAAAAAAAAop4y0o3HUenMKsMCvSqvz_gsMOUMmkf35C3oy4ykALDlr');
            $dropbox->files->list_folder('');
            $dropbox->files->download('/Coaching Jean Claude/tableau suivi.xlsx', Tfk::$tukosTmpDir . '/tableau suivi.xlsx');
            $toto = 'toto';
            
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
