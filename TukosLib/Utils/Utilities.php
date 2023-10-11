<?php
namespace TukosLib\Utils;

class Utilities{
    public static function nullToBlank($value){
        return  ($value === null ? '' : $value);
    }
    public static function nullToBlankFloatVal($value){
        return  ($value === null ? '' : floatval($value));
    }
    public static function blankToNull($value, $keys = []){
        return  ($value === '' ? null : $value);
    }
    public static function arrayCallbackByKeys($values, $callback, $keys = []){
        if (empty($keys)){
            foreach($values as &$value){
                $value = $callback($value);
            }
        }else{
            $presentKeys = array_intersect(array_keys($values), $keys);
            foreach($presentKeys as $key){
                $values[$key] = $callback($values[$key]);
            }
        }
        return $values;
    }
    public static function nullToBlankArray($values, $keys = []){
        return self::arrayCallbackByKeys($values, ['TukosLib\Utils\Utilities', 'nullToBlank'], $keys);
    }
    public static function blankToNullArray($values, $keys = []){
        return self::arrayCallbackByKeys($values, ['TukosLib\Utils\Utilities', 'blankToNull'], $keys);
    }
    public static function jsonDecode($json, $assoc=true){
        return is_array($json) ? $json : json_decode($json, $assoc);
    }
    public static function extractItem($atKey, &$fromArray, $absentValue = null, $emptyValue = 'nochange'){
        if (array_key_exists($atKey, $fromArray)){
            $item = $fromArray[$atKey];
            unset($fromArray[$atKey]);
            return !empty($item) || $emptyValue === 'nochange' ? $item : $emptyValue;
        }else{
			return $absentValue;
        }
    }
    public static function extractItems($atKeys, &$fromArray, $excludeAbsent = true, $absentValue = null, $emptyValue = 'nochange'){
        $result = [];
        $keys = ($excludeAbsent ? array_intersect($atKeys, array_keys($fromArray)) : $atKeys);
        foreach ($keys as $key){
            $result[$key] = self::extractItem($key, $fromArray, $absentValue, $emptyValue);
        }
        return $result;
    }
    public static function getItem($atKey, $fromArray, $absentValue = null, $emptyValue = 'nochange'){
        return array_key_exists($atKey, $fromArray) 
        		? (!empty($fromArray[$atKey]) || $emptyValue === 'nochange' ? $fromArray[$atKey] : $emptyValue) 
        		: $absentValue;
    }
    public static function getItems($atKeys, $fromArray, $excludeAbsent = true, $absentValue = null, $emptyValue = 'nochange'){
        $result = [];
        $keys = ($excludeAbsent ? array_intersect($atKeys, array_keys($fromArray)) : $atKeys);
        foreach ($keys as $key){
            $result[$key] = self::getItem($key, $fromArray, $absentValue, $emptyValue);
        }
        return $result;
    }
    public static function concat($values, $separator = ' ', $trimAt = 255){
        $result = implode($separator, $values);
        if (strlen($result) > $trimAt){
            $result = mb_substr($result, 0, $trimAt-4) . ' ...';
        }  
        return $result;
    }
   /*
    * Adds $value to $array[$key], initializing it when necessary
    */
    public static function increment(&$array, $key, $value){
        if (array_key_exists($key, $array)){
            $array[$key] += $value;
        }else{
            $array[$key] = $value;
        }
    }
    public static function incrementArray($array1, $array2){
        foreach ($array2 as $key => $value){
            self::increment($array1, $key, $value);
        }
        return $array1;
    }
   /*
    * Increments 2D array value, initializing it if necessary
    */
    public static function increment2D(&$array, $rowKey, $colKey, $value){
        if (!array_key_exists($rowKey, $array)){
            $array[$rowKey] = [];
        }
        self::increment($array[$rowKey], $colKey, $value);
        return $array;
    }
   /*
    * converts an object into an associative array (no check made on the proper object format for this). Intended to be used to convert Json objects containing associative array information
    */
    public static function objectToArray($object){
        $result = [];
        foreach ($object as $key => $value) {
            $valueType = gettype($value);
            switch (gettype($value)){
                case 'object':
                case 'array':
                    $result[$key] = self::objectToArray($value);
                    break;
                default:
                    $result[$key] = $value;
            }
        }        
        return (array)$result;
    }

   /*
    * If multidimensional array, transforms into a 1D array via json encoding the sub-arrays
    */
    public static function jsonEncodeArray($array){ 
        $jsonArray = [];
        foreach ($array as $key => $value){
            if (is_array($value)){
                $jsonArray[$key] = json_encode($value);
            }else{
                $jsonArray[$key] = $value;
            }
        }
        return $jsonArray;
    }
   /*
    * Returns subArray[remainingKeys[0]][remainingKeys[1]]...remainingKeys[last]] if found, or else $notFoundValues;
    */
    public static function drillDown($subArray, $remainingKeys, $notFoundValue=null){
        if (empty($remainingKeys) || ! is_array($remainingKeys)){
            return $subArray;
        }else{
            $key = array_shift($remainingKeys);    
            if (isset($subArray[$key])){
                if (is_array($subArray[$key])){
                    return self::drillDown($subArray[$key], $remainingKeys, $notFoundValue);
                }else{
                    return $subArray[$key];
                }
            }else{
                return $notFoundValue;
            }
        }
    }
    public static function drillDownReplace(&$subArray, &$subArrayFilter, $replaceValue){
        if (is_array($subArrayFilter)){
            foreach ($subArrayFilter as $key => &$value){
                if (isset($subArray[$key])){
                    if (is_array($subArrayFilter[$key])){
                        Self::drillDownReplace($subArray[$key], $subArrayFilter[$key]);
                    }else if($subArrayFilter[$key] === true){
                        $subArray[$key] = $replaceValue;
                    }
                }
            }
        }
    }
    public static function drillDownDelete(&$subArray, &$subArrayFilter, $deleteCallback = null){
        if (is_array($subArrayFilter)){
            foreach ($subArrayFilter as $key => &$value){
                if (isset($subArray[$key])){
                    if ($deleteCallback !== null && call_user_func($deleteCallback, $value)){
                        unset($subArray[$key]);
                    }else{
                        if (is_array($subArrayFilter[$key])){
                            Self::drillDownDelete($subArray[$key], $subArrayFilter[$key]);
                            if (empty($subArray[$key])){
                                unset($subArray[$key]);
                            }
                        }else if($subArrayFilter[$key] === true){
                            unset($subArray[$key]);
                        }
                    }
                }
            }
            if (empty($subArray)){
                unset($subArray);
            }
        }else if ($subArrayFilter){
            unset($subArray);
        }
    }
    public static function array_filter_recursive($array){
        if (empty($array)){
            return $array;
        }else{
            foreach ($array as $key => &$value){
                if (is_array($value)){
                    $value = Self::array_filter_recursive($value);
                }
            }
            return array_filter($array, function($value){
                return !is_array($value) || !empty($value);
            });
        }
    }
    public static function array_contains(&$array, &$subArray){
        foreach ($subArray as $key => &$value){
            if (isset($array[$key])){
                if (is_array($value) && is_array($array[$key])){
                    if (!self::array_contains($array[$key], $value)){
                        return false;
                    };
                }else if ($value !== $array[$key]){
                    return false;
                }
            }else{
                return false;
            }
        }
        return true;
    }
    /*
     *applies the callback to every key/value of the array (e.g. to translate all keys and values) *** warning: order of parameters inverse of array_map (hence map_array :-) ***
     */
    public static function map_array_recursive($arrayOrValue, $callback){
        if (is_array($arrayOrValue)){
            $newArray = [];
            foreach ($arrayOrValue as $key => $value){
                $newArray[$callback($key)] = self::map_array_recursive($value, $callback);
            }
            return $newArray;
        }else{
            return $callback($arrayOrValue);
        }
    }

   /*
    * A series of array_merge_recursive functions extending upon the native php array_merge8recursive
    *
    * This version does not overwrite numeric keys but ppends them. It does overwrite string keys whereas array_merge_recursive converts those to arrays rather than overwriting
    * (provided by Walf in the php manual)
    */

    public static function array_merge_recursive_simple() {
    
    	if (func_num_args() < 2) {
    		trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
    		return;
    	}
    	$arrays = func_get_args();
    	$merged = array();
    	while ($arrays) {
    		$array = array_shift($arrays);
    		if (!is_array($array)) {
    			trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
    			return;
    		}
    		if (!$array)
    			continue;
    			foreach ($array as $key => $value)
    				if (is_string($key))
    					if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
    						$merged[$key] = call_user_func(__METHOD__, $merged[$key], $value);
    						else
    							$merged[$key] = $value;
    							else
    								$merged[] = $value;
    	}
    	return $merged;
    }
    /*
     * keys need to be the sequence 0,1, ...
     */
    public static function isNumeric($paArray){
        if (!is_array($paArray)){
            return false;
        }else{
            $i = 0;
            foreach($paArray as $key => $value){
                if ($key !== $i){
                    return false;
                }
                return true;
            }
        }
    }

   /*
    * This version does overwrite both numeric keys and string keys whereas array_merge_recursive converts those to arrays rather than overwriting
    * (provided by Brian in the php manual). Implementation for two arrays arguments only
    */
    public static function array_merge_recursive_replace($paArray1, $paArray2, $replaceNumeric = true, $subOnly = false, $replace = '~replace', $delete = '~delete'){
    	if (!(is_array($paArray1) or is_object($paArray1))){
    	    //return empty($paArray2) ? $paArray1 : (is_array($paArray2) ? Self::bypass_recursive($paArray2, $replace) : $paArray2);
    	    return is_array($paArray2) ? (empty($paArray2) ? $paArray1 : Self::bypass_recursive($paArray2, $replace)) : $paArray2;
    	}
        if (!(is_array($paArray2) or is_object($paArray2))) {
        	return $paArray2; 
        }
        if (!$replaceNumeric  && Self::isNumeric($paArray1) && Self::isNumeric($paArray2)){
            return array_merge($paArray1, $paArray2);
        }
        foreach ($paArray2 as $sKey2 => $sValue2){
        	if(!empty($sValue2[$delete])){
        		unset($paArray1[$sKey2]);
        	}else if ($sKey2 === $replace){
            		return $sValue2;
            }else if($sValue2 === $delete){
                unset($paArray1[$sKey2]);
            }else{
                if (!$subOnly || isset($paArray1[$sKey2])){
                    $paArray1[$sKey2] = Self::array_merge_recursive_replace(@$paArray1[$sKey2], $sValue2, $replaceNumeric, false, $replace, $delete);
                }
            }
        }
        return $paArray1;
    }
    /*
     * this version concatenates numeric arrays instead of replacing
     */
    public static function bypass_recursive($paArray, $keyToBypass){
    	foreach ($paArray as $key => $value){
    		if ($key === $keyToBypass){
    			return $value;
    		}else if (is_array($value)){
    			$paArray[$key] = Self::bypass_recursive($value, $keyToBypass);
    		}
    	}
    	return $paArray;
    }

    public static function array_diff_assoc_recursive($array1, $array2) {
        $difference=array();
        foreach($array1 as $key => $value) {
            if( is_array($value) ) {
                if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = self::array_diff_assoc_recursive($value, $array2[$key]);
                    if( !empty($new_diff) )
                        $difference[$key] = $new_diff;
                }
            } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }
    
    /**
    * @author Gajus Kuizinas <gk@anuary.com>
    * @version 1.0.0 (2013 03 19)
    */
    public static function array_diff_key_recursive (array $arr1, array $arr2) {
        $diff = array_diff_key($arr1, $arr2);
        $intersect = array_intersect_key($arr1, $arr2);
       
        foreach ($intersect as $k => $v) {
            if (is_array($arr1[$k]) && is_array($arr2[$k])) {
                $d = self::array_diff_key_recursive($arr1[$k], $arr2[$k]);
               
                if ($d) {
                    $diff[$k] = $d;
                }
            }
        }

        return $diff;
    }

    public static function array2D_Search_Strict($theArray, $theColumn, $theTarget){
        foreach ($theArray as $key => $row){
            if ($row[$theColumn] === $theTarget){
                return $key;
            }
        }
        return false;
    }
    public static function missing_array_keys($keys, $theArray){
        $missing = [];
        foreach ($keys as $key){
            if (!array_key_exists($key, $theArray)){
                $missing[] = $key;
            }
        }
        return $missing;
    }
    public static function present_array_keys($keys, $theArray){
        $missing = [];
        foreach ($keys as $key){
            if (array_key_exists($key, $theArray)){
                $missing[] = $key;
            }
        }
        return $missing;
    }
    public static function reduce($theArray, $n){// returns an array with $n time less values, which are the averages over the $n values
        $i = 1; $avg = 0; $reduced = [];
        foreach($theArray as $value){
            $avg += $value;
            $i += 1;
            if ($i === $n){
                $reduced[] = intval($avg / $n);
                $i = 0;
                $avg = 0;
            }
        }
        return $reduced;
    }
    public static function removeAccents($string){
        $transliterator = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
        return $transliterator->transliterate($string);
    }
    public static function replace($comparisonOperator, $arrayToSearch, $searchProperty, $searchValue, $returnProperty, &$cache, $ignoreCase, $ignoreAccent){
        if (empty($cache[$searchValue])){
            foreach($arrayToSearch as $row){
                $targetValue = $row[$searchProperty]; $sourceValue = $searchValue;
                if ($ignoreAccent){
                    $transliterator = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
                    $targetValue = $transliterator->transliterate($targetValue);
                    $sourceValue = $transliterator->transliterate($sourceValue);
                }
                if ($ignoreCase){
                    $targetValue = strtolower($targetValue);
                    $sourceValue = strtolower($sourceValue);
                }
                if($comparisonOperator === 'find' ? (string)$targetValue === (string)$sourceValue : strpos($targetValue, $sourceValue) !== false){
                    $cache[$searchValue] = $row[$returnProperty];
                    break;
                }
            }
            if (empty($cache[$searchValue])){
                $cache[$searchValue] = $searchValue;
            }
        }
        return $cache[$searchValue];
    }
    public static function findReplace($arrayToSearch, $searchProperty, $searchValue, $returnProperty, &$cache, $ignoreCase = false, $ignoreAccent = false){
        return self::replace('find', $arrayToSearch, $searchProperty, $searchValue, $returnProperty, $cache, $ignoreCase, $ignoreAccent);
    }
    public static function includesReplace($arrayToSearch, $searchProperty, $searchValue, $returnProperty, &$cache, $ignoreCase = false, $ignoreAccent = false){
        return self::replace('includes', $arrayToSearch, $searchProperty, $searchValue, $returnProperty, $cache, $ignoreCase, $ignoreAccent);
    }
    
   /*
    * Transforms the associative array $data into an array, ready to be consumed by dojo/store. 
    *   [$key => $value, ...] => [[$idProperty => $i, $keyProperty => $key, $valueProperty => $value],...]
    */
    public static function toStoreData($data, $keyProperty, $valueProperty, $idProperty='id'){
        $result = [];
        $i = 1;
        foreach ($data as $key => $value){
            $result[] = [$idProperty => $i, $keyProperty => $key, $valueProperty => $value];
            $i++;
        }
        return $result;
    }

    public static function toNumeric($associativeArray, $keyName, $asString = false){
        if (is_array($associativeArray)){
            $result = [];
            foreach ($associativeArray as $keyValue => $value){
                if ($asString){
                	$keyValue = (string)$keyValue;
                }
            	if (is_array($value)){
                    $result[] = array_merge([$keyName => $keyValue], $value);
                }else{
                    $result[] = [$keyName => $keyValue];
                }
            }
            return $result;
        }else{
            return $associativeArray;
        }
    }
    
    public static function toAssociative($numericArray, $keyName){
        if (is_array($numericArray)){
            $result = [];
        	foreach ($numericArray as $value){
                $keyValue = $value[$keyName];
                unset($value[$keyName]);
                $result[$keyValue] = $value;
            }
            return $result;
        }else{
            return $numericArray;
        }
    }

    public static function toAssociativeGrouped($numericArray, $keyName, $scalarIfSingle = false){
    	if (is_array($numericArray)){
    		$result = [];
    		foreach ($numericArray as $value){
    			$keyValue = self::extractItem($keyName, $value, 'absent');
    			if ($keyValue !== 'absent'){
    			     $result[$keyValue][] = ($scalarIfSingle && count($value) === 1) ? array_pop($value) : $value;
    			}
    		}
    		return $result;
    	}else{
    		return $numericArray;
    	}
    }
   /*
    * Transforms array [value1, value2, , ...], into array [['id' => value1, 'name' => $translator(value1), ...], ready to be consumed by dojo/store (Intended for tukos/storeSelect)
    */
    public static function idsNamesStore($idsStore, $translator = null, $options = null){
        if ($options && count($options) <= 4){
            var_dump($idsStore);
        }
        list($allowEmpty, $translationMode, $prependKeyToName, $useKeyAsId, $hasTooltip) = empty($options) ? [true, 'ucfirst', false, false, false] : $options;
        $theStore = $allowEmpty ? [['id' => '', 'name' => '']] : [];
        foreach ($idsStore as $key => $value){
            if (is_array($value)){
                $theStore[] = array_merge(['id' => $key, 'name' => $translator ? $translator($key, $translationMode) : $key], $hasTooltip ? ['tooltip' => $translator($key . 'tooltip', 'none')] : [], $value);// at least used for sports::levelOptions1, etc.
            }else{
                 $theStore[] = array_merge(['id' => $useKeyAsId ? $key : $value, 'name' => ($prependKeyToName ? $key . ': ' : '') . ($translator ? $translator($value, $translationMode) : $value)], $hasTooltip ? ['tooltip' => $value . 'tooltip'] : []);
            }
        }
        return $theStore;
    }
    public static function translations($ids, $translator = null, $translationMode = 'ucfirst'){
        $result = [];
        foreach ($ids as $id){
            $result[$id] = $translator ? $translator($id, $translationMode) : $id;
        }
        return $result;
    }

   /*
    * Returns an array [['id' => year1, 'name' => year1, ...] ready to be consumed by dojo/store, (Intended for tukos/storeSelect)
    */
    public static function yearsStore($atts = []){
        $defAtts = ['fromYear' => date('Y'),
                      'toYear' => 1950,
                     'prepend' => [],
                      'append' => []];
        $atts = array_merge($defAtts, $atts);
        $theStore = $atts['prepend'];
        $year     = $atts['fromYear'];
        if ($atts['fromYear'] > $atts['toYear']){
            while ($year >= $atts['toYear']){
                $theStore[] = ['id' => $year, 'name' => $year];
                $year += -1;
            }
        }else{
            while ($year <= $atts['toYear']){
                $theStore[] = ['id' => $year, 'name' => $year];
                $year += 1;
            }
        }
        $theStore = array_merge($theStore, $atts['append']);
        return $theStore;
    }    
    /*
     * Appends to $descendants the $idProperties for $store rows which are descendants of $fromId, via their $parentIdProperty. excluding $fromId.
     * A row can have many children. The results are returned "depth-first".
     */
    public static function getDescendants($fromId, $store, &$descendants, $idProperty, $parentIdProperty){
        foreach ($store as $row){
            if($row[$parentIdProperty] === $fromId && $row[$idProperty] !== $row[$parentIdProperty]){
                $descendants[] = $row[$idProperty];
                Self::getDescendants($row['id'], $store, $descendants, $idProperty, $parentIdProperty);
            } 
        }
    }
    /*
     * Appends to $ancestors the $idProperties for $store rows which are ancestors of $fromId, via their $parentIdProperty. including $fromId (at $ancestors[0])
     * Each row can have only one parent
     */
    public static function getAncestors($fromId, $store, &$ancestors, $idProperty, $parentIdProperty){
        foreach ($store as $row){
            if($row[$idProperty] === $fromId){
                $ancestors[] = $row[$idProperty];
                if ($row[$parentIdProperty] === $row[$idProperty] || $row[$parentIdProperty] <= 0/* || $row[$parentIdProperty] == null*/){//reached the root
                    break;
                }else{
                    Self::getAncestors($row[$parentIdProperty], $store, $ancestors, $idProperty, $parentIdProperty);
                }
            }
        }
    }

    public static function format($value, $format, $translator = null, $idsNamesStore = null, $storeCache = null){
        if (isset($value)){
            switch($format){
                case 'numberUnit':
                        $values = json_decode($value, true);
                        $value = $values[0] . ' ' . (empty($translator) ? $values[1] : $translator($values[1])) . ($values[0] > 1 ? 's' : '');
                        break;
                case 'minutesToHHMM':
                    $minutes = round($value);
                        return self::pad(intval($minutes / 60), 2) . ':' .  self::pad($minutes % 60, 2);
                    break;
                case 'inlineImage':
                        if (!empty($value)){
                            $type = pathinfo($value, PATHINFO_EXTENSION);
                            $data = file_get_contents($value);
                            $value = '<img src="data:image/' . $type . ';base64, ' . base64_encode($data) . '">';
                        }
                        break;
                case 'currency':
					$loc = localeconv();      
                	$value = number_format(floatval($value), 2, $loc['decimal_point'], self::utf8($loc['thousands_sep'])) . ' &euro;';
					break;
                case 'percent':
					$loc = localeconv();      
                	$value = empty($value) ? '' : number_format($value * 100, 2, $loc['decimal_point'], self::utf8($loc['thousands_sep'])) . ' %';
					break;
                case 'StoreSelect':
                    $value = self::findReplace($idsNamesStore, 'id', $value, 'name', $storeCache);
                    break;
                default:
                    if (!empty($translator)){
                        $value = $translator($value);
                    }
                    break;
            }
        }
        return $value;
    }

    public static function utf8($value){
        switch ($encoding = mb_detect_encoding($value, null, true)){
            case 'UTF-8' : 
            case false   : return $value; break;
            default:
                return iconv($encoding, 'UTF-8', $value);
        }
        //return Tfk::isWindows() ? iconv('Windows-1252','UTF-8', $value) : utf8_encode($value);
    }
    public static function adjustSourceCols(&$cols, &$removedComputedCols, &$addedSourceCols, $computedColsDescription){
        $addedSourceCols = [];
        $removedComputedCols = array_intersect($cols, array_keys($computedColsDescription));
        if (!empty($removedComputedCols)){
            $cols = array_diff($cols, $removedComputedCols);
            foreach ($removedComputedCols as $col){
                $addedSourceCols = array_unique(array_merge($addedSourceCols, $computedColsDescription[$col]));
            }
            $addedSourceCols = array_diff($addedSourceCols, $cols);
            $cols = array_merge($cols, $addedSourceCols);
        }
    }
    public static function sentence($words, $translator = null){
        if (isset($translator)){
            return implode(' ', array_map($translator, $words));
        }else{
            return implode(' ', $words);
        }
    }
    public static function substitute($template, $map){
    	return str_replace(array_map(function($key){return '${' . $key . '}';}, array_keys($map)), array_values($map), $template);
    }
    public static function escapeSQuote($string){
        return str_replace("'", "\\\\'", $string);
    }
    public static function hexToRgb($hexstr) {
    	$int = hexdec($hexstr);
    	return array("red" => 0xFF & ($int >> 0x10), "green" => 0xFF & ($int >> 0x8), "blue" => 0xFF & $int);
    }
    public static function is_integer($value){
    	return ctype_digit(strval($value));
    }
    public static function identityMapping($values){
    	foreach ($values as $value){
    		$mapping[$value] = $value;
    	}
    	return $mapping;
    }
    public static function pad($number, $size){
       $result = strval(intval($number));
        while (strlen($result)< $size){
            $result = '0' . $result;
        }
        return $result;
    }
    public static function timer(){
        return hrtime(true);
    }
    public static function duration(&$timer){
        $prev = $timer;
        return (($timer = hrtime(true)) - $prev) / 1000000000;
    }
    public static function objToEdit($items, $colsDescription){
        if (isset($items)){
            foreach ($colsDescription as $col => $description){
                if (!empty($description['objToStoreEdit'])){
                    foreach($description['objToStoreEdit'] as $func => $params){
                        foreach($items as &$item){
                            if (isset($item[$col])){
                                if (isset($params['class'])){
                                    
                                }
                                $item[$col] = isset($params['class']) ? call_user_func_array([$params['class'], $func], [$item[$col]]) : $func($item[$col]);
                            }
                        }
                    }
                }
            }
        }
        return $items;
    }
    public static function editToObj($items, $colsDescription){
        if (isset($items)){
            foreach ($colsDescription as $col => $description){
                if (!empty($description['storeEditToObj'])){
                    foreach($description['storeEditToObj'] as $func => $params){
                        foreach($items as &$item){
                            if (isset($item[$col])){
                                $item[$col] = call_user_func_array([$params['class'], $func], [$item[$col]]);
                            }
                        }
                    }
                }
            }
        }
        return $items;
    }
    public static function random_password( $length = 8 ) {// found at: https://hughlashbrooke.com/2012/04/23/simple-way-to-generate-a-random-password-in-php/
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
}
?>
