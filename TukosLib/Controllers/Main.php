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
        $username = Tfk::$registry->get('Authentication')->isAuthenticated($dialogue, $request);
        if ($username !== false){/* Proceed only if user is authorized */
        	SUtl::instantiate();
            $user = Tfk::$registry->get('user');
            if ($user->setUser(['name' => $username])){/* so as $user has the proper rights and other initialization information*/
	            try{
	                $controllerClass = 'TukosLib\\Controllers\\' . $request['controller'];
	                $controller = new $controllerClass();
	                if($controller->respond($request, $query)){
	                    //$dialogue->sendResponse();
	                }
	            }catch(\Exception $e){
	                //Tfk::debug_mode('log', 'an exception occured while responding to your request: ', $e->getMessage());
	                Feedback::add(Tfk::tr('errorrespondingrequest') . ': ' . $e->getMessage());
	                $dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(Feedback::get())));
	            }            
	            if ($streamsStore = Tfk::$registry->isInstantiated('streamsStore')){
	                Tfk::$registry->get('streamsStore')->waitOnStreams();
	            }
	            $storeProfiles = Tfk::$registry->get('store')->getProfiles();
	            $storeProfilesOutput = HUtl::page('Tukos Profiler Results',  HUtl::table($storeProfiles, []));
	            file_put_contents(Tfk::$tukosTmpDir . '/tukosstoreprofiles.html', $storeProfilesOutput);
	            $storeProfiles = Tfk::$registry->get('configStore')->getProfiles();
	            $storeProfilesOutput = HUtl::page('Tukos Profiler Results',  HUtl::table($storeProfiles, []));
	            file_put_contents(Tfk::$tukosTmpDir . '/tukosconfigstoreprofiles.html', $storeProfilesOutput);
            }else{
            	//Tfk::debug_mode('log', Tfk::tr('usersitemdoesnotexistforusername'));
            	Feedback::add(Tfk::tr('usersitemdoesnotexistforusername'));
            	$dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations(json_encode(Feedback::get())));
            }
        }//else{/* a new user needs to be authenticated: the response contains the login form */
        
            $dialogue->sendResponse(); 
        
        //}
    }
}
?>
