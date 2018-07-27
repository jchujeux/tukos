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
        if ($request['object'] === 'auth'){
        	SUtl::instantiate();
        	Tfk::setTranslator();
        	switch ($request['view']){
                case 'loginValidation':
                    $this->checkCredentials($dialogue);
                    break;
                case 'logout':
                    $this->session->destroy();
                    //$welcomeUrl = Tfk::$registry->pageUrl . 'help/edit?storeatts=' . json_encode(['where' => ['name' => ['like',  '%welcome%']]]); 
                    //$dialogue->response->headers->set("Location",  $welcomeUrl);
                    $dialogue->response->headers->set("Location",  $_SERVER['HTTP_REFERER']);
                    $login = new LoginPage(Tfk::$registry->pageUrl);
                    break;
            }
            return false;
        }else{
            /*
             * receiving a tukosApp request. Check if authorized
             */
            $segment = $this->session->getSegment(Tfk::$registry->appName/*'TukosAuth'*/);
            if ($segment->status !== 'VALID'){
        		SUtl::instantiate();
        		Tfk::setTranslator();
            	$login = new LoginPage(Tfk::$registry->pageUrl);
                return false;
            }else{
                $this->session->commit();
                if (isset($segment->targetDb)){
                	Tfk::$registry->get('appConfig')->dataSource['dbname'] = $segment->targetDb;
                }
                return $segment->username; // user credentials are OK
            }
        }
    }
    
    public function checkCredentials ($dialogue){

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
            $dialogue->response->setContent(Tfk::tr('SUCCESSFULAUTHENTICATION'));
        }
        //return $userid;
    }
} 
?>
