<?php
/**
 * 
 */
namespace TukosLib\Objects\Admin\Health\Scripts;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Objects\Directory;
use TukosLib\TukosFramework as Tfk;

class GainLossCompute {

    function __construct(){ 
        $user         = Tfk::$registry->get('user');
        try{
            $options = new \Zend_Console_Getopt([
                'class=s'      => 'this class name',
                'parentid-s'   => 'parent id (optional, default is user->id())',
            ]);
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Ods();
            $reader->setLoadSheetsOnly(["Feuille4"]);
            $spreadsheet = $reader->load("f:/documents/private/impots/regul2017/Calcul plus value et dividendes Schwab.ods");
            $worksheet = $spreadsheet->getActiveSheet();
            $getCell = function($col, $row) use ($worksheet) {
            	return $worksheet->getCellByColumnAndRow($col, $row);
            };
            $firstRow = 14; $lastRow = 453; $iDateSettled = 2; $iQty = 3; $iTotalValue = 7; $oDateSettled = 10; $oQty = 11; $oTotalValue = 15; $tQty = 16;
            $this->stock = []; $gain = 0.0;
            $fromRow = 21; $toRow = 295; $firstStockRow = max([$firstRow, $fromRow-1]);
            $requiredQty = $fromRow === $firstRow ? 0 : $getCell($tQty, $firstStockRow)->getOldCalculatedValue();
            while ($requiredQty && $firstStockRow >= $firstRow){// build initial stock
            	$iDateSettledValue = $getCell($iDateSettled, $firstStockRow)->getFormattedValue();
                if ($iDateSettledValue){
            		$unitValue = $getCell($iTotalValue, $firstStockRow)->getOldCalculatedValue()/($qty=$getCell($iQty, $firstStockRow)->getCalculatedValue());
                	if ($qty  < $requiredQty){
            			$requiredQty += -$qty;
            			$firstStockRow += -1;
            		}else{
            			$qty = $requiredQty;
            			$requiredQty = 0;
            		}
                	array_unshift($this->stock, ['dateSettled' => $iDateSettledValue, 'quantity' => $qty, 'unitValue' => $unitValue]);
                }else{
                	$firstStockRow += -1;
                }
            }
            if ($requiredQty){
            	echo "Not enough stock to initialize gain/loss computation - Missing stock items: $requiredQty\n";
            }else{
	            for ($row = $fromRow; $row <= $toRow; ++$row){
	            	$iDateSettledValue = $getCell($iDateSettled, $row)->getFormattedValue();
	            	if ($iDateSettledValue){
	            		$this->stock[] = ['dateSettled' => $iDateSettledValue, 'quantity' => $qty = $getCell($iQty, $row)->getCalculatedValue(), 'unitValue' => $getCell($iTotalValue, $row)->getCalculatedValue()/$qty];
	            	}
	            	$oDateSettledValue = $getCell($oDateSettled, $row)->getFormattedValue();
	            	if ($oDateSettledValue){
	            		$gain += $this->removeFromStock($qty = $getCell($oQty, $row)->getCalculatedValue(), $getCell($oTotalValue, $row)->getOldCalculatedValue()/$qty);
	            	}
	            }
            }
            echo "The gain is: $gain\n";
            echo 'the stock is: ' . var_dump($this->stock);
        }catch(Getopt_exception $e){
            Tfk::error_message('on', 'an exception occured while parsing command arguments : ', $e->getUsageMessage());
        }
    }
    
    function removeFromStock($quantity, $unitValue){
    	$gain = 0.0;
    	while ($quantity){
    		$firstInStock = reset($this->stock);
    		$firstInStockKey = key($this->stock);
    		if ($quantity < $firstInStock['quantity']){
    			$gain += $quantity * ($unitValue - $firstInStock['unitValue']);
    			$this->stock[$firstInStockKey]['quantity'] += -$quantity;
    			$quantity = 0;
    		}else{
    			$gain += ($qty = $firstInStock['quantity']) * ($unitValue - $firstInStock['unitValue']);
    			$quantity += - $qty;
    			unset($this->stock[$firstInStockKey]);
    		}
    	}
    	return $gain;
    }
}
?>
