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
    function translator($translatorName, $setsPath){
        $this->translatorPaths[$translatorName] = $setsPath;
        return function($key, $mode = null) use ($translatorName){
            if (is_string($key)) {
                return '#{' . implode('|', [$key, json_encode($mode), $translatorName]) . '}@';
            }else if (is_array($key)){
                if (is_array($key[0])){//case where one would send e.g. [['yes', 'totranslate'], ['no', 'tonottranslate']]. We most probably don't use this (?)
                    $translation = '';
                    foreach ($key as $subKey){
                        $translation .= ($subKey[0] === 'no' ? $subKey[1] : '#{' . implode('|', [$subKey[1], $mode, $translatorName]) . '}@');
                    }
                    return $translation;
                }else{//assumes [$string, $mode or [$mode1, mode2, ...]] is sent, e.g. ['Just_Do_It', '_To '] or ['Just_Do_It', ['_To ', 'ucfirst']]
                    $translation =  '#{' . implode('|', [$key[0], json_encode($key[1]), $translatorName]) . '}@';
                    return $translation;
                }
            }else{
                return $key;
            }
        };
    }
    function substituteTranslations($template){
        $names = []; $setNames = []; $pattern = "/[#]{([^}][^@]*)}@/";
        preg_match_all($pattern, $template, $matches);
        if (!empty($matches[1])){
            $matchesToTranslate = array_unique($matches[1]);
            array_walk($matchesToTranslate, function($placeHolder, $key) use (&$names, &$setNames){
                list($name, $mode, $translatorName) = explode('|', $placeHolder);
                $names[$name] = true;
                $setNames = array_unique(array_merge($setNames, $this->translatorPaths[$translatorName]));
            });
            $names = array_keys($names);
            $translations = $this->_getTranslations($names, $setNames);
            foreach($matchesToTranslate as &$match){
                list($name, $mode, $translatorName) = explode('|', $match);
                $getTranslation = function($name, $translations, $translatorPaths, $translatorName){
                    if (empty($nameTranslations = Utl::getItem(strtolower($name), $translations))|| empty($activeSets = array_intersect($translatorPaths[$translatorName], array_keys($nameTranslations))) || empty($nameTranslation = $nameTranslations[reset($activeSets)])){
                        $subNames = explode('_', $name); $translatedSubNames = [];
                        if (count($subNames) > 1){
                            foreach($subNames as $subName){
                                $translatedSubNames[] =  (empty($nameTranslations = Utl::getItem(strtolower($subName), $translations))|| empty($activeSets = array_intersect($translatorPaths[$translatorName], array_keys($nameTranslations))) ||
                                                          empty($nameTranslation = $nameTranslations[reset($activeSets)]))
                                   ? $subName
                                   : $nameTranslation;
                            }
                            return implode('_', $translatedSubNames);
                        }else{
                            return $name;
                        }
                    }else{
                        return $nameTranslation;
                    }
                };
                $translatedName = preg_replace('/([^\\\\])"/', '$1\\"', $getTranslation($name, $translations, $this->translatorPaths, $translatorName));
                //$translatedName = $getTranslation($name, $translations, $this->translatorPaths, $translatorName);
                $mode = json_decode(json_decode('"' . $mode . '"', true), true);// because $mode is a substring of $template which is json encoded
                $isPlural = is_array($mode) ? in_array('plural', $mode) : $mode === 'plural';
                $translatedNames = explode('¤', $translatedName);
                $translatedName = $isPlural ? Utl::getItem(1, $translatedNames, $translatedNames[0] . 's') : $translatedNames[0];
                if (!empty($mode)){
                    if (is_array($mode)){
                        $translation = $translatedName;
                        foreach($mode as $subMode){
                            $translation = Utl::transform($translation, $subMode);
                        }
                    }else{
                        $translation = Utl::transform($translatedName, $mode);
                    }
                }else if (strtoupper($name) === $name){
                    $translation = mb_strtoupper($translatedName);
                }else if(ctype_upper($name[0])){
                    $translation = mb_strtoupper(mb_substr($translatedName, 0, 1)) . mb_substr($translatedName, 1);
                }else{
                    $translation = $translatedName;
                }
                $replacements[] = $translation;
                $match = "#{" . $match . "}@";
            }
            return str_replace($matchesToTranslate, $replacements, $template);
        }else{
            return $template;
        }
    }
    function getTranslations($names, $translatorName){
        if (empty($names)){
            return [];
        }else{
            $translationValues = $this->_getTranslations($names, $this->translatorPaths[$translatorName]);
            foreach($names as $name){
                $translations[$name] = ($value = Utl::getItem($name, $translationValues)) === null ? $name : reset($value);
            }
            return $translations;
        }
    }
    function _getTranslations($names, $setNames){
        $languageCol = $this->getLanguageCol();
        $names = array_filter($names, function($name){
            return !empty($name) && !is_numeric($name) && !str_starts_with($name, '[');
        });
        if (empty($names)){
            return [];
        }else{
            $namesToTranslate = [];
            array_walk($names, function($name) use (&$namesToTranslate) {
                $subNames = explode('_', $name);
                $namesToTranslate[] = $name;
                if (count($subNames) > 1){
                    foreach($subNames as $subName){
                        if (!is_numeric($subName)){
                            $namesToTranslate[] = $subName;
                        }
                    }
                }
            });
            $translations = Utl::toAssociativeGrouped(Tfk::$registry->get('configStore')->getAll([
                'table' => 'translations',
                'where' => [['col' => 'setname', 'opr' => 'IN', 'values' => $setNames], ['col' => 'name', 'opr' => 'IN', 'values' => $namesToTranslate]],
                'cols' => ['name', 'setname', $languageCol]]), 'name', true);
            array_walk($translations, function(&$translation, $name) use ($languageCol){
                $translation = array_column($translation, $languageCol, 'setname');
            });
            return array_change_key_case(array_filter($translations));
        }
    }
    function untranslator($translatorName, $setsPath, $language = null){/* returns a translator callback - usage: $translator->translate('myMessage'); */        
        if (! $language){
            $language = $this->getLanguage();
        }
        $languageCol = str_replace('-', '_', strtolower($language));
        if (!isset($this->translatorsMessages[$languageCol])){
            $this->translatorsMessages[$languageCol] = [];
        }
        $this->translatorPaths[$translatorName] = $setsPath;
        return function($key) use ($setsPath, $languageCol){
            if (empty($key) || is_numeric($key) || str_starts_with($key, '[')){
                return $key;
            }
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
            $subTranslations = explode('_', $lckey);
            if (count($subTranslations) > 1){
                $unTranslations = [];
                foreach($subTranslations as $subTranslation){
                    $lastUnTranslation = $subTranslation;
                    foreach($setsPath as $setItem){
                        $unTranslation = array_search($subTranslation, array_map('mb_strtolower', $messages[$setItem]));
                        if (!empty($unTranslation)){
                            $lastUnTranslation = $unTranslation;
                            break;
                        }
                    }
                    $unTranslations[] = $lastUnTranslation;
                }
                return implode('_', $unTranslations);
            }
            return $key;
        };
    } 
    function presentTukosTooltips(){
        return Tfk::$registry->get('configStore')->query("SELECT name FROM translations WHERE name RLIKE 'TukosTooltip$'")->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
?>
