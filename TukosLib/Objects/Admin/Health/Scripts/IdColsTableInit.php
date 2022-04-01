<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class IdColsTableInit {

    var $noParentWhere = [
        ['col' => 'id'      , 'opr' => '>'      , 'values' => 0],
        [   ['col' => 'parentid', 'opr' => 'IS NULL', 'values' => null],
            ['col' => 'parentid', 'opr' => 'LIKE'   , 'values' => 0   , 'or' => true],
        ],
    ];
    var $parentWhere = [
        ['col' => 'id'      , 'opr' => '>'      , 'values' => 0],
        [   ['col' => 'parentid', 'opr' => 'IS NOT NULL', 'values' => null],
            ['col' => 'parentid', 'opr' => '<>'   , 'values' => 0],
        ],
    ];

    function __construct($parameters){ 
        $appConfig    = Tfk::$registry->get('appConfig');
        $user         = Tfk::$registry->get('user');
        $store        = Tfk::$registry->get('store');
        $objectsStore = Tfk::$registry->get('objectsStore');
        try{
            $options = new Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
                'parentTable-s'=> 'parent script table (optional, required if parentid is not a users)',
            ]);
            $store->emptyTable('idcols');
            $idCols = [];
            $objectsToConsider = array_intersect(Directory::getObjs(), $store->tableList()());
            foreach ($objectsToConsider as $objectName){
                try {
                    $objectModel = $objectsStore->objectModel($objectName);
                    $idCols = $objectModel->idCols;
                    $objectModel->setIdCols($idCols);
                    foreach ($idCols as $idCol){
                        $results = $objectModel->getAll([
                             'where' => [], 
                              'cols' => ['count(*)', $idCol],
                           'groupBy' => [$idCol]
                        ]);
                        $objectModel->extendValuesFromObjectTables($results);
                        foreach ($results as $result){
                            Tfk::log_message('on', 'Table: ' . $objectName . ' - processing row: ', $result);
                            $id   = $result[$idCol]/*['id']*/;
                            if ($result['count(*)'] > 0){
                                if (isset($values[$id][$idCol])){
                                    if (isset($values[$id][$idCol][$objectName])){
                                        $values[$id][$idCol][$objectName] += $result['count(*)'];
                                    }else{
                                        $values[$id][$idCol][$objectName]  = $result['count(*)'];
                                    }   
                                }else{
                                    if (!isset($values[$id])){
                                        $values[$id] = $result[$idCol];
                                    }
                                    $values[$id][$idCol]  = [$objectName => $result['count(*)']];
                                }
                            }
                        }
                    }
                }catch(\Exception $e){
                    Tfk::error_message('on', ' Exception in IdColsTableInit: ', $e->getMessage());
                }
            }
            foreach ($values as $id => $value){
                $idColsValues = array_diff(array_keys($value), ['id', 'table', 'name']);
                foreach ($idColsValues as $idCol){
                    $value[$idCol] = json_encode($value[$idCol]);
                }
                $store->insert($value, 'idcols');
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command aguments : ', $e->getUsageMessage());
        }
    }
}
?>
