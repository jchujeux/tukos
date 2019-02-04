<?php
/**
 *
 * class for the wine input tukos object, i.e; the wines entered into the winestock
 */
namespace TukosLib\Objects\Wine\Inputs;

use TukosLib\Objects\Wine\Wine;
use TukosLib\Objects\Wine\AbstractModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator){
        $colsDefinition = [ 'winesid'       => 'INT(11)',
                            'entrydate'     =>  "date",
                            'status'        => "ENUM ('" . implode("','", Wine::$statusOptions) . "')",
                            'vintage'       => 'INT(11) NOT NULL',
                            'cost'          =>  "DECIMAL (5, 2)",
                            'whereobtained' =>  "ENUM ('" . implode("','", Wine::$whereObtainedOptions) ."')",
                            'format'        =>  "ENUM ('" . implode("','", Wine::$formatOptions) ."')",
                            'quantity'      =>  'INT(11) NOT NULL',];

        parent::__construct($objectName, $translator, 'wineinputs', ['parentid' => ['winecellars'], 'winesid' => ['wines']], [], $colsDefinition, [['wineid']], ['whereobtained', 'format']);
    }
    
    function initialize($init=[]){
        $result = parent::initialize($init);
        $result['status'] = 'PENDING';
        return $result;
    }
    /*
     * For selected input Ids with pending status, add them to the corresponding winecellar stock
     */  
    function process($idsToProcess){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $winestockObject = $objectsStore->objectModel('winestock');

        $notPending    = $this->tr('notPending');
        $newStockValue = $this->tr('newStockValue');
        $newStockItem  = $this->tr('newStockItem');

        $feedback = [];
        foreach ($idsToProcess as $id){
            $item = $this->getOne(['where' => ['id' => $id], 'cols' => ['parentid', 'name', 'winesid', 'status', 'vintage', 'format', 'quantity', 'permission', 'contextid']]);
            if ($item['status'] !== 'PENDING'){
                $feedback[$notPending][] = $id;
            }else{
                $stockitem = $winestockObject->getOne(
                    ['where' => ['parentid' => $item['winesid'], 'vintage' => $item['vintage'], 'format' => $item['format'], 'cellarid' => $item['parentid']],
                     'cols' => ['id', 'quantity']
                    ]);
                if (!empty($stockitem)){
                    $stockitem['quantity'] += $item['quantity'];
                    $winestockObject->updateOne(['quantity' => $stockitem['quantity']], ['where' => ['id' => $stockitem['id']]]);
                    $this->updateOne(['status' => 'PROCESSED'], ['where' => ['id' => $id]]);
                    $feedback[$newStockValue][] = $id;
                }else{
                    unset($item['status']);
                    $item['cellarid'] = $item['parentid'];
                    $item['parentid'] = $item['winesid'];
                    unset($item['winesid']);
                    $insert = $winestockObject->insert($item, true); 
                    $this->updateOne(['status' => 'PROCESSED'], ['where' => ['id' => $id]]);
                    $feedback[$newStockItem][] = $id;
                }
            }
        }
        Feedback::add($feedback);
    }  
}
?>
