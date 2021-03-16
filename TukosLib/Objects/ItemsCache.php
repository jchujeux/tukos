<?php
namespace TukosLib\Objects;

class ItemsCache {

    private static $itemsCache = [];
    private static $idCache = [];
    private static $itemsCacheExtras = [];
    private static $idCacheExtras = [];

    protected static function whereId($where, $table){
        return (isset($where['id']) ? $where['id'] : json_encode([$where, $table]));
    }
    protected static function colsInCache($idOrWhereId){
        if (isset(self::$itemsCache[$idOrWhereId])){
            return array_keys(self::$itemsCache[$idOrWhereId]);
        }else if (empty(self::$idCache[$idOrWhereId])){
            return [];
        }else{
            return array_keys(self::$itemsCache[self::$idCache[$idOrWhereId]]);
        }
    }
    protected static function valuesInCache($idOrWhereId){
        if (isset(self::$itemsCache[$idOrWhereId])){
            return self::$itemsCache[$idOrWhereId];
        }else if (empty(self::$idCache[$idOrWhereId])){
            return [];
        }else{
            return self::$itemsCache[self::$idCache[$idOrWhereId]];
        }
    }
    public static function emptyCache(){
        self::$itemsCache = [];
    }
    public static function missingCols($atts){
        return array_diff($atts['cols'], self::colsInCache(self::whereId($atts['where'], $atts['table'])));
    }
    public static function insert($values){
        $whereId = $values['id'];
        self::$idCache[$whereId] = $whereId;
        return self::$itemsCache[$whereId] = $values;
    }
    public static function getOne($atts){
        $fCols = array_flip($atts['cols']);
        return array_intersect_key(array_replace($fCols, self::valuesInCache(self::whereId($atts['where'], $atts['table']))), $fCols);
    }
    public static function updateOne($values, $idOrWhereId){
        if (empty(self::$idCache[$idOrWhereId])){
            self::$idCache[$idOrWhereId] = $idOrWhereId;
        }else{
            self::$idCache[$idOrWhereId];
        }
        if (empty(self::$itemsCache[$idOrWhereId])){
            self::$itemsCache[$idOrWhereId] = $values;
        }else{
            self::$itemsCache[$idOrWhereId] = array_merge(self::$itemsCache[$idOrWhereId], $values);
        }
    }
    public static function mergeOne($values, $atts){
        $whereId = self::whereId($atts['where'], $atts['table']);
        self::updateOne($values, $whereId);
        return array_merge(array_intersect_key(self::$itemsCache[$whereId], array_flip($atts['cols'])), $values);
    }
    public static function cacheExtra($value, $name, $atts){
        $whereId = self::whereId($atts['where'], $atts['table']);
        if (empty(self::$idCacheExtras[$whereId])){
            self::$idCacheExtras[$whereId] = $whereId;
        }
        if (empty(self::$itemsCacheExtras[$whereId])){
            self::$itemsCacheExtras[$whereId] = [$name => $value];
        }else{
            self::$itemsCacheExtras[$whereId][$name] = $value;
        }
        return $whereId;
    }
    public static function getExtrafromCache($name, $idOrWhereId){
        if (isset(self::$itemsCacheExtras[$idOrWhereId])){
            $extra = self::$itemsCacheExtras[$idOrWhereId];
        }else if (empty(self::$idCacheExtras[$idOrWhereId])){
            return false;
        }else{
            $extra = self::$itemsCacheExtras[self::$idCacheExtras[$idOrWhereId]];
        }
        return isset($extra[$name]) ? $extra[$name] : false;
    }
}
?>
