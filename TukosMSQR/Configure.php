<?php 
namespace TukosMSQR; 

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

class Configure{

    function __construct(){

        $this->appDir  = dirname(__FILE__);
        $key =  'XZK@w0kw' . getenv('MYSQL_ENV_VAR');
        $this->ckey = MD5($key);
        $this->dataSource = ['datastore' => 'mysql', 'host'   => 'localhost', 'admin'   => 'tukosAppAdmin', 'pass'   => MD5('XZK@w0kw' . getenv('MYSQL_ENV_VAR')), 'dbname'   => 'tukos20'];
        $this->filesStore = ['datastore' => 'mysql', 'host'   => 'localhost', 'admin'   => 'tukosAppAdmin', 'pass'   => MD5('XZK@w0kw' . getenv('MYSQL_ENV_VAR')), 'dbname'   => 'tukos20files'];
        $this->configSource = ['datastore' => 'mysql', 'host'   => 'localhost', 'admin'   => 'tukosAppAdmin', 'pass'   => $this->ckey, 'dbname' => 'tukosconfig', 'authstore' => 'sql',	'table' => 'users', 'username_col' => 'username', 'password_col' => 'password'];
        $this->languages = ['default' => 'en-us', 'supported' => ['en-us', 'fr-fr', 'es-es']];

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
        Tfk::setTranslator();
        
        $request =Tfk::$registry->request;
        $query = Tfk::$registry->urlQuery;
        if (!isset($query['id']) && !isset($query['notab'])){
            $query['notab'] = 'yes';
            Tfk::$registry->urlQuery = $query;
        }
            
        $this->modulesMenuLayout = array_merge(($request['object'] === 'physiopersotreatments' && $request['view'] === 'Edit' && isset($query['id'])) 
            ? ['@physiopersoplans' => ['type' => 'MenuBarItem', 'atts' => ['onClickArgs' => ['object' => 'physiopersoplans', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['id' => ['object' => 'physiopersotreatments', 'id' => $query['id'], 'col' => 'parentid']]]]],
               '@physiopersotreatments' => ['type' => 'MenuBarItem', 'atts' => ['onClickArgs' => ['object' => 'physiopersotreatments', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['id' => $query['id']]]]]
              ]
            : ['$physiopersoplans' => [], '$physiopersotreatments' => []],
            ['#physiopersodailies' => [],
                '@help' => [
                    [/*'overview' => ['type' => 'MenuItem',     'atts' => ['onClickArgs' => ['object' => 'Help', 'view' => 'Overview', 'mode' => 'Tab', 'action' => 'Tab']]],*/
                    'guidedtour' => ['type' => 'MenuItem', 'atts' => [
                        'onClickArgs' => ['object' => 'Help', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['storeatts' => json_encode(['where' => ['name' => ['RLIKE', Tfk::tr('GuidedtourTukosMSQR')]]])]]]],
                    'tutotukos' => ['type' => 'MenuItem', 'atts' => [
                        'onClickArgs' => ['object' => 'Help', 'view' => 'Edit', 'mode' => 'Tab', 'action' => 'Tab', 'query' => ['storeatts' => json_encode(['where' => ['name' => ['RLIKE', Tfk::tr('TutotukosMSQR')]]])]]]]
                    ]]
            ]);
        $this->transverseModules = ['help'];
        $this->objectModulesDefaultContextName = ['tukos' => 'tukos', 'customviews' => 'tukos'];
        $this->setobjectModulesDefaultContextName($this->modulesMenuLayout);
        $this->objectModules = array_merge(array_keys($this->objectModulesDefaultContextName), ['users', 'people', 'organizations', 'physiopatients']);
        
        $this->mailConfig = ['host' => 'localhost', 'software' => 'Mercury'];
        
        $this->accordion = [
        ];
        
        $this->noPageCustomForAll = true;
        $this->noContextCustomForAll = true;
        
        if (Tfk::$registry->mode === 'interactive'){
            Tfk::$registry->set('dialogue', function(){
                return new WebDialogue(Tfk::$registry->get('translatorsStore'));
            });
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
        Tfk::$registry->set('user', function(){
            return new UserInformation($this->objectModulesDefaultContextName, $this->modulesMenuLayout, $this->ckey);
        }); 
    }

    function setobjectModulesDefaultContextName($modulesLayout){
        static $depth = 0;
        static $contextName = 0;
        $depth += 1;
        if (is_array($modulesLayout)){
            foreach($modulesLayout as $key => $layout){
                $module = in_array($key[0], ['#', '$', '@']) ? substr($key, 1): $key;
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
