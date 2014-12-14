<?php namespace Sframe;
/**
 * Validator
 * 多种验证时，一旦碰到无效则停止验证，即判无效
 *
 * @package Validator
 * @author shukyyang@pplive.com
 * @version $Id: Validator.php 1279 2011-12-18 02:42:28Z shukyyang $
 */

class Validator
{
    /**
     * 进入集成式的验证处理
     * run('isEmail')
     * run(array('validator'=>'valueRange', 'options'=>array(1, 30)))
     * run(array(
     *     array('validator'=>'isEmail'),
     *     array('validator'=>'valueRange', 'options'=>array(1, 30), 'message'=>'xxxx')
     * ))
     * @param string|array $rule 如果为string时则为单个验证器
     * 数组形态时array('validator'=>'xxx', 'options'=>array(), 'message'=>'xxxx')
     * @return bool
     */
    public static function run($data, $rule, &$message = '')
    {
        // 参数组织
        if (is_string($rule)) {
            $rule = array(array('validator' => $rule));
        } elseif (is_array($rule) && !empty($rule['validator'])) {
            $rule = array($rule);
        }
        // 聚合验证处理
        $valid = true;
        foreach ($rule as $v) {
            if (!$valid) {
                break;
            }
            if (empty($v['validator']) || !method_exists(__CLASS__, $v['validator'])) {
                throw new SF_Exception('无效的验证方法');
            }
            // 验证调用
            $options = array('value' => $data);
            if (isset($v['options']) && is_array($v['options'])) {
                $options = array_merge($options, $v['options']);
            }
            $valid = call_user_func_array(array(__CLASS__, $v['validator']), $options);
            // 消息
            if (!$valid) {
                $message = (empty($v['message'])) ? '经验证无效' : $v['message'];
            }
        }
        return $valid;
    }

    /**
     * 邮箱是否有效
     */
    public static function isEmail($value)
    {
        return preg_match('/^\w+([.]\w+)*[@]\w+([.]\w+)*[.][a-zA-Z]{2,4}$/', $value);
    }

    /**
     * 是否是日期
     * 格式：yyyy-mm-dd or yyyy/mm/dd
     */
    public static function isDate($value)
    {
        if (strpos($value, '-') !== false) {
            $p = '-';
        } elseif (strpos($value, '/') !== false) {
            $p = '\/';
        } else {
            return false;
        }
        if (preg_match('/^\d{4}' . $p . '\d{1,2}' . $p . '\d{1,2}$/', $value)) {
            $arr = explode($p, $value);
            if (count($arr) < 3)
                return false;
            list($year, $month, $day) = $arr;
            return checkdate($month, $day, $year);
        } else {
            return false;
        }
    }

    /**
     * 是否是Url
     */
    public static function isUrl($value)
    {
        return preg_match('/http:\/\/[a-z0-9\-]+\.[a-z]+\/?.*/i', $value);
    }

    /**
     * 自定义正则，是否匹配
     */
    public static function isMatch($value, $regxp)
    {
        return preg_match($regxp, $value);
    }

    /**
     * 值是否相等
     */
    public static function isEqual($value, $cmp)
    {
        return $value == $cmp && strlen($value) == strlen($cmp);
    }

    /**
     * 值是否完全同
     */
    public static function isSame($value, $cmp)
    {
        return $value === $cmp;
    }
    
    /**
     * 值是否存在数组中
     */
    public static function isIn($value, $array)
    {
        return in_array($value, $array);
    }

    /**
     * 字串长度
     * @param int $type 如果是0则按1个中文按1个字符计算，如果1按1个中文3个字符计算
     */
    public static function lengthRange($value, $min, $max, $type = 0)
    {
        $len = mb_strlen($value, 'UTF-8');
        return $len >= $min && $len <= $max;
    }

    /**
     * 值范围
     */
    public static function valueRange($value, $min, $max)
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * 不为空
     */
    public static function notEmpty($value)
    {
        return !empty($value);
    }
    
    /**
     * 不为null
     */
    public static function notNull($value)
    {
        return $value !== null;
    }


    /**
     * 递归验证不为空
     */
    public static function recurNotEmpty($value)
    {
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                if (false == self::recurNotEmpty($v)) {
                    return 0;
                }
            }
        } else {
            return self::notEmpty($value);
        }
        return 1;
    }

    /**
     * 是否指定类型
     */
    public static function isType($value, $type)
    {
        return gettype($value) == $type;
    }

}