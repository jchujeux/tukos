<?php
namespace TukosLib;

use TukosLib\TukosFramework as Tfk;

class Registry{

    function __construct($mode){
        $this->mode = $mode;
        $this->loader = require Tfk::auraDir . 'package/Aura.Autoload/scripts/instance.php';
        $this->loader->register();
        require Tfk::phpVendorDir . 'autoload.php';
        
        $this->loader->add('Aura\Di\\'    , Tfk::auraDir . 'package/Aura.Di/src/');

        $this->container = new \Aura\Di\Container(new \Aura\Di\Forge(new \Aura\Di\Config));
        
        if ($this->mode === 'interactive'){
            $this->loader->add('Aura\View\\'     , Tfk::auraDir . 'package/Aura.View/src/');
            $this->loader->add('Aura\Web\\'      , Tfk::auraDir . 'package/Aura.Web/src/');
            $this->loader->add('Aura\Session\\'  , Tfk::auraDir . 'package/Aura.Session/src/');
            $this->loader->add('Aura\Http\\'     , Tfk::auraDir . 'package/Aura.Http/src/');
            $this->loader->add('Aura\Router\\', Tfk::auraDir . 'package/Aura.Router/src/');
            $this->loader->add('Aura\Uri\\', Tfk::auraDir . 'package/Aura.Uri/src/');
            $this->setHttpServices();
        }
        $this->loader->add('Aura\Sql\\'         , Tfk::auraDir . 'package/Aura.Sql/src/');
        //$this->loader->add('Aura\Sql\\'     , Tfk::auraV2Dir . 'Sql/');
        $this->loader->add('Aura\SqlQuery', Tfk::auraV2Dir);
        $this->loader->add('Aura\Intl\\'     , Tfk::auraDir . 'package/Aura.Intl/src/');
        
        $this->loader->add('Zend\\'       , Tfk::phpZendDir);
        $this->loader->add('Pear\\'       , Tfk::phpPearDir);
        $this->loader->add('ManuelLemos\\', Tfk::phpVendorDir);
        $this->loader->add('PaulButler\\' , Tfk::phpVendorDir);
        $this->loader->add('Ifsnop\\'     , Tfk::phpVendorDir);
        $this->loader->add('PHPMailer\\'  , Tfk::phpVendorDir);
        $this->loader->add('Html2Text\\'  , Tfk::phpVendorDir);
        //$this->loader->add('Google\\'  , Tfk::phpVendorDir . 'google/apiclient/src/');
        
        $this->loader->add('TukosLib\\', Tfk::phpTukosDir);
        $this->loader->add('TukosApp\\', Tfk::phpTukosDir);
        $this->loader->add('TukosSports\\', Tfk::phpTukosDir);
        $this->loader->add('TukosBus\\', Tfk::phpTukosDir);
    }

    protected function setHttpServices(){

        $this->container->set('routeMap', new \Aura\Router\Map(new \Aura\Router\DefinitionFactory, new \Aura\Router\RouteFactory)); 
        $map = $this->get('routeMap');
        
        $this->inComingUriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        $map->add('tukosPane'       , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}/{:mode}/{:action}/{:pane}');
        $map->add('tukosPane'       , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}/{:mode}/{:action}');
        $map->add('tukosAction'     , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}/{:mode}');
        $map->add('tukosView  '     , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}/{:view}');
        $map->add('tukosObject'     , Tfk::publicDir . 'index20.php/{:application}/{:controller}/{:object}');
        $map->add('tukosController' , Tfk::publicDir . 'index20.php/{:application}/{:controller}/');
        $map->add('tukosBase'       , Tfk::publicDir . 'index20.php/{:application}/');
        
        // get the route based on the path and server
        $this->route = $map->match($this->inComingUriPath, $_SERVER);
        if ($this->route && $this->route->values['application']){
            $this->appName = strtolower($this->route->values['application']);
            if (isset($this->route->values['controller'])){
                $this->controller = $this->route->values['controller'];
            }else{
            	$this->controller = "page";
            }
            if (isset($this->route->values['object'])){
               $this->objectName = $this->route->values['object'];
            }else{
            	$this->objectName = "help";
            }
            $this->isMobile = strtolower(substr($this->controller, 0, 6)) === 'mobile' ? true : false;

            $this->pageUrl          = $map->generate('tukosController', ['application' => $this->appName, 'controller' => ($this->isMobile ? 'mobile' : '') . 'page']);
            $this->dialogueUrl      = $map->generate('tukosController', ['application' => $this->appName, 'controller' => ($this->isMobile ? 'mobile' : '') . 'dialogue']);
        }
        $this->container->set('urlFactory', new \Aura\Uri\Url\Factory($_SERVER));
        $urlFactory = $this->get('urlFactory');
        $url = $urlFactory->newCurrent();
        $this->urlQuery = $url->query->getArrayCopy();
    }        

    public function set($service, $serviceObject){
        return $this->container->set($service, $serviceObject);
    }
    public function get($service){
        return $this->container->get($service);
    }
}
?>
