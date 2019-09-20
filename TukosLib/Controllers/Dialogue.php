<?php 

namespace TukosLib\Controllers;

use TukosLib\Utils\Utilities as Utl;
use TukosLib\Utils\Translator;
use TukosLib\Utils\Feedback;
use TukosLib\Objects\StoreUtilities as SUtl;
use TukosLib\TukosFramework as Tfk;

class Dialogue extends Translator{

    function __construct(){
        parent::__construct(Tfk::$tr);
        $this->dialogue  = Tfk::$registry->get('dialogue');
    }

    function response($request, $query, $ignoreUnallowed = false){
        $objectsStore = Tfk::$registry->get('objectsStore');
        $objectController = $objectsStore->objectController($request['object']);
        if (isset($query['storeatts'])){
            $query['storeatts'] = json_decode($query['storeatts'], true);
/*
            if (!empty($query['storeatts']['where'])){
                $colsToUntranslate = array_intersect($objectController->model->colsToTranslate, array_keys($query['storeatts']['where']));//assumes there is not a ['col' => ..., 'opr' => ..., 'value' => ...] condition
                if (!empty($colsToUntranslate)){
                    $utr = Tfk::$registry->get('translatorsStore')->untranslator($objectController->objectName, $objectController->translationSetsPath);
                }
                foreach ($colsToUntranslate as $col){
                    if (is_array($query['storeatts']['where'][$col])){
                        $query['storeatts']['where'][$col][1] = $utr($query['storeatts']['where'][$col][1]);
                    }else{
                        $query['storeatts']['where'][$col] = $utr($query['storeatts']['where'][$col]);
                    }
                }
            }
*/
        }
        if (!empty($query['params'])){
            $query['params'] = json_decode($query['params'], true);
        }
        Tfk::$registry->timezoneOffset = Utl::extractItem('timezoneOffset', $query, 0);

        return $objectController->response($request, $query, $ignoreUnallowed);
    }

    function respond($request, $query, $ignoreUnallowed = false){
        $response = $this->response($request, $query, $ignoreUnallowed);
        if ($response !== false){
        	$this->responseToDialogue($response);
         	return true;
        }else{
        	return false;
        }
    }

    function responseToDialogue($response){
        //Feedback::add($this->tr('svrexectime') . (microtime(true) - Tfk::$startMicroTime));
        $response = json_encode(array_merge($response, array_filter(['extendedIds' => SUtl::translatedExtendedIdCols()]), ['extras' => Tfk::getExtras(), 'feedback' => Feedback::get()]));
        if ($response){
            $this->dialogue->response->setContent(Tfk::$registry->get('translatorsStore')->substituteTranslations($response));
        }else{
            Tfk::debug_mode('error', 'AbstractController->responseToDialogue - json_encoding error: ', json_last_error());
        }
    }
}  
?>
