<?php namespace SFrame\Encryption;

class Encrypter
{
    protected $_iv = ''; // 0202030605080709
    protected $_key = ''; // 15B9FDAEDA40F86BF71C73292546924A294FC8BA31B6E9EA
    
    public function __construct($key, $iv)
    {
        $this->_key = $key;
        $this->_iv = $iv;
    }
    
    public function encode($input)
    {
        Base32::encode($this->des_encrypt($input));
    }
    
    public function decode($input)
    {
        return $this->des_decrypt(Base32::decode($input));
    }

    /**
     * 3DES加密算法
     *
     * @param string $input 需要加密的数据
     * @param string $key 密钥
     * @param string $iv 偏移向量
     * @return string
     */
    public function encrypt($input)
    {
        $key = pack('H48', $this->_key);
        $iv = pack('H16', $this->_iv);
        // PaddingPKCS7补位
        $srcdata = $input;
        $block_size = mcrypt_get_block_size('tripledes', 'ecb');
        $padding_char = $block_size - (strlen($input) % $block_size);
        $srcdata .= str_repeat(chr($padding_char), $padding_char);
        return mcrypt_encrypt(MCRYPT_3DES, $key, $srcdata, MCRYPT_MODE_CBC, $iv);
    }
    
    /**
     * 3DES解密算法
     *
     * @param string $input 需要解密的数据
     * @param string $key 密钥
     * @param string $iv 偏移向量
     * @return string
     */
    public function decrypt($input)
    {
        $key = pack('H48', $this->_key);
        $iv = pack('H16', $this->_iv);
        $result = mcrypt_decrypt(MCRYPT_3DES, $key, $input, MCRYPT_MODE_CBC, $iv);
        $end = ord(substr($result, - 1));
        $out = substr($result, 0, - $end);
        return $out;
    }
}
