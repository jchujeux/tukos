<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;


use TukosLib\TukosFramework as Tfk;

class RecomputeStreamsCorrectionsAndKpis {

    function __construct($parameters){ 
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new \Zend_Console_Getopt(
                ['app-s'	  => 'tukos application name (default provided in interactive mode)',
                 'rootUrl-s'  => 'tukos root url (default provided in interactive mode)',
                  'parentid-s'=> ' (default provided in interactive mode)',
                  'db-s'	  => 'tukos application database name (default provided in interactive mode)',
                 'class=s'    => 'this class name (mandatory)',
                 'planid=s'   => 'The training plan for which applying this script (mandatory)',
                 'fromdate-s' => 'optional, if not provided the plan fromdate is used',
                 'todate-s'   => 'optional, if not provided the plan enddate is used',
                 'corrections-s' => 'optional, if not provided, corrections are computed and replace existing ones, if "remove" existing corrections are removed, if "skip", no corrections is computed nor applied'
                ]);
            $planModel = $objectsStore->objectModel('sptplans');
            
            $planModel->updateCorrectedStreamsAndKpis ($options->getOption('planid'), ['fromdate' => $options->getOption('fromdate'), 'todate' => $options->getOption('todate'), 'corrections' => $options->getOption('corrections')]);

            echo 'done';                
                
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in EditWorkoutsKpisCache: ', $e->getUsageMessage());
        }
    }
}
?>
