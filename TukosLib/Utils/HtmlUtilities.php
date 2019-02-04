<?php
namespace TukosLib\Utils;
use TukosLib\TukosFramework as Tfk;

class HtmlUtilities{
    public static function page ($title, $content){
        return '<!DOCTYPE HTML><html><head><meta charset="utf-8"><title>' . $title . '</title></head><body>' . $content . '</body></html>';
    }
    public static function table($values, $excludedCols=[]){
        $html = '<table>';
        foreach ($values as $key => $row){
            $html .= '<tr><td>' . $key;
            if (!empty($row)){
                if (is_scalar($row)){
                    if (is_scalar($row)){
                        $html .= '<td>' . $row . '</td>';
                    }else{
                        $html .= '<td>' . self::table($row) . '</td>';
                    }
                }else{
                    foreach ($row as $col => $value){
                        if (!in_array($col, $excludedCols)){
                            if (is_scalar($value)){
                                $html .= '<td>' . $value . '</td>';
                            }else{
                                $html .= '<td>' . self::table($value) . '</td>';
                            }
                        }
                    }
                }
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
    
    public static function buildHtml($htmlElements){
        $html = '';
        if (is_array($htmlElements)){
            if (isset($htmlElements['tag'])){
                $html .= '<' . $htmlElements['tag'] . (isset($htmlElements['atts']) ?  ' ' .  $htmlElements['atts'] : '') . '>' . (isset($htmlElements['content']) ? self::buildHtml($htmlElements['content']) : '') . '</' . $htmlElements['tag'] . '>';
            }else{
                foreach ($htmlElements as $htmlElement){
                    $html .= self::buildHtml($htmlElement);
                }
            }
        }else{
            $html .= $htmlElements;
        }
        return $html;
    }
}
?>
