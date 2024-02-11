<?php
$phpDir = dirname(__DIR__) . '/';
require $phpDir . 'TukosLib/Utils/Fuzzy.php';
require $phpDir . 'TukosLib/Utils/Utilities.php';

use TukosLib\Utils\Fuzzy as FZ;
use TukosLib\Utils\Utilities as Utl;

$timer = Utl::timer();
try {
    $fuzzyDomain = FZ::absoluteFuzzyDomain([103, 126, 142, 159], 3);
    $fuzzyDomainRelative = FZ::relativeFuzzyDomain([130, 142, 152, 158], 0.2);
    foreach([160] as $value){
        echo "value: $value\n";
        $fValue = FZ::absoluteFuzzyValue($value, $fuzzyDomain);
        echo "value: $value\n";
        var_dump($fValue);
        $fValue = FZ::relativeFuzzyValue($value, $fuzzyDomainRelative);
        echo "\nRelative:\n";
        var_dump($fValue);
    }
    $duration = Utl::duration($timer);
    echo "duration: $duration";
} catch(Exception $e) {
    print $e->getMessage();
}