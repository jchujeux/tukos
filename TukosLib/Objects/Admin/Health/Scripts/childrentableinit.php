<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use Zend\Console\Getopt;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class ChildrenTableInit {

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
            $stmt = $store->pdo->query("DROP TABLE IF EXISTS `children`", []);
            $stmt = $store->pdo->query("CREATE TABLE `children` (`parentid` int(11), `children` text DEFAULT NULL, PRIMARY KEY (`parentid`)) " .
                                        "ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci", []);
            $children = [];
            $tablesToConsider = array_intersect(Directory::getObjs(), $store->tableList()());
            foreach ($tablesToConsider as $tableName){
                try {
                    $updatedCount = $store->update(['parentid' => 0], ['table' => $tableName, 'where' => [['col' => 'parentid', 'opr' => 'IS NULL', 'values' => null]]]);
                    if ($updatedCount >0){
                        echo 'Table: ' . $tableName . 'Updated count - parentid from null to 0: ' . $updatedCount . '<br>';
                    }
                    $stmt = $store->pdo->query("SELECT count(*), `id` FROM `" . $tableName . "`WHERE `id` = `parentid`");
                    $results = $stmt->fetchAll();
                    if(count($results) > 1 || $results[0]['count(*)'] > 0){
                        Tfk::log_message('on', 'Table: ' . $tableName . ' - rows with id = parentid: ', $results);
                    }
                    $stmt = $store->pdo->query("ALTER TABLE `" . $tableName . "` CHANGE `parentid` `parentid` INT( 11 ) NOT NULL DEFAULT '0'", []);
                    $results = $store->getAll([
                         'table' => $tableName,
                         'where' => [['col' => 'id'      , 'opr' => '>'      , 'values' => 0]], 
                          'cols' => ['count(*)', 'parentid'],
                       'groupBy' => ['parentid']
                    ]);
                    foreach ($results as $result){
                        Tfk::log_message('on', 'Table: ' . $tableName . ' - processing row: ', $result);
                        if ($result['count(*)'] > 0){
                            if (isset($children[$result['parentid']])){
                                if (isset($children[$result['parentid']][$tableName])){
                                    $children[$result['parentid']][$tableName] += $result['count(*)'];
                                }else{
                                    $children[$result['parentid']][$tableName]  = $result['count(*)'];
                                }   
                            }else{
                                $children[$result['parentid']]  = [$tableName => $result['count(*)']];
                            }
                        }
                    }
                }catch(\Exception $e){
                    Tfk::error_message('on', ' Exception in ChildrenTableInit: ', $e->getMessage());
                }
            }
            foreach ($children as $parentid => $itsChildren){
                $store->insert(['parentid' => $parentid, 'children' => json_encode($itsChildren)], ['table' => 'children']);
            }
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command aguments : ', $e->getUsageMessage());
        }
    }
}
?>
