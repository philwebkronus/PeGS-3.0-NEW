<?php
class Cipher 
{
    private $securekey, $iv;

    function __construct($textkey) 
    {
        // Uncomment if key hashing is needed.
        //$this->securekey = hash('sha256',$textkey,TRUE);
        $this->securekey = $textkey;
        $this->iv = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
    }

    function encryptS($input) 
    {
    	// $MCRYPT_RIJNDAEL_128   = 'MCRYPT_RIJNDAEL_128';
    	// $MCRYPT_SERPENT        = 'MCRYPT_SERPENT';
    	// $MCRYPT_BLOWFISH       = 'MCRYPT_BLOWFISH';

        //If you want to change the Cipher just change the first parameter in mcrypt_encrypt or mcrypt_decrypt function

        return base64_encode(mcrypt_encrypt(MCRYPT_SERPENT, $this->securekey, $input, MCRYPT_MODE_ECB, $this->iv));
    }

    function decryptS($input) 
    {
        return trim(mcrypt_decrypt(MCRYPT_SERPENT, $this->securekey, base64_decode($input), MCRYPT_MODE_ECB, $this->iv));
    }
    
    function encryptR($input) 
    {
    	// $MCRYPT_RIJNDAEL_128   = 'MCRYPT_RIJNDAEL_128';
    	// $MCRYPT_SERPENT        = 'MCRYPT_SERPENT';
    	// $MCRYPT_BLOWFISH       = 'MCRYPT_BLOWFISH';

        //If you want to change the Cipher just change the first parameter in mcrypt_encrypt or mcrypt_decrypt function

        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->securekey, $input, MCRYPT_MODE_ECB, $this->iv));
    }

    function decryptR($input) 
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->securekey, base64_decode($input), MCRYPT_MODE_ECB, $this->iv));
    }
    
    function encryptB($input) 
    {
    	// $MCRYPT_RIJNDAEL_128   = 'MCRYPT_RIJNDAEL_128';
    	// $MCRYPT_SERPENT        = 'MCRYPT_SERPENT';
    	// $MCRYPT_BLOWFISH       = 'MCRYPT_BLOWFISH';

        //If you want to change the Cipher just change the first parameter in mcrypt_encrypt or mcrypt_decrypt function

        return base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, $this->securekey, $input, MCRYPT_MODE_ECB, $this->iv));
    }

    function decryptB($input) 
    {
        return trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $this->securekey, base64_decode($input), MCRYPT_MODE_ECB, $this->iv));
    }
}

/**
Usage:

Encrypt

$name      = 'StringToEncrypt'
$enc       = new Cipher("yourSecretKey"); 
$new_name  = $enc->encrypt($name);


Decrypt

$string = 'StringToDecrypt';
$cipher = new Cipher("yourSecretKey");
$dec    =  $cipher->decrypt($string);
*/