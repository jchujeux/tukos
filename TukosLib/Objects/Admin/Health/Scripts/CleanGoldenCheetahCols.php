<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class CleanGoldenCheetahCols {

    function __construct($parameters){ 
        $store        = Tfk::$registry->get('store');
        try{
            $tablesAndColumns = [
                'sptsessions' => ['toDrop' => ['gctriscore', 'feeling'], 'toRename' => ['gcavghr' => 'avghr', 'gc95hr' => 'hr95', 'gctrimphr' => 'trimphr', 'gch4time' => 'h4time', 'gch5time' => 'h5time', 'gcavgpw' => 'avgpw', 'gctrimppw' => 'trimppw', 'gcmechload' => 'mechload']],
                'physiopersodailies' => ['toDrop' => ['mechload', 'mechanicalload'], 'toRename' => ['gcmechload' => 'mechload']],
                'physiopersosessions' => ['toDrop' => ['mechload', 'mechanicalload'], 'toRename' => ['gcmechload' => 'mechload']],
                
            ];
            foreach ($tablesAndColumns as $table => $cols){
                $columnsStructure = Utl::toAssociative($store->tableColsStructure($table), 'Field');
                foreach ($cols['toDrop'] as $col){
                    try{
                        if (isset($columnsStructure[$col])){
                            $alterStmt = $store->pdo->query("ALTER TABLE `$table` DROP `$col`");
                            echo "<br>column to delete not found: $col in table $table";
                        }else{
                            echo "<br> column not found:$col in table $table. Could not delete";
                        }
                    }catch(\Exception $e){
                        echo '<br>' . "table: $table, col: $col" .  $e->getMessage();
                    }
                }
            
                foreach ($cols['toRename'] as $col => $newCol){
                    if (isset($columnsStructure[$col])){
                        try{
                            $dbColStructure = $columnsStructure[$col];
                            $dbColDescription = $dbColStructure['Type'] . ($dbColStructure['Null'] === "YES" ? " NULL " : '') . (($default = $dbColStructure['Default'] === 'Pas de défaut') ? '' : ("  default  " . ($default === false ? ' NULL ' : $default))) . ($dbColStructure['Key'] === 'PRI' ? '  primarykey ' : '');
                            $alterStmt = $store->pdo->query("ALTER TABLE `$table` CHANGE COLUMN `$col` `$newCol` $dbColDescription");
                            echo "<br>renamed column $col to $newCol in table $table";
                        }catch(\Exception $e){
                            echo '<br>' . $e->getMessage();
                        }
                    }else{
                        echo "<br>could not rename column $col in table $table. Column not found";
                    }
                }
            }
            foreach (array_merge($tablesAndColumns['sptsessions']['toRename'], ['gcflag' => 'synchroflag']) as $col => $newCol){
                $renameStmt = $store->pdo->query("UPDATE `customviews` SET customization= REPLACE(customization, '$col', '$newCol')");
                if ($rows = $renameStmt->rowCount()){
                    echo "<br>customviews.customization - renaming $col: $rows rows affected";
                }
                $store->pdo->query("UPDATE `tukos` SET custom = REPLACE(custom, '$col', '$newCol')");
                if ($rows = $renameStmt->rowCount()){
                    echo "<br>tukos.custom - renaming $col: $rows rows affected";
                }
            }
        }catch(\Exception $e){
            echo '<br>  Exception in CleanGoldenCheetahCols: ' . $e->getMessage();
        }
    }
}
?>
