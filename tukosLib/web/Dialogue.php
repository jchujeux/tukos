<?php
namespace TukosLib\Web;

use TukosLib\TukosFramework as Tfk;
use Aura\Web\Context;
use Aura\Http\Manager\factory as HttpFactory;


class Dialogue {
    protected $http;

    public function __construct($translatorsStore){
        $this->translatorsStore = $translatorsStore;
        $this->context = new Context($GLOBALS); 
        $factory = new HttpFactory;
        $this->http = $factory->newInstance('curl');
        $this->response = $this->http->newResponse();
        $this->response->headers->set("Content-Type",  'text/html; charset=utf-8');
        $this->setToBrowserLanguage();
    }
    
   function setToBrowserLanguage(){
        $browserLanguages = [];
        if (array_key_exists('accept-language', $this->context->header)){/* set default to browser language if supported */
            // break up string into pieces (languages and q factors)
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $this->context->header['accept-language'], $lang_parse);
            if (count($lang_parse[1])) {
                // create a list like "en" => 0.8
                $browserLanguages = array_combine($lang_parse[1], $lang_parse[4]);                
                // set default to 1 for any without q factor
                foreach ($browserLanguages as $lang => $val) {
                    if ($val === '') $browserLanguages[$lang] = 1;
                }        
                // sort list based on value	
                arsort($browserLanguages, SORT_NUMERIC);
            }
        }
        $this->browserLanguage = $this->translatorsStore->getLanguage();
        $supportedLanguages = $this->translatorsStore->supportedLanguages;
        foreach ($browserLanguages as $lang => $val){
            if (in_array(strtolower($lang), $supportedLanguages)){
                $this->browserLanguage = $lang;
                break;
            }
        }
        $this->translatorsStore->setLanguage($this->browserLanguage);
    }
    
    function getBrowserLanguage(){
        return $this->browserLanguage;
    }
    function getLanguage(){
        return $this->translatorsStore->getLanguage();
    }
    function setLanguage($theLanguage){
        $this->translatorsStore->setLanguage($theLanguage);
    }
   /*
    * get the Rest method invoked by the request for this dialogue
    */
    public function getRestMethod(){
        return $this->context->server['REQUEST_METHOD'];
    }
    
   /*
    * gets values sent from the client
    */
    public function getValues(){
        $method = $this->getRestMethod();
        switch ($method){
            case 'POST':
            case 'PUT':
                $result = $this->context->getJsonInput(true);
                if (is_null($result)){
                    return $this->context->getPost();
                }else{
                    return $result;
                }
                break;
            default:
                Tfk::debug_mode('log', 'dialogue->getValues - method not supported - method : ', $method);  
                return false;             
        }
    }

    public function sendResponse(){
        $this->http->send($this->response);
    }
}
?>
