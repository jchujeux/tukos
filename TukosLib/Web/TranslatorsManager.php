<?php
namespace TukosLib\Web;

use TukosLib\Utils\Utilities as Utl;
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
    public function getSetMessages($setItem, $languageCol = null){
        if (! $languageCol){
            $languageCol = $this->getLanguageCol();
        }
        return array_column(Tfk::$registry->get('configStore')->getAll(['table' => 'translations', 'where' => ['setname' => $setItem], 'cols' => ['name', $languageCol]]), $languageCol, 'name');
    }
    public function getSetsMessages($setItems, $languageCol = null){
        if (! $languageCol){
            $languageCol = $this->getLanguageCol();
        }
        return array_column(Tfk::$registry->get('configStore')->getAll(['table' => 'translations', 'where' => [['col' => 'setname', 'opr' => 'IN', 'values' => $setItems]], 'cols' => ['name', $languageCol]]), $languageCol, 'name');
    }
    
    private function addSet($setItem, $languageCol){
        if (!isset($this->translatorsMessages[$languageCol])){;
           $this->translatorsMessages[$languageCol] = [];
        }
        return $this->translatorsMessages[$languageCol][$setItem] = array_change_key_case(array_filter($this->getSetMessages($setItem, $languageCol)));
    }
/*      
    function translator($translatorName, $setsPath, $language = null){// returns a translator callback - usage: $translator->translate('myMessage'); 
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
        return function($key, $mode = null) use ($setItemsPath, $languageCol){
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
                    }else if(ctype_upper($key[0])/* && ctype_lower($translation[0])*//*){
                        return mb_strtoupper(mb_substr($translation, 0, 1)) . mb_substr($translation, 1);
                    }else{
                        return $translation;
                    }
                }
            }
            return $key;
        };
    }
*/
    function translator($translatorName, $setsPath){
        $this->translatorPaths[$translatorName] = $setsPath;//array_map("strtolower", $setsPath);
        return function($key, $mode = null) use ($translatorName){
            return is_string($key) ?'#{' . implode('|', [$key, $mode, $translatorName]) . '}': $key;
            //return $key;
        };
    }
    
    function substituteTranslations($template){
        $names = []; $setNames = []; $pattern = "/[#]{([^}]*)}/";
        preg_match_all($pattern, $template, $matches);
        if (!empty($matches[1])){
            $matchesToTranslate = array_unique($matches[1]);
            array_walk($matchesToTranslate, function($placeHolder, $key) use (&$names, &$setNames){
                list($name, $mode, $translatorName) = explode('|', $placeHolder);
                $names[$name] = true;
                $setNames = array_unique(array_merge($setNames, $this->translatorPaths[$translatorName]));
            });
            $names = array_keys($names);
/*
            $languageCol = $this->getLanguageCol();
            $translations = Utl::toAssociativeGrouped(Tfk::$registry->get('configStore')->getAll([
                'table' => 'translations', 
                'where' => [['col' => 'setname', 'opr' => 'IN', 'values' => $setNames], ['col' => 'name', 'opr' => 'IN', 'values' => $names]], 
                'cols' => ['name', 'setname', $languageCol]]), 'name', true);
            array_walk($translations, function(&$translation, $name) use ($languageCol){
                $translation = array_column($translation, $languageCol, 'setname');
            });
            $translations = array_change_key_case(array_filter($translations));
*/
            $translations = $this->_getTranslations($names, $setNames);
            $i = 0;
            foreach($matchesToTranslate as &$match){
                list($name, $mode, $translatorName) = explode('|', $match);
                $translatedName = (empty($nameTranslations = Utl::getItem(strtolower($name), $translations))|| empty($activeSets = array_intersect($this->translatorPaths[$translatorName], array_keys($nameTranslations))) || 
                                   empty($nameTranslation = $nameTranslations[reset($activeSets)]))
                    ? $name
                    : preg_replace('/([^\\\\])"/', '$1\\"', $nameTranslation);
                    //: preg_replace('/([^\\\\])(["\'])/', '$1\\\\$2', $nameTranslation);
                if (!empty($mode)){
                    $translation = $this->transform($translatedName, $mode);
                }else if (strtoupper($name) === $name){
                    $translation = mb_strtoupper($translatedName);
                }else if(ctype_upper($name[0])){
                    $translation = mb_strtoupper(mb_substr($translatedName, 0, 1)) . mb_substr($translatedName, 1);
                }else{
                    $translation = $translatedName;
                }
                $replacements[] = $translation;
                $match = "#{" . $match . "}";
            }
            return str_replace($matchesToTranslate, $replacements, $template);
        }else{
            return $template;
        }
    }
    function getTranslations($names, $translatorName){
        $translationValues = $this->_getTranslations($names, $this->translatorPaths[$translatorName]);
        foreach($names as $name){
            $translations[$name] = ($value = Utl::getItem($name, $translationValues)) === null ? $name : reset($value); 
        }
        return $translations;
    }
    function _getTranslations($names, $setNames){
        $languageCol = $this->getLanguageCol();
        $translations = Utl::toAssociativeGrouped(Tfk::$registry->get('configStore')->getAll([
            'table' => 'translations',
            'where' => [['col' => 'setname', 'opr' => 'IN', 'values' => $setNames], ['col' => 'name', 'opr' => 'IN', 'values' => $names]],
            'cols' => ['name', 'setname', $languageCol]]), 'name', true);
        array_walk($translations, function(&$translation, $name) use ($languageCol){
            $translation = array_column($translation, $languageCol, 'setname');
        });
        return array_change_key_case(array_filter($translations));
    }
    function untranslator($translatorName, $setsPath, $language = null){/* returns a translator callback - usage: $translator->translate('myMessage'); */        
        if (! $language){
            $language = $this->getLanguage();
        }
        $languageCol = str_replace('-', '_', strtolower($language));
        if (!isset($this->translatorsMessages[$languageCol])){
            $this->translatorsMessages[$languageCol] = [];
        }
        /*
         $setItemsPath = [];
        foreach ($setsPath as $setName){
            $setItemsPath[] = strtolower($setName);
        }
        $this->translatorPaths[$translatorName] = $setItemsPath;
*/
        $this->translatorPaths[$translatorName] = $setsPath;
        return function($key) use ($setsPath, $languageCol){
            $lckey = mb_strtolower($key);
            $messages = $this->translatorsMessages[$languageCol];
            foreach ($setsPath as $setItem){
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
            case 'escapeSQuote':
                return Utl::escapeSQuote($translation);
                break;
            case 'ucfirst':
                return ucfirst(mb_strtolower($translation));
                break;
            case 'ucwords':
                return ucwords(mb_strtolower($translation));
                break;
            case 'none':
            default:
                return $translation;
        }
    }
}
?>
