<?php
namespace TukosLib\Objects;

class Directory{
    private static $directory =  [
        'tukos'             => 'Tukos',
        'backoffice'        => 'BackOffice',
    	'users'             => 'Admin\Users',
        'customviews'       => 'Admin\Users\CustomViews',
        'navigation'        => 'Admin\Users\Navigation',
        'contexts'          => 'Admin\Contexts',
        'objrelations'      => 'Admin\ObjRelations',
        'translations'      => 'Admin\Translations',
        'mailsmtps'         => 'Admin\Mail\Smtps',
        'mailservers'       => 'Admin\Mail\Servers',
        'mailaccounts'      => 'Admin\Mail\Accounts',
        'mailboxes'         => 'Admin\Mail\Boxes',
        'mailmessages'      => 'Admin\Mail\Messages',
        'mailtukosmessages' => 'Admin\Mail\TukosMessages',
        'scripts'           => 'Admin\Scripts',
        'scriptsoutputs'    => 'Admin\Scripts\Outputs',
        'health'            => 'Admin\Health',
        'people'            => 'Collab\People',
        'organizations'     => 'Collab\Organizations',
        'teams'             => 'Collab\Teams',
        'notes'             => 'Collab\Notes',
        'documents'         => 'Collab\Documents',
        'calendars'         => 'Collab\Calendars',
        'calendarsentries'   => 'Collab\Calendars\Entries',
        'tasks'             => 'Collab\Tasks',
        //'deals'             => 'deals',
        //'dealsstatus'       => 'deals\status',
        //'dealsteams'        => 'deals\teams',
        //'dealsrevenue'      => 'deals\revenue',
        //'projects'          => 'projects',
        //'projectsstatus'    => 'projects\status',
        //'projectsteams'     => 'projects\teams',
        'wines'             => 'Wine\Wines',
        'wineappellations'  => 'Wine\Appellations',
        'wineregions'       => 'Wine\Regions',
        'winegrowers'       => 'Wine\Growers',
    	'winecellars'       => 'Wine\Cellars',
        'wineinputs'        => 'Wine\Inputs',
        'wineoutputs'       => 'Wine\Outputs',
        'winestock'         => 'Wine\Stock',
        'winedashboards'    => 'Wine\Dashboards',
        'itsvcdescs'       => 'Itm\Itsm\SvcDescriptions',
        'itslatargets'      => 'Itm\Itsm\Slas\Targets',
        'itincidents'       => 'Itm\Itsm\Incidents',
        'itsystems'         => 'Itm\Systems',
        'networks'          => 'Itm\Networks',
        'hosts'             => 'Itm\Hosts',
        'macaddresses'      => 'Itm\MacAddresses',
        'hostsdetails'      => 'Itm\HostsDetails',
        'servicesdetails'   => 'Itm\ServicesDetails',
        'connexions'        => 'Itm\Connexions',
        'sptathletes' => 'Sports\Athletes',
        'sptprograms' => 'Sports\Programs',
    	'sptsessions'   => 'Sports\Sessions',
        'sptsessionsstages' => 'Sports\Sessions\Stages',
    	'sptexercises'		=> 'Sports\Exercises',
        'sptexerciseslevels' => 'Sports\Exercises\Levels',
        'physiopatients'    => 'Physio\Patients',
        'physioprescriptions' => 'Physio\Prescriptions',
        'physioassesments' => 'Physio\Assesments',
        'physiocdcs' => 'Physio\Cdcs',
        'physiotemplates' => 'Physio\Templates',
    	'bustrackcatalog' => 'BusTrack\Catalog',
        'bustrackpeople' => 'BusTrack\People',
        'bustrackorganizations' => 'BusTrack\Organizations',
        'bustrackquotes' => 'BusTrack\Quotes',
        'bustrackinvoicescustomers' => 'BusTrack\Invoices\Customers',
        'bustrackinvoicescustomersitems' => 'BusTrack\Invoices\Customers\Items',
        'bustrackinvoicessuppliers' => 'BusTrack\Invoices\Suppliers',
        'bustrackinvoicessuppliersitems' => 'BusTrack\Invoices\Suppliers\Items',
        'bustrackpaymentscustomers'  => 'BusTrack\Payments\Customers',
        'bustrackpaymentscustomersitems' => 'BusTrack\Payments\Customers\Items',
        'bustrackpaymentssuppliers' => 'BusTrack\Payments\Suppliers',
        'bustrackpaymentssuppliersitems' => 'BusTrack\Payments\Suppliers\Items',
        'bustrackcategories' => 'BusTrack\Categories',
        'bustrackdashboardscustomers' => 'BusTrack\Dashboards\Customers',
        'bustrackdashboardssuppliers' => 'BusTrack\Dashboards\Suppliers',
        'bustrackreconciliationscustomers' => 'BusTrack\Reconciliations\Customers',
        'bustrackreconciliationssuppliers' => 'BusTrack\Reconciliations\Suppliers',
        'help'              => 'Help',
        ];
    private static $objectsDomainALiases = ['people' => ['bustrack' => 'bustrackpeople', 'sports' => 'sptathletes', 'physio' => 'physiopatients'], 'organizations' => ['bustrack' => 'bustrackorganizations', 'wine' => 'winegrowers']];
    private static $configStatusRange = ['tukos' => 3, 'bustrack' => 2001, 'wine' => 3001, 'itm' => 4001, 'sports' => 5001, 'physio' => 6001, 'users' => 10001];
    
    public static function objectsDomainAliases(){
        return self::$objectsDomainALiases;
    }
    public static function configStatusRange(){
    	return self::$configStatusRange;
    }
    public static function getObjDir($object){
        return self::$directory[strtolower($object)];
    }
    public static function getObjDomain($object){
        return strtolower(explode('\\', self::$directory[strtolower($object)], 2)[0]);
    }
    public static function getObjs(){
        return array_keys(self::$directory);
    }
    public static function getNativeObjs(){
        return array_diff(array_keys(self::$directory), ['tukos', 'backoffice', 'mailboxes', 'mailmessages', 'translations']);
    }
    public static function getDomains(){
        $domains = [];
        foreach (self::$directory as $objDir){
            $domains[] = strtolower(explode('\\', $objDir, 2)[0]);
        }
        return array_unique($domains);
    }
}
?>
