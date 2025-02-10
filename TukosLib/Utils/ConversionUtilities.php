<?php
namespace TukosLib\Utils;

class ConversionUtilities{

    const earthRadius  = 6371E3; 
    
    public static function convert($toConvert, $coef, $offset = 0.0){
        $toConverted = function($toConvert) use ($offset, $coef){
            return $offset + $toConvert * $coef;
        };
        if (is_array($toConvert)){
            return array_map($toConverted, $toConvert);
        }else{
            return $toConverted($toConvert);
        }
    }
    public static function degreesToRadians($degrees){
        return self::convert($degrees, pi()/180);
    }
    public static function radiansToDegrees($radians){
        return self::convert($radians, 180/pi());
    }
    public static function latlngRadiansToMeters($fromLat, $fromLng, $toLat, $toLng){
        return self::earthRadius * sqrt(($toLat - $fromLat) ** 2 + (($toLng - $fromLng) * cos($fromLat)) ** 2);
    }
    
}
?>
