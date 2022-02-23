<?php
namespace TukosLib\Utils;
use TukosLib\TukosFramework as Tfk;

class HtmlUtilities{
    private static $domDocument = null;
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
    function cut($text, $max_length, $ellipsis = ' ...'){/* found at: https://stackoverflow.com/questions/2398725/using-php-substr-and-strip-tags-while-retaining-formatting-and-without-break*/
        if (strlen($text) <= $max_length || strlen(($stripped_text = strip_tags($text))) <= $max_length){
            return $text;
        }
        $tags   = array();
        $result = "";     
        $is_open   = false;
        $grab_open = false;
        $is_close  = false;
        $in_double_quotes = false;
        $in_single_quotes = false;
        $tag = "";        
        $i = 0;
        $stripped = 0;        
        while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length){
            $symbol  = $text{$i};
            $result .= $symbol;
            switch ($symbol){
                case '<':
                    $is_open   = true;
                    $grab_open = true;
                    break;
                case '"':
                    if ($in_double_quotes){
                        $in_double_quotes = false;
                    }else{
                        $in_double_quotes = true;
                    }
                    break;
                case "'":
                    if ($in_single_quotes){
                        $in_single_quotes = false;
                    }else{
                        $in_single_quotes = true;
                    }
                    break;
                case '/':
                    if ($is_open && !$in_double_quotes && !$in_single_quotes){
                        $is_close  = true;
                        $is_open   = false;
                        $grab_open = false;
                    }
                    break;
                case ' ':
                    if ($is_open){
                        $grab_open = false;
                    }else{
                        $stripped++;
                    }
                    break;
                case '>':
                    if ($is_open) {
                        $is_open   = false;
                        $grab_open = false;
                        array_push($tags, $tag);
                        $tag = "";
                    }else if ($is_close){
                        $is_close = false;
                        array_pop($tags);
                        $tag = "";
                    }
                    break;
                default:
                    if ($grab_open || $is_close){
                        $tag .= $symbol;
                    }
                    if (!$is_open && !$is_close){
                        $stripped++;
                    }
            }
            $i++;
        }
        $result .= $ellipsis;
        while ($tags){
            $result .= "</".array_pop($tags).">";
        }
        return $result;
    }
    public static function urlStyle(){
        return "text-decoration:underline; color:blue; cursor:pointer;";
    }
    public static function getDomDocument(){
        if (is_null(self::$domDocument)){
            self::$domDocument = new \DOMDocument();
        }
        return self::$domDocument;
    }
    public static function imageUrl($imageTag){
        $doc = self::getDomDocument();
        $doc->loadHTML("<html><body>$imageTag</body></html>");
        return $doc->getElementsByTagName('img')[0]->getAttribute('src');
    }
}
?>
