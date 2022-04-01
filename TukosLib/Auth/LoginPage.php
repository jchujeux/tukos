<?php
namespace TukosLib\Auth;

/*use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;*/
use Aura\View\ViewFactory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class LoginPage{

    /*public function __construct($pageUrl, $svrFeedback=""){
        $dialogue = Tfk::$registry->get('dialogue');
        $template = new Template(new EscaperFactory, new TemplateFinder, new HelperLocator);
        $template->requestUrl = $pageUrl . 'auth/loginValidation';
        $template->dojoBaseLocation = Tfk::dojoBaseLocation();
        $template->tukosBaseLocation = Tfk::$tukosBaseLocation;
        $template->error = Tfk::tr('AUTHENTICATIONFAILED');
        $template->username = Tfk::tr('username');
        $template->password = Tfk::tr('password');
        $template->login = Tfk::tr('Login');
        $template->authentication = Tfk::tr(Tfk::$registry->appName . 'HeaderBanner', 'none');// . ' - ' . Tfk::tr('Authentication');
        $template->serverFeedback = Tfk::tr($svrFeedback);
        $template->headerBanner =  Tfk::tr(Tfk::$registry->headerBanner);
        $template->logo = Tfk::$registry->logo;
        
        $finder = $template->getTemplateFinder();
        $finder->setPaths([dirname(__FILE__)]);
        $dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($template->fetch(Tfk::$registry->isMobile ? 'mobileLoginTemplate.php' : 'loginTemplate.php')));
    }
    */
    public function __construct($pageUrl, $svrFeedback=""){
        $dialogue = Tfk::$registry->get('dialogue');
        $view = (new ViewFactory)->newInstance();
        $view->requestUrl = $pageUrl . 'auth/loginValidation';
        $view->dojoBaseLocation = Tfk::dojoBaseLocation();
        $view->tukosBaseLocation = Tfk::$tukosBaseLocation;
        $view->error = Tfk::tr('AUTHENTICATIONFAILED');
        $view->username = Tfk::tr('username');
        $view->password = Tfk::tr('password');
        $view->login = Tfk::tr('Login');
        $view->authentication = Tfk::tr(Tfk::$registry->appName . 'HeaderBanner', 'none');// . ' - ' . Tfk::tr('Authentication');
        $view->serverFeedback = Tfk::tr($svrFeedback);
        $view->headerBanner =  Tfk::tr(Tfk::$registry->headerBanner);
        $view->logo = Tfk::$registry->logo;
        
        $viewRegistry = $view->getViewRegistry();
        $viewRegistry->set('login', dirname(__FILE__) . (Tfk::$registry->isMobile ? '/mobileLoginTemplate.php' : '/loginTemplate.php'));
        
        $view->setView('login');
        
        $dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($view()));
    }
}

?>
