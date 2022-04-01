<?php
namespace TukosLib\Utils;

use Defuse\Crypto\Crypto;

class Cipher{

    public static function encrypt($input, $secureKey, $deterministic = false){
        return $deterministic ? openssl_encrypt($input, 'aes-128-ecb', hex2bin($secureKey)) : Crypto::encryptWithPassword($input, $secureKey);
    }

    public static function decrypt($input, $secureKey, $deterministic = false) {
        return $deterministic ? openssl_decrypt($input, 'aes-128-ecb', hex2bin($secureKey)) : Crypto::decryptWithPassword($input, $secureKey);
    }
}
?>    
