<?php
namespace TukosLib;

use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\DiContainer;
use Detection\MobileDetect;

class Registry{

    function __construct($mode, $appName){
        $this->mode = $mode;
        require Tfk::$phpVendorDir . 'autoload.php';// needed for google
        $auraDir = Tfk::$vendorDir['aura'];
        $this->loader = require $auraDir . 'package/Aura.Autoload/scripts/instance.php';
        $this->loader->register();
        
        $this->loader->add('TukosLib\\', Tfk::$tukosPhpDir);
        $this->loader->add('TukosApp\\', Tfk::$tukosPhpDir);
        $this->loader->add('TukosSports\\', Tfk::$tukosPhpDir);
        $this->loader->add('TukosBus\\', Tfk::$tukosPhpDir);
        
        $this->loader->add('Aura\Di\\'    , $auraDir . 'package/Aura.Di/src/');

        //$this->container = new \Aura\Di\Container(new \Aura\Di\Forge(new \Aura\Di\Config));
        $this->container = new DiContainer(new \Aura\Di\Forge(new \Aura\Di\Config));
        
        if ($this->mode === 'interactive'){
            Tfk::$dojoBaseLocation = getenv('dojoBaseLocation');
            Tfk::$tukosBaseLocation = getenv('tukosBaseLocation');
            Tfk::$tukosFormsDojoBaseLocation = Tfk::$dojoCdnBaseLocation = getenv('dojoCdnBaseLocation');
            Tfk::$tukosFormsDomainName = Tfk::$tukosDomainName = getenv('tukosDomainName');
            Tfk::$tukosFormsTukosBaseLocation = 'https://' . Tfk::$tukosDomainName . '/tukos/tukosenv/release/';
            $this->loader->add('Aura\View\\'     , $auraDir . 'package/Aura.View/src/');
            $this->loader->add('Aura\Web\\'      , $auraDir . 'package/Aura.Web/src/');
            $this->loader->add('Aura\Session\\'  , $auraDir . 'package/Aura.Session/src/');
            $this->loader->add('Aura\Http\\'     , $auraDir . 'package/Aura.Http/src/');
            $this->loader->add('Aura\Router\\', $auraDir . 'package/Aura.Router/src/');
            $this->loader->add('Detection\\', Tfk::$vendorDir['MobileDetect']);
            $this->setHttpServices();
        }else{
            $this->appName = $this->setAppName($appName);
        }
        $this->loader->add('Aura\Sql\\'         , $auraDir . 'package/Aura.Sql/src/');
        $this->loader->add('Aura\SqlQuery', Tfk::$vendorDir['auraV2']);
        
        $this->loader->add('Zend\\'       , Tfk::$vendorDir['zend']);
        $this->loader->add('Pear\\'       , Tfk::$vendorDir['pear']);
        $this->loader->add('ManuelLemos\\', Tfk::$phpVendorDir);
        $this->loader->add('Ifsnop\\'     , Tfk::$phpVendorDir);
        $this->loader->add('PHPMailer\\'  , Tfk::$phpVendorDir);
        $this->loader->add('Html2Text\\'  , Tfk::$phpVendorDir);        
        $this->loader->add('Dropbox\\', Tfk::$vendorDir['Dropbox']);
    }

    protected function setHttpServices(){

        $this->container->set('routeMap', new \Aura\Router\Map(new \Aura\Router\DefinitionFactory, new \Aura\Router\RouteFactory)); 
        $map = $this->get('routeMap');
        
        $this->rootUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $this->inComingUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $map->add('tukosPane'       , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}/{:mode}/{:action}/{:pane}');
        $map->add('tukosAction'       , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}/{:mode}/{:action}');
        $map->add('tukosMode'     , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}/{:mode}');
        $map->add('tukosView  '     , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}');
        $map->add('tukosObject'     , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}');
        $map->add('tukosController' , Tfk::publicDir . 'index20.php/{:application}/{:controller}/');
        $map->add('tukosBase'       , Tfk::publicDir . 'index20.php/{:application}/');
        $map->add('tukosDefault'       , Tfk::publicDir . 'index20.php/{:application}');
        
        $this->route = $map->match($this->inComingUriPath, $_SERVER);
        if ($this->route && $this->route->values['application']){
            $this->request = array_merge(['controller' => 'Page', 'object' => 'Help', 'view' => 'Overview'], $this->route->values);
            foreach($this->request as &$value){
                $value = ucfirst($value);
            }
            $this->appName = $this->setAppName($this->request['application']);
            $this->controller = $this->request['controller'];
            if ($this->isMobile = (new MobileDetect)->isMobile());
            $this->pageUrl          = $map->generate('tukosController', ['application' => $this->appName, 'controller' => ($this->isMobile ? 'Mobile' : '') . 'Page']);
            $this->dialogueUrl      = $map->generate('tukosController', ['application' => $this->appName, 'controller' => 'Dialogue']);
            $this->appUrl          = $map->generate('tukosBase', ['application' => $this->appName]);
        }
        $this->urlQuery = $_GET;
    }        
    public function setAppName($appName){
        return ['tukosapp' => 'TukosApp', 'tukossports' => 'TukosSports', 'tukosbus' => 'TukosBus'][strtolower($appName)];
    }
    public function set($service, $serviceObject){
        return $this->container->set($service, $serviceObject);
    }
    public function get($service){
        return $this->container->get($service);
    }
    public function isInstantiated($service){
        return $this->container->isInstantiated($service);
    }
}
?>
