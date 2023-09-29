<?php
namespace TukosLib\Auth;

use Aura\View\ViewFactory;
use TukosLib\Utils\Utilities as Utl;
use TukosLib\TukosFramework as Tfk;

class LoginPage{

    public function __construct($pageUrl, $showLoginPwd = '', $svrFeedback=""){
        $isMobile = Tfk::$registry->isMobile;
        $dialogue = Tfk::$registry->get('dialogue');
        $view = (new ViewFactory)->newInstance();
        $view->requestUrl = $pageUrl . 'auth/loginValidation';
        $view->requestGoogleValidationUrl = $pageUrl . 'auth/loginGoogleValidation';
        $view->dojoBaseLocation = Tfk::dojoBaseLocation();
        $view->tukosBaseLocation = Tfk::$tukosBaseLocation;
        $view->loginMessage = Tfk::tr('loginMessage');
        $view->username = Tfk::tr('username');
        $view->password = Tfk::tr('password');
        $view->login = Tfk::tr('Login');
        if ($showLoginPwd){
            if ($isMobile){
                $view->loginMessage = Tfk::tr('loginMessage');
                $view->username = Tfk::tr('username');
                $view->password = Tfk::tr('password');
                $view->login = Tfk::tr('Login');
                $view->addUserNameForm = 'loginView.addChild(formLayout);';
            }else{
                $view->loginPwd = 
                    '<tr><th></th><td>' . $view->loginMessage . '<br></td></tr>'
                  . '<tr><th>' . $view->username . ': </th><td><input type="text" name="username" oninput="hideSvrFeedback()" /></td></tr>'
                  . '<tr><th>' . $view->password . ': </th><td><input type="password" name="password" oninput="hideSvrFeedback()" /></td></tr>'
                  . '<tr><th></th><td><button type="submit">' . $view->login . '</button></td></tr>';
            }
        }else{
            $isMobile ? $view->addUserNameForm = '' : $view->loginPwd = '';
        }
        $view->authentication = Tfk::tr(Tfk::$registry->appName . 'HeaderBanner', 'none');// . ' - ' . Tfk::tr('Authentication');
        $view->serverFeedback = Tfk::tr($svrFeedback);
        $view->headerBanner = $isMobile ? Tfk::tr(Tfk::$registry->headerBanner, 'addslashes') : Tfk::tr(Tfk::$registry->headerBanner);
        $view->orgLink = Tfk::tr(Tfk::$registry->orgLink);
        $view->confidentialityPolicy =  '<font size=\"2\"><span style=\"font-style: italic;\"><a href=' . Tfk::$registry->rootUrl . Tfk::tr('privacypolicyurl') . ' target=\"_blank\">'  . 
            Tfk::tr('privacypolicy') . '</a></span></font>';
        $view->logo = Tfk::$registry->logo;
        $view->pageManagerArgs = json_encode(['isMobile' => Tfk::$registry->isMobile, 'messages' => Tfk::$registry->get('translatorsStore')->getTranslations(['authenticationfailed'], 'Page')]);
        $viewRegistry = $view->getViewRegistry();
        $viewRegistry->set('login', dirname(__FILE__) . ($isMobile ? '/mobileLoginTemplate.php' : '/loginTemplate.php'));
        
        $view->setView('login');
        
        $dialogue->response->setContent (Tfk::$registry->get('translatorsStore')->substituteTranslations($view()));
    }
}

?>
