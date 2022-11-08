<?php
/**
 *
 * Class for user authentication 
 *
 */
namespace TukosLib\Auth;

use TukosLib\Auth\LoginPage;
use TukosLib\Utils\Cipher;
use TukosLib\Google\Client as GoogleClient;
use TukosLib\Utils\Feedback;
use TukosLib\Utils\Utilities as Utl;
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
    public function isAuthenticated($dialogue, $request, &$query){
        $showLogin = Utl::extractItem('showlogin', $query);
        switch ($request['object']){
            case 'auth':
                switch ($request['view']){
                    case 'LoginValidation':
                        $this->checkUserCredentials($dialogue);
                        break;
                    case 'LoginGoogleValidation':
                        $this->checkGoogleUserCredentials($dialogue);
                        break;
                    case 'Logout':
                        $this->session->destroy();
                        $dialogue->response->headers->set("Location",  $_SERVER['HTTP_REFERER']);
                        $dialogue->response->setStatusCode(302);
                        new LoginPage(Tfk::$registry->pageUrl, $showLogin);
                        break;
                }
                return false;
            case 'backoffice':
                if (isset($query['targetdb'])){
                    try{
                        if ($dbName = Cipher::decrypt(rawurldecode($query['targetdb']), Tfk::$registry->get('appConfig')->ckey, true)){
                            Tfk::$registry->get('appConfig')->dataSource['dbname'] = $dbName;
                        }else{
                            //Feedback::add(Tfk::tr('Usingdefaultdbfortukosapp'));
                        }
                    }catch (\Exception $e){//hack to attempt to handle change of cipher function from mbcrypt to Defuse\Crypto
                       //Feedback::add(Tfk::tr('Usingdefaultdbfortukosapp'));
                    }
                }
                return 'tukosBackOffice';
            default:// receiving a tukos application request check if authorized
                $segment = $this->session->getSegment(Tfk::$registry->appName);
                if ($segment->status !== 'VALID'){
                    if (in_array($request['controller'], ['Page', 'MobilePage'])){
                        new LoginPage(Tfk::$registry->pageUrl,$showLogin);
                    }else{
                        Feedback::add(Tfk::tr('invalidsessionreloadpage'));
                        $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(['feedback' => Feedback::get()])));
                    }
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
        $this->setSession($dialogue, $username, $targetDb);
    }
    public function checkGoogleUserCredentials($dialogue, $message = ''){
        $client = GoogleClient::get();
        $payload = $client->verifyIdToken($dialogue->getValues()['credential']);
        if ($payload) {
            $username = $payload['email'];
            $targetDb = Tfk::$registry->get('verifyUser')->googleUserTargetDb($username);
            $this->setSession($dialogue, $username, $targetDb);
        } else {
            // Invalid ID token
            $dialogue->response->setStatusCode(401);
        }
    }
    public function setSession($dialogue, $username, $targetDb){
        if ($targetDb === false){/* Authentication failed: notify user via http response */
            $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(Feedback::get())));
            $dialogue->response->setStatusCode(401);
        }else{/* Authentication succeeded: update session information, prepare $user global variable and redirect to the url initially requested */
            $segment = $this->session->getSegment(Tfk::$registry->appName/*'TukosAuth'*/);
            $segment->username = $username;
            $segment->targetDb = $targetDb;
            $segment->status = 'VALID';
            $this->session->regenerateId();
            $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(Tfk::tr('SUCCESSFULAUTHENTICATION')));
        }
    }
} 
?>
