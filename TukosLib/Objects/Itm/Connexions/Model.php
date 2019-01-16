<?php
/**
 *
 * class for the Connexions tukos object 
 */
namespace TukosLib\Objects\Itm\Connexions;

use TukosLib\Objects\Itm\Itm;
use TukosLib\Objects\Itm\AbstractModel;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['ip'            => 'VARCHAR(80)  DEFAULT NULL',
                            'macid'         => 'INT(11) NOT NULL',
                            'hostid'        => 'INT(11) NOT NULL',
                            'firstconnect'  =>  "timestamp NULL DEFAULT NULL",
                            'lastconnect'   =>  "timestamp NULL DEFAULT NULL",
                            'trust'         =>  "ENUM ('" . implode("','", Itm::$trustOptions) . "')",
                            'reason'        => 'VARCHAR(80)  DEFAULT NULL',
                            ];
        parent::__construct($objectName, $translator, 'connexions', ['parentid' => ['networks'], 'hostid' => ['hosts'], 'macid' => ['macaddresses']], [], $colsDefinition, '', ['trust']);
    }
    private function discoverOne($networkId, $ip, $mac, $vendor, $timeStamp){
        $objectsStore = Tfk::$registry->get('objectsStore');
        if ($mac){
            $macAddressesObj = $objectsStore->objectModel('macaddresses');
            $macObject = $macAddressesObj->getOrCreate($mac, $vendor);
            $macId = $macObject['id'];
            
            $lastConnexion = $this->getOne([
                'where' => ['parentid' => $networkId, 'macid' => $macObject['id']], 
                'cols'  => ['id', 'ip', 'macid', 'hostid', 'lastconnect'], 'orderBy' => ['lastconnect' => 'DESC'], 'limit' => 1]
            );
            if (!empty($lastConnexion)){
                if ($ip === $lastConnexion['ip']){// new connexion is same as last one for that macid so assume same host and same trust level. 
                    $newConnexion = ['id' => $lastConnexion['id'], 'lastconnect' => $timeStamp, 'trust' => $macObject['trust'], 'reason' => $macObject['reason']];
                    $this->updateOne($newConnexion);
                    return 'NOCHANGE';
                }else{// ip has changed, create a new connexion
                    $newConnexion = [
                        'parentid' => $networkId, 'macid' => $macId,  'ip' => $ip, 'hostid' => $macObject['parentid'], 'firstconnect' => $timeStamp,
                        'lastconnect' => $timeStamp, 'trust' => $macObject['trust'], 'reason' => $macObject['reason']
                    ];
                    $this->insertExtended($newConnexion, true);
                    return 'CHANGEDIP';
                }
            }else{// no connexion was found to this network via this mac address. Create a new connexion
                $newConnexion = ['parentid' => $networkId, 'macid' => $macId,  'ip' => $ip, 'firstconnect' => $timeStamp, 'lastconnect' => $timeStamp,
                                 'trust' => $macObject['trust'], 'reason' => $macObject['reason']];
                $this->insertExtended($newConnexion, true);
                return 'NEWCONNEXIONWITHTHISMACADDRESS';
            }
        }else{/* scan did not return a mac address. We cannot give a value to 'macid'. Has to be done elsewhere (e.g. manually by an autorized user)*/
            $lastConnexion = $this->getOne(
                ['where' => ['parentid' => $networkId, 'ip' => $ip], 'cols' => ['id', 'ip', 'macid', 'hostid', 'lastconnect', 'trust', 'reason'],
                 'orderBy' => ['lastconnect' => 'DESC'], 'limit' => 1]);
            if (!empty($lastConnexion) && empty($lastConnexion['macid'])){
                $this->updateOne(['id' => $lastConnexion['id'], 'lastconnect' => $timeStamp]);
                return 'NOCHANGE';
            }else{                    
                $newConnexion = ['parentid' => $networkId, 'ip' => $ip, 'firstconnect' => $timeStamp, 'lastconnect' => $timeStamp, 'trust' => 'UNKNOWN', 'reason' => 'noMac'];
                $this->insertExtended($newConnexion, true);
                return 'LOSTMACADDRESS';
            }
        }
    }
    
    public function discoverAll($networkId, $hosts){
        $timeStamp =  date('Y-m-d H:i:s');
        $status = [];
        foreach ($hosts as $key => $host){
            $ip  = $host->getAddress('ipv4');
            $mac = $host->getAddress('mac');
            $vendor = $host->getAddress('macvendor');
            $status[$ip] = $this->discoverOne($networkId, $ip, $mac, $vendor, $timeStamp);
        }
        return $status;
    }

}
?>
