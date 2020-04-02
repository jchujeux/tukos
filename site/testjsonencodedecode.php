<?php
$key = 'toto';
$translatorName='TukosLib';
$mode= "_to ";
$template = '#{' . implode('|', [$key, json_encode($mode), $translatorName]) . '}#';
$template = json_encode($template);
$pattern = "/[#]{([^#]*)}[#]/";
preg_match_all($pattern, $template, $matches);
$splitted = list($name, $mode, $translatorName) = explode('|', json_decode($template));
//$mode = json_decode($mode);
$mode= json_decode($mode);
echo 'done';
$pathArray = explode('\\', 'TukosLib\\Objects\\Sports\\Model');
array_splice($pathArray, 3, count($pathArray), [$pathArray[2]]);
$result =  implode('\\', $pathArray);
echo $result;
?>