<?php
/*
 * class for the MacAddresses tukos object 
 * - a macAddress has as a parentid a hostid using that Mac address
 * - since a mac address may be shared between several hosts, several entries may have the same (mac, vendor) pair.
 * - two entries with the same mac address but differnet vendors are considered different. When detected, the trust value is set as suspect. Can be changed manually by an autorized user
 * - a given host can have different mac addresses
 */
namespace TukosLib\Objects\ITM\MacAddresses;

use TukosLib\Objects\ITM\ITM;
use TukosLib\Objects\ITM\AbstractModel;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['mac'           => 'VARCHAR(80)  DEFAULT NULL',
                            'vendor'        => 'VARCHAR(80)  DEFAULT NULL',
                            'trust'         =>  "ENUM ('" . implode("','", ITM::$trustOptions) . "')",
                            'reason'        => 'VARCHAR(80)  DEFAULT NULL',
                            ];
        parent::__construct($objectName, $translator, 'macaddresses', ['parentid' => ['hosts']], [], $colsDefinition, '', ['trust']);
    }

    /*
     * Returns the macAddresses object that matches the mac and vendor, creating a new one if needed and with appropriate trust and hostid.
     */
    public function getOrCreate($mac, $vendor){
        $matchingMacs = $this->getAllExtended(['where' => ['mac' => $mac], 'cols' => ['id', 'name', 'parentid', 'vendor',  'trust', 'reason'], 'orderBy' => ['updated' => 'DESC']]);
        if ($matchingMacs){
            if (count($matchingMacs) === 1){
                $matchingMac = $matchingMacs[0];
                if ($matchingMac['vendor'] != $vendor){//we don't test for strict inequality as if no vendor, $vendor is false, and $matchingMac['vendor'] = ''
                    $matchingMac = $this->insertExtended(['name' => '', 'mac' => $mac, 'vendor' => $vendor, 'trust' => 'SUSPECT', 'reason' => 'macHasDifferentVendor'], true);
                }
                return $matchingMac;
            }else{//pre-existing multi mac address situation (e.g. shared NIC between several hosts): no way to assume which macObject is connecting
                foreach ($matchingMacs as $key => $macObject){
                    if ($macObject['vendor'] != $vendor){
                        unset($matchingMacs[$key]);
                    }
                }
                if (count($matchingMacs) === 1){
                    return array_pop($matchingMacs);
                }else if (count($matchingMacs) === 0){/* New vendor found for that mac: this is suspect. Still store as a new mac, with trust value 'SUSPECT' and no hostid */
                    $matchingMac = $this->insertExtended(['name' => '', 'mac' => $mac, 'vendor' => $vendor, 'trust' => 'SUSPECT', 'reason' => 'macHasDifferentVendor'], true);
                    return $matchingMac;
                }else{/* There are more than one (mac, vendor) pairs in the table: several hosts share the same NIC. Choose the most recent one */
                    return array_pop($matchingMacs);
                }
            }
        }else{// new mac address
            $matchingMac = $this->insertExtended(['name' => '', 'mac' => $mac, 'vendor' => $vendor, 'trust' => 'UNKNOWN', 'reason' => 'newMac'], true); 
            return $matchingMac;               
        }
    }    

}
?>
