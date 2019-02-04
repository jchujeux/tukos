<?php
/**
 *
 * class for the Networks tukos object 
 */
namespace TukosLib\Objects\Itm\Networks;

use TukosLib\Objects\AbstractModel;
use TukosLib\TukosFramework as Tfk; 
use Pear\Net\Nmap\Parser as NmapParser;

class Model extends AbstractModel {
    function __construct($objectName, $translator=null){
        $colsDefinition =  ['iprange'       =>  'VARCHAR(255)  DEFAULT NULL',
                            'signaturecmd'  =>  'VARCHAR(255)  DEFAULT NULL',//the nmap command the output of which should contain the network signature
                            'signature'     =>  'VARCHAR(255)  DEFAULT NULL',//a regular search expression identifying the signature, e.g. the Mac address of a known host
                            ];
        parent::__construct($objectName, $translator, 'networks', ['parentid' => ['organizations']], [], $colsDefinition);
        $this->streamsStore = Tfk::$registry->get('streamsStore');
    }

   /*
    * executes the $signatureCmd, and returns connexion status by searching for the $signature string in the stream output. $id is the stream unique identifier
    * Example of use:  signature is the presence of a host with <Mac address> at address <IP address> (e.g. a network equipment, or box, or server)
    *   $id             = network object id
    *   $signatureCmd   = 'nmap -sn <IP address>'
    *   $signature      = '<mac address>'
    */
    function isConnected($id, $signatureCmd, $signature){
        if ($this->streamsStore->startStream($id, $signatureCmd, false)){
            $output = $this->streamsStore->waitOnStream($id, false, 'return')['output'];
            if(strpos($output, $signature)){
                return 'connected';
            }else{
                return 'failedsignature';
            }
        }else{
            return 'streamIdAlreadyRunning';
        }      
    }
    
    function scan($scriptId, $cmd, $target){
        if ($this->streamsStore->startStream($scriptId, $cmd . ' ' . $target, false)){
            $result = $this->streamsStore->waitOnStream($scriptId, true, 'return');
            $parse = new NmapParser();
            echo $result['output'];
            $parse->setInput($result['output']);
            $parse->folding = false;
            
            $parseResult = $parse->parse();
            if ($parseResult){
                $hosts = $parse->getHosts();
                return $hosts;
            }else{
                return $parseResult;
            }      
        }else{
            Tfk::debug_mode('log', 'networks->scan - a stream is already opened for this id: ', $id);
            return 'StreamIdAlreadyRunning';
        }      
    }


   /*
    * Discover creates or updates connexions and macaddresses objects bases on scan results of an ip range, whereas investigate creates or updates hosts, hostsdetails
    * ans macaddresses objects based on scan results for a set of ip addresses expected to have a host connected to
    *
    * options = ['networkid' => $id, 'discover' => $commandString (the nmap command for discover phase), {'dtarget' => $targetString (nmap target for discover)}]
    * returns [$ip => 'change description'] where changes have been detected with or string containin return status if something unexpected occured
    */
    function discover($options){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $network = $this->getOne(['where' => ['id' => $options['networkid']], 'cols' => ['id', 'name', 'iprange', 'signaturecmd', 'signature']]);
       /*
        * 1. Make sure we are connected to this network
        */
        $status = $this->isConnected($network['id'], $network['signaturecmd'], $network['signature']);
        switch ($status){
            case 'connected':
                /*
                 * 2. Look for hosts currently connected to this network
                 */
                $hosts = $this->scan($options['scriptid'], $options['discover'], (isset($options['dtarget']) ? $options['dtarget'] : $network['iprange']));
                if ($hosts instanceof \ArrayIterator){
                    /*
                     * 3. Is this a known host and connexion ?
                     */
                    $connexionObj = $objectsStore->objectModel('connexions');
                    return $connexionObj->discoverAll($network['id'], $hosts);
                }else{
                    return $hosts;
                }
                break;
            default:
                return $status;
                break;
        }
    }
    
   /*
    * options = ['networkid' => $id, 'investigate' => $commandString (the nmap command for investigate phase), {'itarget' => $targetString (nmap target for investigate)}]
    * $ipsToInvestigate: an array of ip addresses to investigate (will be merged with 
    *   - $options['target']
    *   - connexions with values in $options['trust']  if set
    *   - connexions with hostid empty if $options['nohostid'] is set
    * returns string containing return status if something unexpected occured
    */
    function investigate($options, $ipsToInvestigate){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $network = $this->getOne(['where' => ['id' => $options['networkid']], 'cols' => ['id', 'name', 'iprange', 'signaturecmd', 'signature']]);
        /*
         * 1. Make sure we are connected to this network
         */
        $status = $this->isConnected($network['id'], $network['signaturecmd'], $network['signature']);

        switch ($status){
            case 'connected':
                /*
                 * 2. investigate requested ips
                 */
                if (isset($options['itarget'])){
                    $ipsToInvestigate = array_merge($ipsToInvestigate, explode(' ', $options['itarget']));
                }
                $where = [];
                if (isset($options['trust'])){
                    $where[] = ['col' => 'trust', 'opr' => 'IN', 'values' => explode(' ', $options['trust'])];
                }
                if (isset($options['nohostid'])){
                    $where[] = ['col' => 'hostid', 'opr' => 'IS NULL', 'values' => null, 'or' => true];
                    $where[] = ['col' => 'hostid', 'opr' => 'LIKE', 'values' => 0, 'or' => true];
                }
                if (! empty($where)){
                    $connexionsObj = $objectsStore->objectModel('connexions');
                    $connexionIds = $connexionsObj->getAll(['where' => $where, 'cols' => ['ip']]);
                    $ipsToAdd = [];
                    foreach ($connexionIds as $connexion){
                        $ipsToAdd[] = $connexion['ip'];
                    }
                    $ipsToInvestigate = array_merge($ipsToInvestigate, $ipsToAdd);
                }
                $hosts = $this->scan($options['scriptid'], $options['investigate'], implode(' ', $ipsToInvestigate));
                if ($hosts instanceof \ArrayIterator){
                    $hostsObj      = $objectsStore->objectModel('hosts');
                    return $hostsObj->investigateAll(['id' => $network['id'], 'name' => $network['name'], 'object' => 'networks'], $hosts);

                }else{
                    return $hosts;
                }
                break;
            default:
                return $status;
                break;
        }
    }
}
?>
