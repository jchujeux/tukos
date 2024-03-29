<?php 
namespace TukosLib; 

use Aura\Session\Manager as SessionManager;
use Aura\Session\SegmentFactory;
use Aura\Session\CsrfTokenFactory;

use TukosLib\TukosFramework as Tfk; 
use TukosLib\Auth\Authentication;
use TukosLib\Auth\Drivers\Sql as SqlAuthentication;
use TukosLib\Auth\UserInformation;
use TukosLib\Store\Store;
use TukosLib\Web\Dialogue as WebDialogue;
use TukosLib\Web\TranslatorsManager;
use TukosLib\Objects\ObjectsManager;
use TukosLib\Objects\TukosModel;
use TukosLib\Objects\Admin\Scripts\StreamsManager;

class AbstractConfigure{

    protected static $_languages = ['default' => 'en-us', 'supported' => ['en-us', 'fr-fr', 'es-es']];
    public static function __initialize(){
        Tfk::$registry->set('translatorsStore', function(){
            return new TranslatorsManager(self::$_languages);
        });
        Tfk::setTranslator();
    }
    function __construct($modulesMenuLayout, $requiredModulesNotInLayout = [], $accordion = [], $requiresAuthentication = true, $dbName = ''){

        $this->appDir  = dirname(__FILE__);
        $this->ckey = MD5('XZK@w0kw' . getenv('MYSQL_ENV_VAR'));
        $this->dataSource = ['datastore' => 'mysql', 'host'   => 'localhost', 'admin'   => 'tukosAppAdmin', 'pass'   => $this->ckey, 'dbname'   => $dbName];
        $this->filesStore = ['datastore' => 'mysql', 'host'   => 'localhost', 'admin'   => 'tukosAppAdmin', 'pass'   => $this->ckey, 'dbname'   => $dbName . 'files'];
        $this->configSource = ['datastore' => 'mysql', 'host'   => 'localhost', 'admin'   => 'tukosAppAdmin', 'pass'   => $this->ckey, 'dbname' => 'tukosconfig', 'authstore' => 'sql',	'table' => 'users', 'username_col' => 'username', 'password_col' => 'password'];
        $this->languages = self::$_languages;

        Tfk::$registry->set('configStore', function(){
            return new Store($this->configSource);
        });
        Tfk::$registry->set('tukosModel', function(){
            return new TukosModel();
        });
        Tfk::$registry->set('objectsStore', function(){
            return new ObjectsManager();
        });
        Tfk::$registry->set('streamsStore', function(){
            return new StreamsManager();
        });
        
        $this->modulesMenuLayout = $modulesMenuLayout;
        $this->transverseModules = ['admin', 'collab'];
        $this->objectModulesDefaultContextName = ['tukos' => 'tukos', 'customviews' => 'tukos', 'customwidgets' => 'tukos'];
        $this->setobjectModulesDefaultContextName($this->modulesMenuLayout);
        $this->objectModules = array_unique(array_merge(array_keys($this->objectModulesDefaultContextName), $requiredModulesNotInLayout));
        
        $this->accordion = $accordion;
        
        $this->noPageCustomForAll = true;
        $this->noContextCustomForAll = true;
        
        if (Tfk::$registry->mode === 'interactive'){
            $request =Tfk::$registry->request;
            $query = Tfk::$registry->urlQuery;
            if (($request['controller'] === 'Page' && $request['view'] === 'Edit') && !isset($query['id']) && !isset($query['notab'])){
                $query['notab'] = 'yes';
                Tfk::$registry->urlQuery = $query;
            }
            Tfk::$registry->set('dialogue', function(){
                return new WebDialogue(Tfk::$registry->get('translatorsStore'));
            });
            if ($requiresAuthentication){
                Tfk::$registry->loader->add('Aura\Session\\'  , Tfk::$vendorDir['aura'] . 'package/Aura.Session/src/');
                Tfk::$registry->set('session', function(){
                    return new SessionManager(new SegmentFactory, new CsrfTokenFactory);
                });
                    Tfk::$registry->set('Authentication', function(){
                        return new Authentication($this->configSource);
                    });
                        Tfk::$registry->set('verifyUser', function(){
                            return new SqlAuthentication($this->configSource);
                        });
            }
        }
        Tfk::$registry->set('user', function(){
            return new UserInformation($this->objectModulesDefaultContextName, $this->modulesMenuLayout, $this->ckey);
        }); 
        Tfk::$registry->headerHelpLink = '';
    }

    function setobjectModulesDefaultContextName($modulesLayout){
        static $depth = 0;
        static $contextName = 0;
        $depth += 1;
        if (is_array($modulesLayout)){
            foreach($modulesLayout as $key => $layout){
                $module = (is_string($key) && in_array($key[0], ['#', '$', '@'])) ? substr($key, 1): $key;
                if ($depth === 1){
                    $contextName = (in_array($module, $this->transverseModules) ? 'tukos' : $module);
                }
                if ($module !== $key){
                    $this->objectModulesDefaultContextName[$module] = $contextName;
                }
                $this->setobjectModulesDefaultContextName($layout);
            }
        }
        $depth -= 1;
    }
    function headerHelpTukosTooltipLink($tooltipKey = 'allTukosHelpTukosTooltip', $tooltipObject = 'TukosLib', $label = 'allHelp'){
        return '<td><span style="text-decoration:underline; color:blue; cursor:pointer;font-style:italic;" onclick="tukos.Pmg.viewTranslatedInBrowserWindow(\'' . $tooltipKey . '\', \'' . $tooltipObject . '\');"> ' . Tfk::tr($label) .  '    </span></td>';
    }
}
?>
