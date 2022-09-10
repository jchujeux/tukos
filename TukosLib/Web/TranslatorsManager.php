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
            $i = 0;
            foreach($matchesToTranslate as &$match){
                list($name, $mode, $translatorName) = explode('|', $match);
                $translatedName = (empty($nameTranslations = Utl::getItem(strtolower($name), $translations))|| empty($activeSets = array_intersect($this->translatorPaths[$translatorName], array_keys($nameTranslations))) || 
                                   empty($nameTranslation = $nameTranslations[reset($activeSets)]))
                    ? $name
                    : preg_replace('/([^\\\\])"/', '$1\\"', $nameTranslation);
                    $mode = json_decode(json_decode('"' . $mode . '"', true), true);// because $mode is a substring of $template which is json encoded
                $isPlural = is_array($mode) ? in_array('plural', $mode) : $mode === 'plural';
                if ($isPlural){
                    $isOK = true;
                }
                $translatedNames = explode('¤', $translatedName);
                $translatedName = $isPlural ? Utl::getItem(1, $translatedNames, $translatedNames[0] . 's') : $translatedNames[0];
                if (!empty($mode)){
                    if (is_array($mode)){
                        $translation = $translatedName;
                        foreach($mode as $subMode){
                            $translation = $this->transform($translation, $subMode);
                        }
                    }else{
                        $translation = $this->transform($translatedName, $mode);
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
            case 'ucfirst':
                return ucfirst(mb_strtolower($translation));
            case 'lowercase':
                return mb_strtolower($translation);
            case 'uppercase':
                return mb_strtoupper($translation);
            case 'ucwords':
                return ucwords(mb_strtolower($translation));
            case 'plural':
            case 'none':
            case null:
            case '':
                return $translation;
            default://assumes it is an array describing the transformation, e.g. ['replace', ['(', ')'], ' ']
                switch ($mode[0]){
                    case 'replace':
                        return str_replace($mode[1], $mode[2], $translation);
                    case 'substitute':
                        return Utl::substitute($translation, $mode[1]);
                    default:
                        return $translation;
                        
                }
        }
    }
    function presentTukosTooltips(){
        return Tfk::$registry->get('configStore')->query("SELECT name FROM translations WHERE name RLIKE 'TukosTooltip$'")->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}
?>
