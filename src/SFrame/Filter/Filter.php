<?php namespace Sframe;
/**
 * 过虑
 *
 * @package Filter
 * @author shukyyang
 * @version $Id$
 */

/**
 * @TODO 使用PHP自带的Filter
 * http://www.w3school.com.cn/php/php_filter.asp
 */

class Filter
{
    /**
     * 聚合验证处理
     * 形式：
     * run('xxx', 'stringToLower')
     * run('xxx', array(
     *     'stringToLower',
     *     array('filter'=>'deQuote', 'options'=>array(1))
     * ))
     * @param mixed value
     */
    public static function run($value, $rule)
    {
        is_array($rule) || $rule = array($rule);
        foreach ($rule as $f) {
            is_string($f) && $f = array('filter' => $f);
            if (empty($f['filter']) || !method_exists(__CLASS__, $f['filter'])) {
                throw new SF_Exception('无效的过虑方法' . $f['filter']);
            }
            $options = array('value' => $value);
            if (isset($f['options']) && is_array($f['options'])) {
                $options = array_merge($options, $f['options']);
            }
            $value = call_user_func_array(array(__CLASS__, $f['filter']), $options);
        }
        return $value;
    }

    /**
     * 小写
     */
    public static function stringToLower($value)
    {
        return strtolower((string) $value);
    }

    /**
     * 大写
     */
    public static function stringToUpper($value)
    {
        return strtoupper((string)$value);
    }

    /**
     * trim
     */
    public static function stringTrim($value, $char = ' ')
    {
        return trim($value, $char);
    }
    
    /**
     * 数组递归trim
     */
    public static function recurTrim($value, $char = ' ')
    {
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                $value[$k] = self::recurTrim($v, $char);
            }
        } else {
            $value = self::stringTrim($value, $char);
        }
        return $value;
    }

    /**
     * 过虑引号
     */
    public static function deQuote($value, $q = 0)
    {
        if ($q == 0 || $q == 1) {
            $value = str_replace('\'', '', $value);
        }
        if ($q == 0 || $q == 2) {
            $value = str_replace('\"', '', $value);
        }
        return $value;
    }

    /**
     * 递归过滤引号
     */
    public static function recurDeQuote($value, $q = 0)
    {
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                $value[$k] = self::recurDeQuote($v, $q);
            }
        } else {
            return self::deQuote($value, $q);
        }
        return $value;
    }
    
    /**
     * stripslash
     */
    public static function stripslashes($value)
    {
        return stripslashes($value);
    }

    /**
     * basename
     */
    public static function basename($value)
    {
        return basename((string)$value);
    }
    
    /**
     * rename
     */
    public static function callback($value, $callback)
    {
        return $callback($value);
    }

    /**
     * int
     */
    public static function int($value)
    {
        return (int)$value;
    }
    
    /**
     * 数组递归int
     */
    public static function recurInt($value)
    {
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                $value[$k] = self::recurInt($v);
            }
        } else {
            $value = self::int($value);
        }
        return $value;
    }

    /**
     * float
     */
    public static function float($value, $decimals = 2)
    {
        //$p = pow(10, $decimals);
        //$value = floor($value * $p) / $p;
        return number_format($value, $decimals, '.', '');
    }
    
    /**
     * 数组递归float
     */
    public static function recurFloat($value, $decimals = 2)
    {
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                $value[$k] = self::recurFloat($v, $decimals);
            }
        } else {
            $value = self::float($value, $decimals);
        }
        return $value;
    }

    /**
     * 转义特殊符号
     */
    public static function htmlEntity($value)
    {
        return htmlentities($value, ENT_QUOTES);
    }
    
    /**
     * html转换
     */
    public static function htmlchars($value)
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }
    
    /**
     * 空格替换成&nbsp;
     */
    public static function addspace($value)
    {
        return str_replace(' ', '&nbsp;', $value);
    }

    /**
     * 特殊字符安全过滤
     */
    public static function secure($value)
    {
        return str_replace(array('"', '\'', '%', '`', '\\', '/', '<', '>', '?'), '', $value);
    }

    public static function recurSecure($value)
    {
        if (is_array($value)) {
            foreach ($value as $k=>$v) {
                $value[$k] = self::recurSecure($v);
            }
        } else {
            return self::secure($value);
        }
        return $value;
    }
}