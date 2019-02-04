<?php
/**
 *
 * Class for user authentication 
 *
 */
namespace TukosLib\Auth;

use TukosLib\Auth\LoginPage;
use TukosLib\utils\Feedback;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class Authentication{
    public function __construct(){
        $session = Tfk::$registry->get('session');
        $segment = $session->getSegment(Tfk::$registry->appName/*'TukosAuth'*/);
        if (! isset($segment)) {/* No tukos session exists from this requester on this server */
            $segment->status = "NOTVALID";
            $session->regenerateId();
        }
        $this->session = $session;
    }
    public function isAuthenticated($dialogue, $request){
        switch ($request['object']){
            case 'auth':
                switch ($request['view']){
                    case 'loginValidation':
                        $this->checkUserCredentials($dialogue);
                        break;
                    case 'logout':
                        $this->session->destroy();
                        $dialogue->response->headers->set("Location",  $_SERVER['HTTP_REFERER']);
                        $login = new LoginPage(Tfk::$registry->pageUrl);
                        break;
                }
                return false;
            case 'backoffice':
                //return $this->checkBackOfficeCredentials($dialogue);
                return 'tukosBackOffice';
            default:// receiving a tukos application request check if authorized
                $segment = $this->session->getSegment(Tfk::$registry->appName);
                if ($segment->status !== 'VALID'){
                    //SUtl::instantiate();
                    //Tfk::setTranslator();
                    $login = new LoginPage(Tfk::$registry->pageUrl);
                    return false;
                }else{
                    $this->session->commit();
                    if (isset($segment->targetDb)){
                        Tfk::$registry->get('appConfig')->dataSource['dbname'] = $segment->targetDb;
                    }
                    return $segment->username;
                }
        }
    }
    public function checkUserCredentials ($dialogue){
    	$username = $dialogue->context->getPost('username');
        $targetDb = Tfk::$registry->get('verifyUser')->targetDb($username, MD5($dialogue->context->getPost('password')));
        if ($targetDb === false){/* Authentication failed: notify user via http response */
            $dialogue->response->setStatusCode(401);
            //Feedback::add('username:' . $username . ' , 'password: ' . $password');
        }else{/* Authentication succeeded: update session information, prepare $user global variable and redirect to the url initially requested */            
            $segment = $this->session->getSegment(Tfk::$registry->appName/*'TukosAuth'*/);
            $segment->username = $username;
            $segment->targetDb = $targetDb;
            $segment->status = 'VALID'; 
            $this->session->regenerateId();
            $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(Tfk::tr('SUCCESSFULAUTHENTICATION')));
        }
    }
    public function checkBackOfficeCredentials($dialogue){
        $username = $dialogue->context->getPost('username');
        if (empty(Tfk::$registry->get('verifyUser')->getUser($username, $dialogue->context->getPost('password')))){
            return false;
        }else{
            return $username;
        }
    }
    
} 
?>
