<?php
namespace TukosLib\Objects;

class Directory{
    private static $directory =  [
        'users'             => 'admin\users',
        'customviews'       => 'admin\users\customviews',
        'navigation'        => 'admin\users\navigation',
        'contexts'          => 'admin\contexts',
        'objrelations'      => 'admin\objrelations',
        'translations'      => 'admin\translations',
        'mailsmtps'         => 'admin\mail\smtps',
        'mailservers'       => 'admin\mail\servers',
        'mailaccounts'      => 'admin\mail\accounts',
        'mailboxes'         => 'admin\mail\boxes',
        'mailmessages'      => 'admin\mail\messages',
        'mailtukosmessages' => 'admin\mail\tukosmessages',
        'scripts'           => 'admin\scripts',
        'scriptsoutputs'    => 'admin\scripts\outputs',
        'health'            => 'admin\health',
        'healthtables'      => 'admin\health\tables',
        'people'            => 'collab\people',
        'organizations'     => 'collab\organizations',
        'teams'             => 'collab\teams',
        'notes'             => 'collab\notes',
        'documents'         => 'collab\documents',
        'calendars'      => 'collab\calendars',
        'calendarsentries'   => 'collab\calendars\entries',
        'tasks'             => 'collab\tasks',
        //'deals'             => 'deals',
        //'dealsstatus'       => 'deals\status',
        //'dealsteams'        => 'deals\teams',
        //'dealsrevenue'      => 'deals\revenue',
        //'projects'          => 'projects',
        //'projectsstatus'    => 'projects\status',
        //'projectsteams'     => 'projects\teams',
        'wines'             => 'wine\wines',
        'wineappellations'  => 'wine\appellations',
        'wineregions'       => 'wine\regions',
        'winegrowers'       => 'wine\growers',
    	'winecellars'       => 'wine\cellars',
        'wineinputs'        => 'wine\inputs',
        'wineoutputs'       => 'wine\outputs',
        'winestock'         => 'wine\stock',
        'winedashboards'    => 'wine\dashboards',
        'itsvcdescs'       => 'itm\itsm\svcdescriptions',
        'itslatargets'      => 'itm\itsm\slas\targets',
        'itincidents'       => 'itm\itsm\incidents',
        'itsystems'         => 'itm\systems',
        'networks'          => 'itm\networks',
        'hosts'             => 'itm\hosts',
        'macaddresses'      => 'itm\macaddresses',
        'hostsdetails'      => 'itm\hostsdetails',
        'servicesdetails'   => 'itm\servicesdetails',
        'connexions'        => 'itm\connexions',
        'sptathletes' => 'sports\athletes',
        'sptprograms' => 'sports\programs',
    	'sptsessions'   => 'sports\sessions',
        'sptsessionsstages' => 'sports\sessions\stages',
    	'sptexercises'		=> 'sports\exercises',
        'physiopatients'    => 'physio\patients',
        'physioprescriptions' => 'physio\prescriptions',
        'physioassesments' => 'physio\assesments',
        'physiocdcs' => 'physio\cdcs',
        'physiotemplates' => 'physio\templates',
    	'bustrackcatalog' => 'bustrack\catalog',
        'bustrackcustomers' => 'bustrack\customers',
        'bustrackquotes' => 'bustrack\quotes',
        'bustrackinvoices' => 'bustrack\invoices',
        'help'              => 'help',
        ];
    private static $configStatusRange = ['tukos' => 2, 'bustrack' => 2001, 'wine' => 3001, 'itm' => 4001, 'sports' => 5001, 'physio' => 6001, 'users' => 10001];
    
    public static function configStatusRange(){
    	return self::$configStatusRange;
    }
    
    public static function getObjDir($object){
        return self::$directory[$object];
    }
    public static function getObjDomain($object){
        return explode('\\', self::$directory[$object], 2)[0];
    }
    public static function getObjs(){
        return array_keys(self::$directory);
    }
    public static function getNativeObjs(){
        return array_diff(array_keys(self::$directory), ['mailboxes', 'mailmessages']);
    }
    public static function getDomains(){
        $domains = [];
        foreach (self::$directory as $objDir){
            $domains[] = explode('\\', $objDir, 2)[0];
        }
        return array_unique($domains);
    }
}
?>
