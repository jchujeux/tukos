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
                  'parentid-s'     => 'tukos application database name (default provided in interactive mode)',
                  'db-s'		      => 'tukos application database name (default provided in interactive mode)',
                 'class=s'        => 'this class name (mandatory)',
                 'searchstring=s' => 'string to search in kpis names to eliminate (mandatory)',
                ]);
            $searchString = $options->getOption('searchstring');
            $workoutsModel = $objectsStore->objectModel('sptworkouts');
            $workoutsToEdit = $workoutsModel->getAll(['cols' => ['id', 'kpiscache'], 'where' => [
                ['col' => 'kpiscache', 'opr' => 'IS NOT NULL', 'values' => null], ['col' => 'kpiscache', 'opr' => 'RLIKE', 'values' => $searchString]
            ]], ['kpiscache' => []]);
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
