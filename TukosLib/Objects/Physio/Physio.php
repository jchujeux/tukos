<?php
namespace TukosLib\Objects\Physio;

use TukosLib\Objects\Sports\Sports;

class Physio extends Sports {
    public static $painOptions = ['1' => 'Nopainincrease', '2' => 'Slightpainincrease', '3' => 'Significantpainincrease', '4' => 'Toomuchpainstop'];
    public static $sessionidOptions = ['1' => 'Inthemorning', '2' => 'Atmidday', '3' => 'Intheafternoon', '4' => 'Intheevening'];
}
?>
