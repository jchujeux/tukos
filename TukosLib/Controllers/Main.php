<?php 
/*
 * The entry point for every call toward the tukosApp
 * Intended to initialize the tukosApp specific environment, and then call the adequate controller, which then will dispatch towards the requested action
 */
namespace TukosLib\Controllers;

use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\Utils\HtmlUtilities as HUtl;
use TukosLib\Utils\Feedback;
use TukosLib\TukosFramework as Tfk;

class Main{

    function __construct($request, $query){

        $dialogue = Tfk::$registry->get('dialogue');
        $appConfig = Tfk::$registry->get('appConfig');
        if (isset($appConfig->userName)){
            $username = $appConfig->userName;
        }else{
            $authentication = Tfk::$registry->get('Authentication');
            $username = $authentication->isAuthenticated($dialogue, $request, $query);
        }
        if ($username !== false){/* Proceed only if user is authorized */
            SUtl::instantiate();
            $user = Tfk::$registry->get('user');
            if ($user->setUser(['name' => $username])){/* so as $user has the proper rights and other initialization information*/
                if ($request['controller'] === 'Page'){
                    if (!$user->isPageAllowed($request['object'])){
                        $authentication->session->destroy();
                        Feedback::add(Tfk::tr('accessdeniedtomodule') . ': ' . Tfk::tr($request['object']));
                        $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(Feedback::get())));
                        $dialogue->sendResponse();
                        return;
                    }else{
                        list($request, $query) = $user->getCustomTukosUrl($request, $query);
                    }
                }else{
                    if ($request['controller'] === 'Dialogue' && !isset($_SERVER['HTTP_REFERER'])){
                        Feedback::add(Tfk::tr('urlerror') . ': ' . $request['controller']);
                        $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(Feedback::get())));
                        $dialogue->sendResponse();
                        return;
                    }
                }
                try{
                    $controllerClass = 'TukosLib\\Controllers\\' . $request['controller'];
                    $controller = new $controllerClass();
                    if($controller->respond($request, $query)){
                        $dialogue->sendResponse();
                    }
                }catch(\Exception $e){
                    Feedback::add(Tfk::tr('errorrespondingrequest') . ': ' . $e->getMessage());
                    $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(Feedback::get())));
                    $dialogue->sendResponse();
                }
                if (Tfk::$registry->isInstantiated('streamsStore')){
                    Tfk::$registry->get('streamsStore')->waitOnStreams();
                }
            }else{
                if (isset($authentication)){
                    //$authentication->logoutUser($dialogue, 'usersitemdoesnotexistforusername');
                }
            	$dialogue->sendResponse();
            }
            $storeProfiles = Tfk::$registry->get('store')->profilerMessages();
            $storeProfilesOutput = HUtl::page('Tukos Profiler Results',  HUtl::table($storeProfiles, []));
            file_put_contents(Tfk::$tukosTmpDir . '/tukosstoreprofiles.html', $storeProfilesOutput);
        }else{
            $dialogue->sendResponse();
        }
    }
}
?>
