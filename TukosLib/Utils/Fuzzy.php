<?php
namespace TukosLib\Utils;

class Fuzzy{

    public static function fuzzyDomain($thresholds, $uncertainty, $type = 'absolute'){
        $func = $type . 'FuzzyDomain';
        return ['type' => $type, 'domain' => self::$func($thresholds, $uncertainty)];
    }
    public static function lowestBound($fuzzyDomain){
        $func = $fuzzyDomain['type'] . 'LowestBound';
        return self::$func($fuzzyDomain['domain']);
    }
    public static function highestBound($fuzzyDomain){
        $func = $fuzzyDomain['type'] . 'HighestBound';
        return self::$func($fuzzyDomain['domain']);
    }
    public static function fuzzyValue($value, &$fuzzyDomain){
        $func = $fuzzyDomain['type'] . 'FuzzyValue';
        return self::$func($value, $fuzzyDomain['domain']);
    }

    public static function absoluteFuzzyDomain($thresholds, $uncertainty){
        return [0, $uncertainty, 1 / (2 * $uncertainty), $thresholds];
    }
    public static function absoluteLowestBound($fuzzyDomainAbsolute){
        return reset($fuzzyDomainAbsolute[3]) - $fuzzyDomainAbsolute[1];
    }
    public static function absoluteHighestBound($fuzzyDomainAbsolute){
        return end($fuzzyDomainAbsolute[3]) + $fuzzyDomainAbsolute[1];
    }
    public static function absoluteFuzzyValue($value, &$fuzzyDomainAbsolute){
        list(&$i, $uncertainty, $slope, $thresholds) = $fuzzyDomainAbsolute;
        while (true){
            if ($value <= ($thresholds[$i] - $uncertainty)){// the target key is this one or lower
                if ($i === 0){// we are to the left of the first threshold
                    return [0 => 1];
                }
                if ($value >= $thresholds[$i-1] + $uncertainty){// we are on the plateau
                    return [$i => 1];
                }else if(0 <= ($delta = $value - ($thresholds[$i - 1] - $uncertainty))){// we are on the left slope
                    return [$i - 1 => $x = $delta * $slope, $i => 1 - $x];
                }
                $i += -1; // target key is lower, continue
            }else{
                if (0 <= ($delta = $thresholds[$i] + $uncertainty - $value)){//we are on the right slope
                    return [$i => $x = $delta * $slope, $i+1 => 1 - $x];
                }else if (!isset($thresholds[$i+1])){//we are at the right of the last threshold
                    return [$i + 1 => 1];
                }
                $i += 1; // target key is higher, continue
            }
        }
    }    
    public static function relativeFuzzyDomain($thresholds, $uncertaintyRatio){
        $last = count($thresholds) - 1;
        if ($uncertaintyRatio == 0){
            $slopes = null;
            $rightBounds[0] = $thresholds[0];
            $leftBounds[1] = $thresholds[0];
            for ($i = 1; $i <= $last - 1; $i++){
                $rightBounds[$i] = $thresholds[$i];
                $leftBounds[$i+1] = $thresholds[$i];
            }
            $rightBounds[$last] = $thresholds[$last];
            $leftBounds[$last+1] = $thresholds[$last];
        }else{
            if ($last === 0){
                $uncertainty = abs($uncertaintyRatio * $thresholds[0]);
                $rightBounds[0] = $thresholds[0] - $uncertainty;
                $leftBounds[1] = $thresholds[0] + $uncertainty;
                $slopes[0] = 1 / (2 * $uncertainty);
            }else{
                for($i = 1; $i <= $last; $i++){
                    $uncertainty[$i] = $uncertaintyRatio * 0.5 * ($thresholds[$i] - $thresholds[$i-1]);
                }
                $rightBounds[0] = $thresholds[0] - $uncertainty[1];
                $leftBounds[1] = $thresholds[0] + $uncertainty[1];
                $slopes[0] = 1 / (2 * $uncertainty[1]);
                for ($i = 1; $i <= $last - 1; $i++){
                    $rightBounds[$i] = $thresholds[$i] - $uncertainty[$i];
                    $leftBounds[$i+1] = $thresholds[$i] + $uncertainty[$i+1];
                    $slopes[$i] = 1 / ($leftBounds[$i+1] - $rightBounds[$i]);
                }
                $rightBounds[$last] = $thresholds[$last] - $uncertainty[$last];
                $leftBounds[$last+1] = $thresholds[$last] + $uncertainty[$last];
                $slopes[$last]  = 1 / (2 * $uncertainty[$last]);
            }
        }
        return [0, $rightBounds, $leftBounds, $slopes, $thresholds];
    }
    public static function relativeLowestBound($fuzzyDomainRelative){
        return reset($fuzzyDomainRelative[1]);
    }
    public static function relativeHighestBound($fuzzyDomainRelative){
        return end($fuzzyDomainRelative[2]);
    }
    public static function relativeFuzzyValue($value, &$fuzzyDomainRelative){
        list(&$i, $rightBounds, $leftBounds, $slopes, $thresholds) = $fuzzyDomainRelative;
        while (true){
            if ($value <= ($rightBounds[$i])){// the target key is this one or lower
                if ($i === 0){// we are to the left of the first threshold
                    return [0 => 1];
                }
                if ($value >= $leftBounds[$i]){// we are on the plateau
                    return [$i => 1];
                }else if(0 <= ($delta = $value - $rightBounds[$i-1])){// we are on the left slope
                    return [$i - 1 => $x = $delta * $slopes[$i-1], $i => 1 - $x];
                }
                $i += -1; // target key is lower, continue
            }else{
                if (0 <= ($delta = $leftBounds[$i+1] - $value)){//we are on the right slope
                    return [$i => $x = $delta * $slopes[$i], $i+1 => 1 - $x];
                }else if (!isset($thresholds[$i+1])){//we are at the right of the last threshold
                    return [$i + 1 => 1];
                }
                $i += 1; // target key is higher, continue
            }
        }
    }
}
?>    
