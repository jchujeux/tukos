<?php
namespace TukosLib\Auth;

use Aura\View\Template;
use Aura\View\EscaperFactory;
use Aura\View\TemplateFinder;
use Aura\View\HelperLocator;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class LoginPage{
    public function __construct($pageUrl, $svrFeedback=""){
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
}

?>
