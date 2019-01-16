<?php 
namespace TukosSports; 

use Aura\Session\Manager as SessionManager;
use Aura\Session\SegmentFactory;
use Aura\Session\CsrfTokenFactory;

use TukosLib\TukosFramework as Tfk; 
use TukosLib\Utils\Widgets;
use TukosLib\Auth\Authentication;
use TukosLib\Auth\Drivers\Sql as SqlAuthentication;
use TukosLib\Auth\UserInformation;
use TukosLib\Store\Store;
use TukosLib\Web\Dialogue as WebDialogue;
use TukosLib\Web\TranslatorsManager;
use TukosLib\Objects\ObjectsManager;
use TukosLib\Objects\TukosModel;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Objects\Admin\scripts\StreamsManager;

class Configure{

    function __construct(){

        $this->appDir  = dirname(__FILE__);
        $key =  'XZK@w0kw' . getenv('MYSQL_ENV_VAR');
        $this->ckey = MD5($key);
        $this->dataSource = ['datastore' => 'mysql', 'host'   => '127.0.0.1', 'admin'   => 'tukosSportsAdmin', 'pass'   => MD5('XZK@w0kw' . getenv('MYSQL_ENV_VAR')), 'dbname'   => 'tukossports'];
        $this->filesStore = ['datastore' => 'mysql', 'host'   => '127.0.0.1', 'admin'   => 'tukosSportsAdmin', 'pass'   => MD5('XZK@w0kw' . getenv('MYSQL_ENV_VAR')), 'dbname'   => 'tukos20sportsfiles'];
        $this->configSource = ['datastore' => 'mysql', 'host'   => '127.0.0.1', 'admin'   => 'tukosAppAdmin', 'pass'   => $this->ckey, 'dbname' => 'tukosconfig', 'authstore' => 'sql',	'table' => 'users', 'username_col' => 'username', 'password_col' => 'password'];
        $this->languages = ['default' => 'en-us', 'supported' => ['en-us', 'fr-fr', 'es-es']];

        $this->modulesMenuLayout = [
            'admin' => [[
                '#users' => [['#customviews' => [], '#navigation' => []]], '#contexts' => [], '#objrelations' => [], '#translations' => [],
                'mail' => [[
                    '#mailsmtps' => [], '#mailservers' => [], '#mailaccounts' => [],
                    '#mailtukosmessages' => [],
                ]],
                '#scripts' => [['#scriptsoutputs' => []]], '#health' => [['#healthtables' => []]],
            ]],
            'collab' => [['#people' => [], '#organizations' => []/*, '#teams' => []*/, '#notes' => []/*, '#documents' => []*/, '#calendars' => [['#calendarsentries' => []]], '#tasks' => []]],
            'bustrack' => [['#bustrackcatalog' => [], '#bustrackcustomers' => [], '#bustrackquotes' => [], '#bustrackinvoices' => []]],
        	'sports' => [['#sptathletes' => [], '#sptprograms' => [],  '#sptsessions' => [], '#sptsessionsstages' => [], '#sptexercises' => []]],
            'physio' => [['#physiopatients' => [], '#physioprescriptions' => [], '#physioassesments' => [], '#physiocdcs' => [], '#physiotemplates' => []]],
            '#help' => [],
        ];
        $this->transverseModules = ['admin', 'collab', 'help'];
        $this->objectModulesDefaultContextName = [];
        $this->setobjectModulesDefaultContextName($this->modulesMenuLayout);
        $this->objectModules = array_keys($this->objectModulesDefaultContextName);
        
        $this->mailConfig = ['host' => 'localhost', 'software' => 'Mercury'];
        
        $this->accordion = [
        	['object' => 'help'     , 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'userContext'],
        	['object' => 'calendars' , 'view' => 'Edit', 'action' => 'Accordion', 'pane' => 'calendar', 'title' => 'calendar', 'config' => ['hasId' => true, 'hasCustomViewId'=> true]],
        	['object' => 'tukos'     , 'view' => 'Overview', 'action' => 'Accordion', 'pane' => 'search', 'title' => 'search', 'config' => ['hasCustomViewId' => true]],
        	['object' => 'users'     , 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'log'],
        	['object' => 'navigation', 'view' => 'Pane', 'action' => 'Accordion', 'pane' => 'navigationTree'],
        ];
        
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
        Tfk::$registry->set('translatorsStore', function(){
            return new TranslatorsManager($this->languages);
        }); 

//        SUtl::instantiate();

        if (Tfk::$registry->mode === 'interactive'){
            Tfk::$registry->set('dialogue', function(){
                return new WebDialogue(Tfk::$registry->get('translatorsStore'));
            });
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
        Tfk::$registry->set('user', function(){
            return new UserInformation($this->objectModulesDefaultContextName, $this->modulesMenuLayout, $this->ckey);
        }); 
    }

    function setobjectModulesDefaultContextName($modulesLayout){
        static $depth = 0;
        static $contextName = 0;
        $this->objectModulesDefaultContextName['tukos'] = 'tukos';
        $depth += 1;
        if (is_array($modulesLayout)){
            foreach($modulesLayout as $key => $layout){
                $module = (($key[0] === '#' || $key[0] === '@') ? substr($key, 1): $key);
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
}
?>
