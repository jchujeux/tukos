<?php
$phpDir = dirname(__DIR__) . '/';
require $phpDir . 'TukosLib/Utils/Fuzzy.php';
require $phpDir . 'TukosLib/Utils/Utilities.php';

use TukosLib\Utils\Fuzzy as FZ;
use TukosLib\Utils\Utilities as Utl;

$timer = Utl::timer();
try {
    $fuzzyDomain = FZ::fuzzyDomainAbsolute([103, 126, 142, 159], 3);
    $fuzzyDomainRelative = FZ::fuzzyDomainRelative([103, 126, 142, 159], 0);
    foreach([101, 103, 141, 103, 142, 143, 141, 150, 151, 170, 190, 70] as $value){
        echo "value: $value\n";
        /*$fValue = FZ::fuzzyValueAbsolute($value, $fuzzyDomain);
        echo "value: $value\n";
        var_dump($fValue);*/
        $fValue = FZ::fuzzyValueRelative($value, $fuzzyDomainRelative);
        echo "\nRelative:\n";
        var_dump($fValue);
    }
    $duration = Utl::duration($timer);
    echo "duration: $duration";
} catch(Exception $e) {
    print $e->getMessage();
}