<?php
/**
 *
 * class for the winestock tukos object, i.e; the wine inventory (stored in a cave)
 */
namespace TukosLib\Objects\Wine\Stock;

use TukosLib\Objects\Wine\Wine;
use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk;

class Model extends AbstractModel {
     
    function __construct($objectName, $translator=null){
        $colsDefinition = [ 'cellarid'      => 'INT(11) NOT NULL',
                            'vintage'       => 'INT(11) NOT NULL',
                            'laydown'       => "ENUM('" . implode("','", Wine::$laydownOptions) . "')",
                            'format'        => "ENUM('" . implode("','", Wine::$formatOptions) . "')",
                            'quantity'      => 'INT(11) NOT NULL',];

        parent::__construct($objectName, $translator, 'winestock', ['parentid' => ['wines'], 'cellarid' => ['winecellars']], [], $colsDefinition, [['cellarid']], ['laydown', 'format']);
    }


    function summary($activeUserFilters = null){// argument is ignored here
        $totals = $this->getAll(['where' => $this->user->filter([], $this->objectName), 'cols' => ['count(*)']]);
        $filteredTotals = $this->getAll(['where' => $this->user->filter($this->user->getCustomView($this->objectName, 'overview', isset($this->paneMode) ? $this->paneMode : 'Tab', ['data', 'filters', 'overview']), $this->objectName), 
            'cols' => ['format', 'count(*)', "sum(quantity)"], 'groupBy' => ['format']]);
        $return['totalrecords'] = $totals[0]['count(*)'];
        $return['filteredrecords'] = 0;
        $return['sumquantity'] = 0;
        foreach ($filteredTotals as $total){
            $return[$total['format']]    = $total['sum(quantity)'];
            $return['filteredrecords']  += $total['count(*)'];
            $return['sumquantity']      += $total['sum(quantity)'];
        }
        return $return;
    }    
}
?>
