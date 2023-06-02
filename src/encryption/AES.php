<?php

namespace Klwlbj\Icbc\encryption;

class AES
{
    /**
     * @param string $plaintext
     * @param string|null $key
     * @return string
     */
    public static function AesEncrypt(string $plaintext, string $key = null): string
    {
        $plaintext = trim($plaintext);
        if ($plaintext == '') {
            return '';
        }

        $cipher = 'AES-128-CBC';//加密算法和模式
        $ivlen  = openssl_cipher_iv_length($cipher);//获取加密块大小

        //PKCS5Padding
        $padding = $ivlen - strlen($plaintext) % $ivlen;
        // 添加Padding
        $plaintext .= str_repeat(chr($padding), $padding);

        $key = self::substr($key, 0, openssl_cipher_iv_length($cipher));
        $iv  = str_repeat("\0", $ivlen);

        /* Encrypt data */
        $encrypted = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($encrypted);
    }

    private static function substr($string, int $start, int $length): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length, '8bit');
        }
        return substr($string, $start, $length);
    }

    /**
     * @param string $encrypted
     * @param string|null $key
     * @return string
     */
    public static function AesDecrypt(string $encrypted, string $key = null):string
    {
        if ($encrypted == '') {
            return '';
        }
        $ciphertext_dec = base64_decode($encrypted);

        $cipher = 'AES-128-CBC';//加密算法和模式
        $ivlen = openssl_cipher_iv_length($cipher);//获取加密块大小

        $key = self::substr($key, 0, openssl_cipher_iv_length($cipher));
        $iv = str_repeat("\0", $ivlen);

        /* Initialize encryption module for decryption */
        $decrypted = openssl_decrypt($ciphertext_dec, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        return mb_trim($decrypted, "\0");
    }
}

//echo AES::AesEncrypt("123","abc");
