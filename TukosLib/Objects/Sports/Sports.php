<?php
namespace TukosLib\Objects\Sports;

use TukosLib\TukosFramework as Tfk;

class Sports {

    //public static $intensityOptions = ['verylow', 'low', 'medium', 'high', 'veryhigh'];
    public static $intensityOptions = ['1' => 'extremelylow', '2' => 'verylow',  '3' => 'low', '4' => 'ratherlow', '5' => 'medium', '6' => 'slightlyhigh', '7' => 'ratherhigh',
        '8' => 'high', '9' => 'veryhigh', '10' => 'extremelyhigh'];
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
    
    public static $intensityColorsMap = ['1' => 'LightYellow', '2' => 'Lime',  '3' => 'LightSkyBlue', '4' => 'CornflowerBlue', '5' => 'Gold', '6' => 'Orange', '7' => 'DarkOrange',  '8' => 'OrangeRed', '9' => 'Red', '10' => 'MediumVioletRed'];
    public static $colorNameToHex = ['LightYellow' => 'FFFE0', 'Lime' => '00FF00', 'LightSkyBlue' => '87CEFA', 'CornFlowerBlus' => 'FFD700', 'Gold' => 'FFD700', 'Orange' => 'FFA500', 'DarkOrange' => 'FF8C00', 'OrangeRed' => 'FF4500', 
        'Red' => 'FF0000', 'MediumVioletRed' => 'C71585'];
    public static $sportImagesMap = ['bicycle' =>'bicycleblank.png', 'running' =>'runningblank.png', 'swimming' =>'swimmingblank.png', 'climbing' =>  'climbingblank.png',  'elliptic' =>'elliptic.jpg', 'bodybuilding' =>'bodybuilding.png',
        'rest' =>'sleeping.png',  'other' =>'othersport.png'];
    public static $modeOptions = ['planned', 'performed'];
    public static $feelingOptions = ['1' => 'superfeeling', '2' => 'goodfeeling',  '3' => 'badfeeling', '4' => 'verybadfeeling'];
    public static $sensationsOptions = ['10' => 'supersensations', '9' => 'verygoodsensations',  '8' => 'goodsensations', '7' => 'aboveaveragesensations', '6' => 'averagesensations', '5' => 'belowaveragesensations',
        '4' => 'ratherbadsensations', '3' => 'badsensations', '2' => 'verybadsensations', '1' => 'extremelybadsensations'];
    public static $perceivedEffortOptions = ['1' => 'extremelyeasy', '2' => 'veryeasy',  '3' => 'easy', '4' => 'comfortable', '5' => 'rathercomfortable', '6' => 'slightlydifficult', '7' => 'ratherdifficult',
        '8' => 'difficult', '9' => 'verydifficult', '10' => 'extremelydifficult'];
    public static $moodOptions = ['10' => 'supermood', '9' => 'verygoodmood',  '8' => 'goodmood', '7' => 'aboveaveragemood', '6' => 'averagemood', '5' => 'belowaveragemood', '4' => 'ratherbadmood',
        '3' => 'badmood', '2' => 'verybadmood', '1' => 'extremelybadmood'];
    public static $sessionidOptions = ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'];
}
?>
