<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;
use TukosLib\Google\Script;

class GoogleScriptTest {

    function __construct($parameters){ 
        $user         = Tfk::$registry->get('user');
        try{
            $options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $script = Script::get('1dBua1fOU7lBsnSrlSmkuC3ttXdMId6yWJzgU6YYJOV1KXyI76ISkth0H');
            $toto = 'toto';
            
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
}
?>
