<?php
namespace TukosLib\Web;

use TukosLib\TukosFramework as Tfk; 

class TranslatorsManager {

    protected $translationsTable = 'translations';

    public function __construct($languages){
        $this->translatorsMessages = [];
        $this->supportedLanguages = $languages['supported'];
        $this->language = "en-us";
    }

    function setLanguage($theLanguage){
		$languageStrings = ['en_us' => 'english', 'fr_fr' => 'french', 'es_es' => 'spanish'];
    	$this->language = $theLanguage;  
		setLocale(LC_ALL, $languageStrings[$this->getLanguageCol()]);
    }

    function getLanguage(){
        return $this->language;
    }     

    function getLanguageCol(){
        return str_replace('-', '_', strtolower($this->language));
    }     

    function getTranslationsTable(){
        return $this->translationsTable;
    }     
    private function getSetMessages($setItem, $languageCol){
    	return Tfk::$registry->get('configStore')->getAll(['table' => 'translations', 'where' => ['setname' => $setItem], 'cols' => ['name', $languageCol]]);
    }
    
    private function addSet($setItem, $languageCol){
        if (!isset($this->translatorsMessages[$languageCol])){;
           $this->translatorsMessages[$languageCol] = [];
        }
        return $this->translatorsMessages[$languageCol][$setItem] = array_change_key_case(array_filter(array_column($this->getSetMessages($setItem, $languageCol), $languageCol, 'name')));
    }
      
    function translator($translatorName, $setsPath, $language = null){/* returns a translator callback - usage: $translator->translate('myMessage'); */        
        if (! $language){
            $language = $this->getLanguage();
        }
        $languageCol = str_replace('-', '_', strtolower($language));
    	if (!isset($this->translatorsMessages[$languageCol])){
    		$this->translatorsMessages[$languageCol] = [];
    	}
        $setItemsPath = [];
        foreach ($setsPath as $setName){
            $setItemsPath[] = strtolower($setName);
        }
        $this->translatorPaths[$translatorName] = $setItemsPath;
        $theTranslator = function($key, $mode = null) use ($setItemsPath, $languageCol){
            $lckey = strtolower($key);
            foreach ($setItemsPath as $setItem){
                $messages = $this->translatorsMessages[$languageCol];
            	if (!isset($messages[$setItem])){
                    $messages[$setItem] = $this->addSet($setItem, $languageCol);
                }
                $messages = $messages[$setItem];
                if (isset($messages[$lckey])){
                    $translation = $messages[$lckey];
                    if (!empty($mode)){
                        return $this->transform($translation, $mode);
                    }else if (strtoupper($key) === $key){
                        return mb_strtoupper($translation);
                    }else if(ctype_upper($key[0])/* && ctype_lower($translation[0])*/){
                        return mb_strtoupper(mb_substr($translation, 0, 1)) . mb_substr($translation, 1);
                    }else{
                        return $translation;
                    }
                }                    
            }
            return $key;
        };
        return $theTranslator;
    } 

    function untranslator($translatorName, $setsPath, $language = null){/* returns a translator callback - usage: $translator->translate('myMessage'); */        
        if (! $language){
            $language = $this->getLanguage();
        }
        $languageCol = str_replace('-', '_', strtolower($language));
        $setItemsPath = [];
        foreach ($setsPath as $setName){
            $setItemsPath[] = strtolower($setName);
        }
        $this->translatorPaths[$translatorName] = $setItemsPath;
        //$this->addSets($setItemsPath, $languageCol);
        return function($key) use ($setItemsPath, $languageCol){
            $lckey = strtolower($key);
            $messages = $this->translatorsMessages[$languageCol];
            foreach ($setItemsPath as $setItem){
                if (!isset($messages[$setItem])){
                    $messages[$setItem] = $this->addSet($setItem, $languageCol);
                }
                $translation = array_search($lckey, array_map('mb_strtolower', $messages[$setItem]));
                if (!empty($translation)){
                    return $translation;
                }                    
            }
            return $key;
        };
    } 
        
    function transform($translation, $mode){
        switch ($mode){
            case 'ucfirstOnly':
                return ucfirst(mb_strtolower($translation));
                break;
            default:
                return $translation;
        }
    }
}
?>
