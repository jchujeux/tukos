<?php
namespace TukosLib\Objects\Sports;

use TukosLib\TukosFramework as Tfk;

class Sports {

    public static $intensityOptions = ['verylow', 'low', 'medium', 'high', 'veryhigh'];
    public static $stressOptions   = ['verylow', 'low', 'medium', 'high', 'veryhigh'];
    public static $sportOptions = ['bicycle', 'running', 'swimming', 'climbing', 'elliptic', 'bodybuilding', 'rest', 'other'];
    public static $stagetypeOptions = ['warmup', 'mainactivity', 'warmdown', 'various'];
    public static $level1Options = ['muscular', 'aerobic', 'proprioception', 'stretching', 'specific'],
    $level2Options = [
    		'calf' => ['level1' => 'muscular'], 'thigh' => ['level1' => 'muscular'], 'pelvis' => ['level1' => 'muscular'], 'torso' => ['level1' => 'muscular'], 'triceps' => ['level1' => 'muscular'], 'biceps' => ['level1' => 'muscular'],
    		'pectoral' => ['level1' => 'muscular'], 'back' => ['level1' => 'muscular'], 'globalMS' => ['level1' => 'muscular'], 'globalMI' => ['level1' => 'muscular'],	'coremuscle' => ['level1' => 'muscular'],
    		'loadedsport' => ['level1' => 'aerobic'], 'carriedsport' => ['level1' => 'aerobic'], 'knee' => ['level1' => 'proprioception'], 'ankle' => ['level1' => 'proprioception'], 'global' => ['level1' => 'proprioception'],
    		'uppermembers' => ['level1' => 'stretching'], 'lowermembers' => ['level1' => 'stretching'],
    		'running' => ['level1' => 'specific'], 'trail' => ['level1' => 'specific'], 'othersports' => ['level1' => 'specific'], 'physicalquality' => ['level1' => 'specific']
    ],
    $level3Options = [
    		'withmaterial' => ['level1' => ['muscular', 'aerobic', 'proprioception', 'specific']], 'withoutmaterial' => ['level1' => ['muscular', 'aerobic', 'proprioception', 'specific']],
    		'active' => ['level1' => 'stretching'], 'passive' => ['level1' => 'stretching']
    ];
    
    public static $intensityColorsMap = ['verylow' => 'Lime', 'low' => 'Cyan', 'medium' => 'Gold', 'high' => 'DarkOrange', 'veryhigh' => 'Red'];
    public static $colorNameToHex = ['Lime' => '00FF00', 'Cyan' => '00FFFF', 'Gold' => 'FFD700', 'DarkOrange' => 'FF8C00', 'Red' => 'FF0000'];
    public static $sportImagesMap = [
        'bicycle' =>'bicycleblank.png', 'running' =>'runningblank.png', 'swimming' =>'swimmingblank.png', 'climbing' =>  'climbingblank.png',  'elliptic' =>'elliptic.jpg', 'bodybuilding' =>'bodybuilding.png', 'rest' =>'sleeping.png',  'other' =>'othersport.png'
    ];
    public static $modeOptions = ['planned', 'performed'];
    public static $feelingOptions = ['1' => 'superfeeling', '2' => 'goodfeeling',  '3' => 'badfeeling', '4' => 'verybadfeeling'];
    public static $sensationsOptions = ['1' => 'supersensations', '2' => 'verygoodsensations',  '3' => 'goodsensations', '4' => 'aboveaveragesensations', '5' => 'averagesensations', '6' => 'belowaveragesensations',
        '7' => 'ratherbadsensations', '8' => 'badsensations', '9' => 'verybadsensations', '10' => 'extremelybadsensations'];
    public static $perceivedEffortOptions = ['1' => 'extremelyeasy', '2' => 'veryeasy',  '3' => 'easy', '4' => 'comfortable', '5' => 'rathercomfortable', '6' => 'slightlydifficult', '7' => 'ratherdifficult',
        '8' => 'difficult', '9' => 'verydifficult', '10' => 'extremelydifficult'];
    public static $moodOptions = ['1' => 'supermood', '2' => 'verygoodmood',  '3' => 'goodmood', '4' => 'aboveaveragemood', '5' => 'averagemood', '6' => 'belowaveragemood', '7' => 'ratherbadmood',
        '8' => 'badmood', '9' => 'verybadmood', '10' => 'extremelybadmood'];
}
?>
