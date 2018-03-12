<?php
/**
 * tukos network scan script.
 * This scripts expects the following options from the command line script arguments:
 *   -networkid: -> the tukos id of the network to scan
 *   -cmd:       -> the nmap command, excluding the target
 *   -target::   -> (optional) - Alternative target to the network's IP range
 */

namespace TukosLib\Objects\ITM\Scripts;

use TukosLib\TukosFramework as Tfk;

class NetworkScan {

    function __construct($parameters){ 
        global $argv;
        $objectsStore = Tfk::$registry->get('objectsStore');

        $networks   = $objectsStore->objectModel('networks');

        $args = $argv;
        try{
            $options = new \Zend_Console_Getopt(
                ['app-s'		=> 'tukos application name (not needed in interactive mode)',
                 'class=s'      => 'this class name',
                 'scriptid=i'   => 'the script id for the network to scan',
                 'networkid=i'  => 'the tukos id for the network to scan',
                 'discover-s'   => 'the command to discover connexions and associated hosts usually "nmap -sn -oX -" (optional)',
                 'dtarget-s'    => 'the ip range / addresses to discover (optional, uses network ip range if not present)',
                 'investigate-s'=> 'the command to investigate found hosts via the discovered command and the itargat parameter, usually: "nmap -F -A -T4 -oX -"',
                 'itarget-s'    => 'the ip range / addresses to investigate  (optional)',
                 'trust-s'      => 'the trust values for hosts to investigate (optional)',
                 'nohostid'     => 'if set, add ip addresses for connexions with no host id set to investigate (optional)',
                ]);

            foreach (['networkid', 'discover'] as $key){
                $discoverArgs[$key] = $options->$key;
            }
            
            $ipsToInvestigate = [];
            if(isset($discoverArgs['discover']) && $discoverArgs['discover'] !== ''){
                $discoverArgs['scriptid'] = $options->scriptid;
                $status = $networks->discover($discoverArgs);
                if (is_array($status)){
                	
                    $ipsToInvestigate = array_keys($status);
                    Tfk::debug_mode('log', 'discover completed successfullt - discovered: ', $status);
                    echo 'discover completed successfully - discovered: ' . print_r($status, true);
                }else{
                    Tfk::debug_mode('error', 'discover phase failed with the following exit status: ', $status);
                }
            }else{
                echo 'No discover arguments provided in the script parameters';
            }
            
            
            foreach (['networkid', 'investigate'] as $key){
                $investigateArgs[$key] = $options->$key;
            }
            if (isset($investigateArgs['investigate']) && $investigateArgs['investigate'] !== ''){
                $investigateArgs['scriptid'] = $options->scriptid;
                $status = $networks->investigate($investigateArgs, $ipsToInvestigate);        
            }
                
            Tfk::debug_mode('log', 'investigation completed - status: ', $status);
        }catch(Getopt_exception $e){
            Tfk::debug_mode('log', 'an exception occured while parsing command aguments in networkscan: ', $e->getUsageMessage());
        }            
    }
}
?>
