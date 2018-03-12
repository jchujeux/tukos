<?php
/**
 *
 * class for the HostsDetails tukos object, an "image" of the nmap hosts entity
 */
namespace TukosLib\Objects\ITM\HostsDetails;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk; 

class Model extends AbstractModel {
    protected $statusOptions = ['up'/* */, 'down'/* */, 'unknown'/* */, 'skipped'/* */,];
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['status'        => "ENUM ('" . implode("','", $this->statusOptions) . "')",
                            'hostname'      => 'VARCHAR(80)  DEFAULT NULL',/* from nmap parser with default index*/
                            'type'          => 'VARCHAR(80)  DEFAULT NULL',/* from nmap parser with default index*/
                            'vendor'        => 'VARCHAR(80)  DEFAULT NULL',/* from nmap parser with default index*/
                            'osfamily'      => 'VARCHAR(80)  DEFAULT NULL',/* from nmap parser with default index*/
                            'osgen'         => 'VARCHAR(80)  DEFAULT NULL',/* from nmap parser with default index*/
                            'accuracy'      => 'INT(11)',
                            'upsince'       => "timestamp NULL DEFAULT NULL",
                            'timescanned'   => "timestamp NULL DEFAULT NULL",
                            ];
        parent::__construct($objectName, $translator, 'hostsdetails', ['parentid' => ['hosts']], [], $colsDefinition, '', ['status']);
    }

    public function getOSClass($host){
        $osClasses      = $host->getAllOSClass();
        $classesCount   = count($osClasses);
        switch ($classesCount){
            case 0  : return [];
            case 1  : return $osClasses[0];
            default : 
                $accuracy  = 0;
                foreach ($osClasses as $key => $class){
                    if ($class['accuracy'] > $accuracy){
                        $accuracy = $class['accuracy'];
                        $bestMatch = $key;
                        $ambiguous = false;
                    }else if($class['accuracy'] === $accuracy){
                        if ($class !== $osClasses[$bestMatch]){
                            $ambiguous = true;
                        }
                    }
                }
                return ($ambiguous ? $osClasses[$bestMatch] + ['comments' => 'At least two OS classes had same accuracy: OS information is potentially erroneous or ambiguous'] : $osClasses[$bestMatch]);
        }
    }
    public function upSince($host){
        if (!empty($host->uptime)){
            $upSince = new \DateTime($host->uptime['lastboot']);
            return $upSince->format('Y-m-d H:i:s');
        }else{
            return null;
        }
    }
    public function getHostDetails($host){
        return array_merge(['status' => $host->getStatus(), 'hostname' => $host->getHostname(), 'upsince' => $this->upSince($host)], $this->getOSClass($host));
    }
    
    public function updateOrNew($previousHost, $newHost){
        if ($previousHost){
            $colsToCompare = ['status', 'hostname', 'type', 'vendor', 'osfamily', 'osgen', 'accuracy'];
            foreach ($colsToCompare as $col){
                if (!empty($previousHost[$col]) && !empty($newHost[$col]) && $previousHost[$col] !== $newHost[$col]){
                    return 'new';
                }
            }
            return 'update';
        }else{
            return 'new';
        }
    }
        
   /*
    * create hostsdetails and servicesdetails information from scan result contained in host, run at $timeStamp 
    */
    public function create($hostId, $host, $timeStamp){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $newHostDetails = $this->insertExtended(array_merge($this->getHostDetails($host), ['parentid' => $hostId, 'timescanned' => $timeStamp]), true);
        $servicesObj =  $objectsStore->objectModel('servicesdetails');
        $services = $host->getServices();
        foreach ($services as $key => $service) {
            $servicesObj->insertExtended(['parentid' => $hostId, 'name' => $service->name, 'port' => $service->port, 'protocol' => $service->protocol,
                                          'product'  => $service->product,  'version' => $service->version], true);
        }
    }
   /*
    * update hostsdetails information from scan result contained in host, run at $timeStamp 
    */
    public function revise($hostId, $host, $timeStamp){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $storedDetails  = $this->getOne(['where' => ['parentid' => $hostId], 'cols' => ['id', 'status', 'hostname', 'upsince', 'type', 'vendor', 'osfamily', 'osgen', 'comments'], 'orderBy' => ['updated' => 'DESC']]);
        $scannedDetails = $this->getHostDetails($host);
        switch ($this->updateOrNew($storedDetails, $scannedDetails)){
            case 'update':
                $this->updateOne(array_merge($scannedDetails, ['timescanned' => $timeStamp]), ['where' => ['id' => $storedDetails['id']]]);
                break;
            case 'new':
                $newHostDetails = $this->insertExtended(array_merge($scannedDetails, ['parentid' => $hostId, 'timescanned' => $timeStamp]), true);
                break;
        } 
        $servicesObj =  $objectsStore->objectModel('servicesdetails');
        $services = $host->getServices();
        foreach ($services as $key => $service) {
            $storedService = $servicesObj->getOne(['where' => ['parentid' => $hostId, 'name' => $service->name, 'port' => $service->port], 'cols' => ['id']]);
            if (!empty($storedService)){
                $servicesObj->updateOne(['protocol' => $service->protocol, 'product'  => $service->product,  'version' => $service->version, 'timescanned' => $timeStamp],
                                     ['where' => ['parentid' => $hostId, 'name' => $service->name, 'port' => $service->port]]);
            }else{
                $servicesObj->insertExtended(['parentid' => $hostId, 'name' => $service->name, 'port' => $service->port, 'protocol' => $service->protocol,
                                              'product'  => $service->product,  'version' => $service->version, 'timescanned' => $timeStamp], true);
            }
        }
    }

}
?>
