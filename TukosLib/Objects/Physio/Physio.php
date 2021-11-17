<?php
namespace TukosLib\Objects\Physio;

use TukosLib\Objects\Sports\Sports;

class Physio extends Sports {
    public static $painOptions = ['1' => 'Nopainincrease', '2' => 'Slightpainincrease', '3' => 'Significantpainincrease', '4' => 'Toomuchpainstop'];
    public static $painColors = ['' => '', '1' => 'LIGHTGREEN', '2' => 'ORANGE', '3' => 'RED', '4' => 'RED'];
    public static $whenInTheDayOptions = ['1' => 'Inthemorning', '2' => 'Atmidday', '3' => 'Intheafternoon', '4' => 'Intheevening'];
    public static $sessionidOptions = ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'];
}
?>
