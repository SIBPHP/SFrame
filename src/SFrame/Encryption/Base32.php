<?php namespace SFrame\Encryption;

class Base32
{
    /**
     * BASE32编码
     * @param string $input 需要解码的字符串
     * @return string 解码后的字符串
     */
    public static function encode($input)
    {
        // Reference: http://www.ietf.org/rfc/rfc3548.txt
        $BASE32_ALPHABET = 'aBcDeFgHiJkLmNoPqRsTuVwXyZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;
        for ($i = 0, $j = strlen($input); $i < $j; $i++) {
            $v <<= 8;
            $v += ord($input [$i]);
            $vbits += 8;
            while ($vbits >= 5) {
                $vbits -= 5;
                $output .= $BASE32_ALPHABET [$v >> $vbits];
                $v &= ( (1 << $vbits) - 1);
            }
        }
        if ($vbits > 0) {
            $v <<= ( 5 - $vbits);
            $output .= $BASE32_ALPHABET [$v];
        }
        return $output;
    }

    /**
     * BASE32解码
     * @param string $input 需要解码的字符串
     * @return string 解码后的字符串
     */
    public static function decode($input)
    {
        $output = '';
        $v = 0;
        $vbits = 0;
        $input = strtolower($input);
        for ($i = 0, $j = strlen($input); $i < $j; $i++) {
            $v <<= 5;
            if ($input [$i] >= 'a' && $input [$i] <= 'z') {
                $v += ( ord($input [$i]) - 97);
            } elseif ($input [$i] >= '2' && $input [$i] <= '7') {
                $v += ( 24 + $input [$i]);
            }
            $vbits += 5;
            while ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr($v >> $vbits);
                $v &= ( (1 << $vbits) - 1);
            }
        }
        return $output;
    }

}
