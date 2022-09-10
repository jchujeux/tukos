<?php
namespace TukosLib;

use TukosLib\TukosFramework as Tfk;
use TukosLib\Utils\Utilities as Utl;
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
        
        $this->loader->add('Aura\Di\\'    , $auraDir . 'package/Aura.Di/src/');
        
        $this->container = new DiContainer(new \Aura\Di\Forge(new \Aura\Di\Config));
        
        if ($this->mode === 'interactive'){
            Tfk::$dojoBaseLocation = getenv('dojoBaseLocation');
            Tfk::$tukosBaseLocation = getenv('tukosBaseLocation');
            Tfk::$tukosFormsDojoBaseLocation = Tfk::$dojoCdnBaseLocation = getenv('dojoCdnBaseLocation');
            Tfk::$tukosFormsDomainName = Tfk::$tukosDomainName = getenv('tukosDomainName');
            Tfk::$tukosFormsTukosBaseLocation = 'https://' . Tfk::$tukosDomainName . '/tukos/tukosenv/release/';
            $this->loader->add('Aura\Web\\'      , $auraDir . 'package/Aura.Web/src/');
            $this->loader->add('Aura\Http\\'     , $auraDir . 'package/Aura.Http/src/');
            $this->setHttpServices();
            $this->loader->add($this->appName . '\\', Tfk::$tukosPhpDir);
        }else{
            $this->loader->add('Ifsnop\\'     , Tfk::$phpVendorDir);
            $this->appName = $this->setAppName($appName);
        }
        
        $this->loader->add('Aura\SqlQuery', Tfk::$vendorDir['auraV2']);
        
        $this->loader->add('Pear\\'       , Tfk::$vendorDir['pear']);
        $this->loader->add('ManuelLemos\\', Tfk::$phpVendorDir);
        $this->loader->add('Html2Text\\'  , Tfk::$phpVendorDir);        
        $this->loader->add('Dropbox\\', Tfk::$vendorDir['Dropbox']);
    }

    protected function setHttpServices(){
        $this->rootUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        if (empty($_SERVER['PATH_INFO'])){
            $this->route = false;
        }else if (!empty($route = explode('/', substr($_SERVER['PATH_INFO'], 1)))){
            $routeSteps = ['application', 'controller', 'object', 'view', 'mode', 'action', 'pane'];
            foreach($route as $key => $value){
                $this->route[$routeSteps[$key]] = $value;
            }
            $this->isMobile = (new MobileDetect)->isMobile();
            $this->isCrawler = isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT']);
            $this->request = array_merge(['controller' => 'Page', 'object' => 'Help', 'view' => 'Overview', 'mode' => 'Tab'], $this->route);
            if ($this->isMobile){
                switch (strtolower($this->request['controller'])){
                    case 'page': $this->request['controller'] = 'MobilePage'; break;
                    case 'blog': $this->request['controller'] = 'MobileBlog'; break;
                }
                if (strtolower($this->request['mode']) === 'tab'){
                    $this->request['mode'] = 'Mobile';
                }
            }
            foreach($this->request as &$value){
                $value = ucfirst($value);
            }
            unset($value);
            $this->request['object'] = strtolower($this->request['object']);
            $this->appName = $this->setAppName($this->request['application']);
            $this->controller = $this->request['controller'];
            $this->appUrl          = Tfk::$publicDir . "index20.php/{$this->appName}/";
            $this->pageUrl         = "{$this->appUrl}Page/";
            $this->dialogueUrl   = "{$this->appUrl}Dialogue/";
        }
        $this->urlQuery = [];
        foreach($_GET as $key => $value){// due to Strava authentication redirect changing '&' into '&amp;' (and in cases of a new authorization does it even twice!!
            $this->urlQuery[preg_replace('/^(amp;)*/', '', $key)] = $value;
        }
        $this->organization = Utl::extractItem('org', $this->urlQuery, 'tukos');
        $this->logo = ['tukos' => /*$this->rootUrl*/'https://tukos.site' . Tfk::$publicDir . 'images/tukosswissknife.png', 'tds' => Tfk::$publicDir . 'images/tdspetit.jpg'][$this->organization];
        $this->headerBanner = ['tukos' => 'headerBanner', 'tds' => 'tdsHeaderBanner'][$this->organization];
    }        
    public function setAppName($appName){
        return ['tukosapp' => 'TukosApp', 'tukossports' => 'TukosSports', 'tukosbus' => 'TukosBus', 'tukosblog' => 'TukosBlog', 'tukosmsqr' => 'TukosMSQR', 'tukoswoundtrack' => 'TukosWoundTrack'][strtolower($appName)];
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
