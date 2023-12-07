<?php
namespace TukosLib;


class TukosFramework{

    /**
     * Concentrate here all depencies relative to the physical server set-up
     *
     */
    const mailServerFolder = '/Xampp/MercuryMail/';

    const dojoModules = ['dojo', 'dijit', 'dojox'];

    const tukosSchedulerUserId = 12, tukosUserId = 13, tukosBackOfficeUserId = 15, tukosBackOfficeMailAccountId = 18;
    
    public static $publicDir, $tukosPhpDir, $phpVendorDir, $vendorDir = [], $tukosTmpDir, $tukosPhpImages,
                  $registry = null, $startMicroTime, $tr, $osName, $mode, $extras = [], $environment, $tukosBaseLocation, $dojoBaseLocation, $tukosFormsDojoBaseLocation, $dojoCdnBaseLocation, $tukosFormsTukosBaseLocation, 
                  $tukosDomainName, $tukosFormsDomainName, $htmlToPdfCommand; 
  
    public static function initialize ($mode, $appName = null, $phpDir = null){
        self::$publicDir = getenv('tukosPublicDir');// this is the beginnning of the url path, i.e. '/tukos/' is aliased with '/tukos/site/' in apache config
        self::$tukosPhpDir = $phpDir;
        self::$tukosTmpDir = $phpDir . '/tmp/';
        self::$tukosPhpImages = $phpDir . 'site/images/';
        self::$phpVendorDir = self::$tukosPhpDir . 'vendor/';
        $vendorDirs = ['aura' => 'auraphp-system-1.0.0/', 'auraV2' => 'Aura-2.1.0', 'pear' => '', 'zend' => 'zf1/zend-console-getopt/library/', 'MobileDetect' => 'mobiledetect/mobiledetectlib/namespaced/',
                       'Dropbox' => 'lukebaird/dropbox-v2-php-sdk/'];
        array_walk($vendorDirs, function($vendorDir, $module){
            self::$vendorDir[$module] = self::$phpVendorDir . $vendorDir;
        });
        mb_internal_encoding('UTF-8');
        mb_detect_order(['UTF-8', 'windows-1252', 'ISO-8859-1']);
        require __DIR__ . '/Registry.php';
        self::$registry = new Registry($mode, $appName);
        self::$osName = php_uname('s');
        self::$mode = $mode;
        self::$htmlToPdfCommand = getenv('wkHtmlToPdfCommand');
    }
    
    public static function setEnvironment($environment){
    	self::$environment = empty($environment) ? 'production' : $environment;
    	if (self::$environment === 'development'){
    	    self::$tukosBaseLocation = self::$publicDir . "tukosenv/src/";
            self::$tukosFormsTukosBaseLocation = self::$tukosBaseLocation;
            self::$tukosFormsDojoBaseLocation = self::$tukosBaseLocation;
            self::$tukosFormsDomainName = 'localhost';
    	}else{
   	        self::$tukosBaseLocation = self::$publicDir . 'tukosenv/release/';
    	}
    	if (empty(self::$dojoBaseLocation)){
    	    self::$dojoBaseLocation = self::$tukosBaseLocation;
    	}
    }
    
    public static function moduleLocation($module){
        return (in_array($module, self::dojoModules) ? self::$dojoBaseLocation : self::$tukosBaseLocation) . $module;
    }
    
    public static function dojoBaseLocation(){
        return empty(self::$dojoBaseLocation) ? self::$tukosBaseLocation : self::$dojoBaseLocation;
    }
       
    public static function isWindows(){
    	return self::$osName[0] === 'W';
    }
    
    public static function osName(){
    	return self::$osName;
    }
    
    public static function isCommandLine(){
    	return self::$mode === 'commandLine';
    }
    
    public static function isInteractive(){
    	return self::$mode === 'interactive';
    }

    public static function debug_mode ($flag, $text, $obj = null){
        switch ($flag){
            case 'off':
                break;
             default:
                echo '<br> ' . $text . ' '; ($obj === null ? null : var_dump($obj));
        }
    }

    public static function log_message ($flag, $text, $obj = null){
        switch ($flag){
            case 'off':
                break;
             default:
                echo '<br> ' . $text . ' '; ($obj === null ? null : var_dump($obj));
        }
    }
    
    public static function error_message ($flag, $text, $obj = null){
        self::debug_mode($flag, '<b>Error: </b>' . $text, $obj);
    }
    public static function setTranslator($language = null){
        self::$tr = self::$registry->get('translatorsStore')->translator('Page', [self::$registry->appName, 'tukosLib', 'common'], $language);
    }
    public static function tr($theText, $mode=null){
        return call_user_func(self::$tr, $theText, $mode);
    }
    public static function addExtra($id, $value){
    	self::$extras[$id] =$value;
    }
    public static function getExtras(){
    	return self::$extras;
    }

}
?>