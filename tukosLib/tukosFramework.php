<?php
namespace TukosLib;


class TukosFramework{

    /**
     * Concentrate here all depencies relative to the physical server set-up
     *
     */
    const phpTukosDir = '/tukos/'; // this is the php source top directory
    const phpVendorDir = self::phpTukosDir . 'vendor/';
    const auraDir   = self::phpVendorDir . 'auraphp-system-1.0.0/';
    const auraV2Dir = self::phpVendorDir . 'aura-2.1.0/';
    const phpPearDir = self::phpVendorDir;
    const phpZendDir = self::phpVendorDir . 'zf1/zend-console-getopt/library/';
    const phpCommand = '/xampp/php/php ';
    const phpDetachedCommand = 'start /Dx: /xampp/php/php '; // so that runs in a separate ms-dos windows (detached)
    const htmlToPdfCommand = '/wkhtmltopdf/bin/wkhtmltopdf ';
    //const tukosUsersFiles = '/jch/tukosusersfiles/';
    const tukosTmpDir = '/tukos/tmp/';
    const mailServerFolder = '/Xampp/MercuryMail/';
    const backupBinDir = '/xampp/mysql/bin/';

    const tukosSite = '/tukos/site/';

    const publicDir = '/tukos/'; // this is the beginnning of the url path, i.e. '/tukos/' is aliased with '/tukos/site/' in apache config
    
    public static $registry = null, $startMicroTime, $tr, $osName, $mode, $extras = [], $environment; 
  
    public static function initialize ($mode, $appName = null){
        self::$startMicroTime = microtime(true);
        mb_internal_encoding('UTF-8');
        require __DIR__ . '\registry.php';
        self::$registry = new Registry($mode);
        if ($mode === 'commandLine'){
            self::$registry->appName = strtolower($appName);
        }
        self::$osName = php_uname('s');
        self::$mode = $mode;
    }
    
    public static function setEnvironment($environment){
    	self::$environment = $environment;
    }
    public static function jsFullDir($dir){
    	return self::publicDir . 'tukosenv/' . (empty(self::$environment) || self::$environment === 'production' ? 'release/' : 'src/') . $dir;
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
        self::$tr = self::$registry->get('translatorsStore')->translator('Page', [self::$registry->appName, 'tukosLib'], $language);
    }
    public static function tr($theText){
        return call_user_func(self::$tr, $theText);
    }
    public static function addExtra($id, $value){
    	self::$extras[$id] =$value;
    }
    public static function getExtras(){
    	return self::$extras;
    }

}
?>
