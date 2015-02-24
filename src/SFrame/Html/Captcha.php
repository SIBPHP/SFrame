<?php namespace SFrame\Html;

class Captcha
{

    const SESSION_KEY = 'vcode';

    protected $_code = '';

    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * 验证
     * @param string $code
     */
    public static function check($code)
    {
        return ($code && $_SESSION[self::SESSION_KEY] == $code);
    }

    /**
     * 获取验证码字符
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * 显示验证码
     */
    public function render()
    {
        header('Pragma: no-cache');
        header('Cache-Control: no-cache');
        header('Content-Type: image/jpeg');
        $image = imagecreatetruecolor(79, 25);
        $color_Background = imagecolorallocate($image, mt_rand(190, 255), mt_rand(190, 255), mt_rand(190, 255));
        imagefill($image, 0, 0, $color_Background);
        $key = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 't', 'v', 'w', 'x', 'y');
        $string = null;
        $char_X = mt_rand(1, 20);
        $char_Y = 0;
        for ($i = 0; $i < 4; $i++) {
            $char_Y = mt_rand(1, 6);
            $char = $key [mt_rand(0, 49)];
            $string .= $char;
            $color_Char = imagecolorallocate($image, mt_rand(20, 190), mt_rand(20, 190), mt_rand(20, 190));
            imagechar($image, 5, $char_X, $char_Y, $char, $color_Char);
            $char_X = $char_X + mt_rand(8, 20);
        }
        $this->_code = $string;
        $_SESSION[self::SESSION_KEY] = $string;
        imagegif($image);
        imagedestroy($image);
    }

}
