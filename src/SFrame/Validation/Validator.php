<?php namespace SFrame\Validation;

/**
 * Validator
 */
class Validator
{
    /**
     * Usage:
     * run('isEmail')
     * run(array('validator'=>'valueRange', 'options'=>array(1, 30)))
     * run(array(
     *     array('validator'=>'isEmail'),
     *     array('validator'=>'valueRange', 'options'=>array(1, 30), 'message'=>'xxxx')
     * ))
     * @param string|array $rule the rule options
     * Format: array('validator'=>'xxx', 'options'=>array(), 'message'=>'xxxx')
     * @return bool
     */
    public static function run($data, $rule, &$message = '')
    {
        // set rule into array
        if (is_string($rule)) {
            $rule = array(array('validator' => $rule));
        } elseif (is_array($rule) && !empty($rule['validator'])) {
            $rule = array($rule);
        }
        $valid = true;
        foreach ($rule as $v) {
            if (!$valid) {
                break;
            }
            if (empty($v['validator']) || !method_exists(__CLASS__, $v['validator'])) {
                throw new \RuntimeException('The validator not exists: '. $v['validator']);
            }
            // if validator needs parameters
            $options = array('value' => $data);
            if (isset($v['options']) && is_array($v['options'])) {
                $options = array_merge($options, $v['options']);
            }
            $valid = call_user_func_array(array(__CLASS__, $v['validator']), $options);
            // Invalid
            if (!$valid) {
                $message = (empty($v['message'])) ? 'Invalid data' : $v['message'];
            }
        }
        return $valid;
    }

    /**
     * Email
     */
    public static function isEmail($value)
    {
        $result = filter_var($value, FILTER_VALIDATE_EMAIL);
        return $result == false ? false : true;
    }
    
    /**
     * Date
     * Format: yyyy-mm-dd or yyyy/mm/dd
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
            if (count($arr) < 3) {
                return false;
            }
            list($year, $month, $day) = $arr;
            return checkdate($month, $day, $year);
        } else {
            return false;
        }
    }

    /**
     * URL
     */
    public static function isUrl($value)
    {
        $result = filter_var($value, FILTER_VALIDATE_URL);
        return $result == false ? false : true;
    }
    
    /**
     * Http or https url string
     */
    public static function isHttpUrl($value)
    {
        return preg_match('/https?:\/\/[a-z0-9\-]+\.[a-z]+\/?.*/i', $value);
    }
    

    /**
     * regular expression
     */
    public static function isMatch($value, $regxp)
    {
        return preg_match($regxp, $value);
    }

    /**
     * if equal
     */
    public static function isEqual($value, $cmp)
    {
        return $value == $cmp && strlen($value) == strlen($cmp);
    }

    /**
     * if same
     */
    public static function isSame($value, $cmp)
    {
        return $value === $cmp;
    }
    
    /**
     * in array
     */
    public static function isInArray($value, $array)
    {
        return in_array($value, $array);
    }

    /**
     * length range
     */
    public static function lengthRange($value, $min, $max)
    {
        $len = mb_strlen($value, 'UTF-8');
        return $len >= $min && $len <= $max;
    }

    /**
     * value range
     */
    public static function valueRange($value, $min, $max)
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * is empty
     */
    public static function notEmpty($value)
    {
        return !empty($value);
    }
    
    /**
     * is null
     */
    public static function notNull($value)
    {
        return $value !== null;
    }


    /**
     * not empty recursively
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
     * the value type
     */
    public static function isType($value, $type)
    {
        return gettype($value) == $type;
    }

}