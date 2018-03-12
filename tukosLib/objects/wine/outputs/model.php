<?php
/**
 *
 * class for the wine input tukos object, i.e; the wines entered into the winestock
 */
namespace TukosLib\Objects\Wine\Outputs;

use TukosLib\Objects\Wine\Wine;
use TukosLib\Objects\Wine\AbstractModel;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition = [ 'stockid'  => 'INT(11) NOT NULL',
                            'exitdate' => "date NOT NULL DEFAULT '0000-00-00'",
                              'status' => "ENUM ('" . implode("','", Wine::$statusOptions) . "')",
                            'quantity' => 'INT(11) NOT NULL',];
        parent::__construct($objectName, $translator, 'wineoutputs', ['parentid' => ['winecellars'], 'stockid' => ['winestock']], [], $colsDefinition, ' KEY (`stockid`)', ['status']);
    }
    
    function initialize($init=[]){
        $result = parent::initialize($init);
        $result['status'] = 'PENDING';
        return $result;
    }
    /*
     * Perform the cellar stock update for the selected ids
     */
    function process($idsToProcess){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $winestockObject = $objectsStore->objectModel('winestock');

        $notPending = $this->tr('notPending');
        $stockItemNotFound = $this->tr('StockItemNotFound');
        $negativeResultsNotProcessed = $this->tr('NegativeResultNotProcessed');
        $remainingStock = $this->tr('RemainingStock');

        $feedback = [];
        foreach ($idsToProcess as $id){
            $item = $this->getOne(['where' => ['id' => $id], 'cols' => ['parentid', 'stockid', 'status', 'quantity']]);
            if ($item['status'] !== 'PENDING'){
                $feedback[$notPending][] = $id;
            }else{
                $stockitem = $winestockObject->getOne(['where' => ['id' => $item['stockid']], 'cols' => ['cellarid', 'quantity']]);
                if ($stockitem['cellarid'] !== $item['parentid']){
                    $feedback[$stockItemNotFound][] =  $id;
                }else{
                    $targetQty = $stockitem['quantity'] - $item['quantity'];
                    if ($targetQty < 0){
                        $feedback[$negativeResultsNotProcessed][] = '(' . $id . ',' . $targetQty . ')';
                    }else{
                        $winestockObject->updateOne(['quantity' => $targetQty], ['where' => ['id' => $item['stockid']]]);
                        $this->updateOne(['status' => 'PROCESSED'], ['where' => ['id' => $id]]);
                        $feedback[$remainingStock][] = '(' . $item['stockid'] . ',' . $targetQty .')';
                    }

                }
            }
        }
        Feedback::add($feedback);
    }        
}
?>
