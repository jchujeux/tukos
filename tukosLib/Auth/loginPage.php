<?php
namespace TukosLib\Auth;

use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;
use TukosLib\TukosFramework as Tfk;

class LoginPage{
    public function __construct($pageUrl){
        $dialogue = Tfk::$registry->get('dialogue');
        $template = new Template(new EscaperFactory, new TemplateFinder, new HelperLocator);
        $template->requestUrl = $pageUrl . 'auth/loginValidation';
        $template->dojoDir = Tfk::dojoBaseLocation();//jsFullDir('');
        //$template->jsTukosDir = Tfk::jsFullDir('tukos');//Tfk::jsTukosDir;
        $template->error = Tfk::tr('AUTHENTICATIONFAILED');
        $template->username = Tfk::tr('username');
        $template->password = Tfk::tr('password');
        $template->login = Tfk::tr('Login');
        $template->authentication = Tfk::tr('Authentication');
        $template->serverFeedback = Tfk::tr('serverFeedback');
        
        $finder = $template->getTemplateFinder();
        $finder->setPaths([dirname(__FILE__)]);
        $dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($template->fetch(Tfk::$registry->isMobile ? 'MobileLoginTemplate.php' : 'loginTemplate.php')));
    }
}

?>
