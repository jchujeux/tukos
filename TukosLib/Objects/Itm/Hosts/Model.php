<?php
/**
 *
 * class for the Hosts tukos object 
 */
namespace TukosLib\Objects\Itm\Hosts;

use TukosLib\Objects\Itm\Itm;
use TukosLib\Objects\Itm\AbstractModel;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['osfamily'      => "ENUM ('" . implode("','", Itm::$osFamilyOptions) . "')",
                            'hosttype'      => "ENUM ('" . implode("','", Itm::$hostTypeOptions) . "')",
                            'lastinvscan'   =>  "timestamp",
                            'lastsecscan'   =>  "timestamp",
                            'trust'         =>  "ENUM ('" . implode("','", Itm::$trustOptions) . "')",
                            'reason'        => 'VARCHAR(80)  DEFAULT NULL',
                            ];
        parent::__construct($objectName, $translator, 'hosts', ['parentid' => ['organizations']], [], $colsDefinition, [], ['osfamily', 'hosttype', 'trust']);
        $this->objectsStore = Tfk::$registry->get('objectsStore');
    }


   /*
    * Update hostid information from scan result contained in host, run at $timeStamp 
    */
    private function updateHost($hostid, $host, $timeStamp){
        $this->updateOne(['id' => $hostid, 'lastinvscan' => $timeStamp]);
        $hostDetailsObj = $this->objectsStore->objectModel('hostsdetails');
        $hostDetailsObj->revise($hostid, $host, $timeStamp);
    }

    private function findOrCreateHost($connexion, $host, $timeStamp){
       /*
        * A trusted or unknown connexion means at this stage that this is a new host
        */
        switch($connexion['trust']){
            case 'TRUSTED':
            case 'UNKNOWN': 
                $newHost = $this->insertExtended(['name' => '', 'lastinvscan' => $timeStamp, 'trust' => 'UNKNOWN', 'reason' => 'new'], true);
                $hostDetailsObj = $this->objectsStore->objectModel('hostsdetails');
                $hostDetailsObj->create($newHost['id'], $host, $timeStamp);
                return $newHost;
                break;
            case 'SUSPECT':
            case 'MALICIOUS':
                Tfk::debug_mode('log', 'connexion is suspect or malicious: tbd connexion, host: ', [$connexion, $host]);
                break;
        }
        return false;
    }

    private function investigateOne($networkId, $host, $timeStamp){
        $mac = $host->getAddress('mac');
        $vendor = $host->getAddress('macvendor');
        $ip  = $host->getAddress('ipv4');

        $where = ['ip' => $ip];
        if ($mac){
            $macAddressesObj = $this->objectsStore->objectModel('macaddresses');
            $macObject = $macAddressesObj->getOrCreate($mac, $vendor);
            $where['macid'] = $macObject['id'];
        }

        $connexionsObj = $this->objectsStore->objectModel('connexions');
        $connexion = $connexionsObj->getOne(
                ['where' => $where, 'cols' => ['id', 'hostid', 'macid', 'ip', 'lastconnect', 'trust', 'reason'], 'orderBy' => ['lastconnect' => 'DESC'], 'limit' => 1]);
        if (!empty($connexion)){
            if (isset($connexion['hostid']) && $connexion['hostid'] > 0){/* same ip, mac (or no mac) and has a host id: we assume this is the same host that may need update*/
                $this->updateHost($connexion['hostid'], $host, $timeStamp);
            }else{
                $foundAlternate = false;
                if (! empty($macObject)){
                    /* can we find the latest connexion with this macid with a different ip and a hostid ? If yes, means a new ip address has been served for the host */
                    $alternateConnexions = $connexionsObj->getAllExtended(
                        ['where' => ['macid' => $macObject['id']], 'cols' => ['id', 'hostid', 'macid', 'ip', 'lastconnect', 'trust', 'reason'], 'orderBy' => ['lastconnect' => 'DESC']]);                                               
                    
                    foreach ($alternateConnexions as $altConnexion){
                        if (isset($altConnexion['hostid']['id']) && $altConnexion['hostid']['id'] > 0){
                            $connexionsObj->updateOne(['id' => $connexion['id'], 'hostid' => $altConnexion['hostid']]);
                            $foundAlternate = true;
                            break;
                        }
                    }
                    if (! $foundAlternate){
                        /* else can we find the latest trusted connexion with this ip address and a host id, with a different macid, but same mac address ? (e.g; if mac vendor has changed)*/
                        $alternateConnexions = $connexionsObj->getAllExtended(
                            ['where' => ['ip' => $ip], 'cols' => ['id', 'hostid', 'macid', 'ip', 'lastconnect', 'trust', 'reason'], 'orderBy' => ['lastconnect' => 'DESC']]);                                               
                        foreach ($alternateConnexions as $altConnexion){
                            if (isset($altConnexion['hostid']['id']) && $altConnexion['hostid']['id'] > 0){
                                $connexionsObj->updateOne(['id' => $connexion['id'], 'hostid' => $altConnexion['hostid']]);
                                $foundAlternate = true;
                                break;
                            }
                        }
                    }
                }
                if (! $foundAlternate){
                    $newHost = $this->findOrCreateHost($connexion, $host, $timeStamp);
                    if ($newHost){
                    $connexionsObj->updateOne(['id' => $connexion['id'], 'hostid' => $newHost['id']]);
                        if (isset($connexion['macid'])){
                            $macAddressesObj = $this->objectsStore->objectModel('macaddresses');
                            $macAddressesObj->updateOne(['id' => $connexion['macid'], 'parentid' => $newHost['id']]);
                        }
                    }
                }else{
                    $this->updateHost($altHostId, $host, $timeStamp);
                }   
            }
        }else{// no connexion found : there must be a bug
            Tfk::debug_mode('log', 'networks->checkHost - no connexion found - there must be a bug! ip is: ', $ip);
        }
        return [];
    }
    
    public function investigateAll($networkId, $hosts){
        $connexionsObj = $this->objectsStore->objectModel('connexions');
        $connexionsObj->discoverAll($networkId, $hosts);/* to make sure we update connexion information from this latest scan*/
        $timeStamp =  date('Y-m-d H:i:s');
        foreach ($hosts as $key => $host){
            $result = $this->investigateOne($networkId, $host, $timeStamp);
        }
        return [];
    }

}
?>
