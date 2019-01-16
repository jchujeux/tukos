<?php
namespace TukosLib\Utils;

class Cipher{/* insired from http://stackoverflow.com/questions/26756322/php-using-mcrypt-and-store-the-encrypted-in-mysql*/
    private static $iv_size;

    public static function encrypt($input, $secureKey){
        if (empty(self::$iv_size)){
            self::$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        }
        $iv = mcrypt_create_iv(self::$iv_size);
        return base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $secureKey, $input, MCRYPT_MODE_CBC, $iv));
    }

    public static function decrypt($input, $secureKey) {
        if (empty(self::$iv_size)){
            self::$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        }
        $input = base64_decode($input);
        $iv = substr($input, 0, self::$iv_size);
        $cipher = substr($input, self::$iv_size);
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $secureKey, $cipher, MCRYPT_MODE_CBC, $iv));
    }
}
?>    
