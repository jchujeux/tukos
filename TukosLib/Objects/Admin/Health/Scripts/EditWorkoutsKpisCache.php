<?php
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\StoreUtilities as SUtl;


use TukosLib\TukosFramework as Tfk;

class EditWorkoutsKpisCache {

    function __construct($parameters){ 
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new \Zend_Console_Getopt(
                ['app-s'		  => 'tukos application name (default provided in interactive mode)',
                 'rootUrl-s'     => 'tukos root url (default provided in interactive mode)',
                  'parentid-s'     => '(default provided in interactive mode)',
                  'db-s'		      => 'tukos application database name (default provided in interactive mode)',
                 'class=s'        => 'this class name (mandatory)',
                    'planid-s'   => 'The training plan for which applying this script (optional)',
                    'fromdate-s' => 'optional',
                    'todate-s'   => 'optional',
                    'searchstring=s' => 'string to search in kpis names to eliminate (mandatory)',
                ]);
            $searchString = $options->getOption('searchstring');
            $workoutsModel = $objectsStore->objectModel('sptworkouts');
            $planId = $options->getOption('planid'); $fromDate = $options->getOption('fromdate'); $toDate = $options->getOption('todate');
            $where = [['col' => 'kpiscache', 'opr' => 'IS NOT NULL', 'values' => null], ['col' => 'kpiscache', 'opr' => 'RLIKE', 'values' => $searchString]];
            if ($planId){
                $where['parentid'] = $planId;
            }
            if ($fromDate){
                $where[] = ['col' => 'startdate', 'opr' => '>=', 'values' => $fromDate];
            }
            if ($toDate){
                $where[] = ['col' => 'startdate', 'opr' => '<=', 'values' => $toDate];
            }
            
            $workoutsToEdit = $workoutsModel->getAll(['cols' => ['id', 'kpiscache'], 'where' => $where], ['kpiscache' => []]);
            array_walk($workoutsToEdit, function(&$item) use ($workoutsModel, $searchString) {
                foreach ($item['kpiscache'] as $kpi => $value){
                    if (str_contains($kpi, $searchString)){
                        $item['kpiscache'][$kpi] = '~delete';
                    }
                }
                $workoutsModel->updateOne($item);
            });
            echo 'done - number of workouts items edited: ' . count($workoutsToEdit);                
                
        }catch(\Zend_Console_Getopt_Exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command arguments in EditWorkoutsKpisCache: ', $e->getUsageMessage());
        }
    }
}
?>
