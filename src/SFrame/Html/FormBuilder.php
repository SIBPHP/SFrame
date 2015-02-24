<?php namespace SFrame\Html;
/**
 * Form Builder
 */

class FormBuilder
{
    protected $_data = array();

    public function __construct($data = array())
    {
        if (!empty($data)) {
            $this->setData($data);
        }
    }

    /**
     * Set Form data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    public function render()
    {
        
    }

    /**
     * ===========================
     * From inputs
     * ===========================
     */
    
    public function text($name, $value = null, array $options = array())
    {
        echo '<input type="text" name="' . $name . '" id="' . $name . '" class="text" value="' . $this->_value($value) . '"' . $this->_options($options) . ' />';
    }

    public function hidden($name, $value = null, array $options = array())
    {
        echo '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $this->_value($value) . '"' . $this->_options($options) . ' />';
    }

    public function email($name, $value = null, array $options = array())
    {
        echo '<input type="email" name="' . $name . '" id="' . $name . '" class="text email" value="' . $this->_value($value) . '"' . $this->_options($options) . ' />';
    }

    public function password($name, $value = null, array $options = array())
    {
        echo '<input type="password" name="' . $name . '" id="' . $name . '" class="text password" value="' . $this->_value($value) . '"' . $this->_options($options) . ' />';
    }

    public function textarea($name, $value = null, array $options = array())
    {
        echo '<textarea name="' . $name . '" id="' . $name . '" class="textarea"' . $this->_options($options) . '>' . $this->_value($value) . '</textarea>';
    }

    public function select($name, $data, $value = null, array $options = array())
    {
        $str = '<select name="' . $name . '" id="' . $name . '" class="select"' . $this->_options($options) . '>';
        foreach ($data as $k => $v) {
            $selected = ($k == $this->_value($value)) ? ' selected' : '';
            $str .= '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
        }
        $str .= '</select>';
        return $str;
    }

    public function checkbox($name, $value = 1, $checked = false, array $options = array())
    {
        $checked = $checked ? ' checked' : '';
        echo '<input type="checkbox" name="' . $name . '[]" class="checkbox ' . $name . '" value="' . $value . '"' . $checked . $this->_options($options) . ' />';
    }

    public function radio($name, $value = null, $checked = false, array $options = array())
    {
        $checked = $checked ? ' checked' : '';
        echo '<input type="radio" name="' . $name . '" class="radio ' . $name . '" value="' . $this->_value($value) . '"' . $checked . $this->_options($options) . ' />';
    }

    public function checkboxGroup($name, $data, $checked_list = null, array $options = array())
    {
        $checked_list = $this->_value($checked_list);
        if (!is_array($checked_list)) {
            $checked_list = explode(',', $checked_list);
        }
        $str = '';
        foreach ($data as $k => $v) {
            $checked = in_array($k, $checked_list);
            $str .= '<label>' . $this->checkbox($name, $k, $checked, $options) . $v . '</label>';
        }
        return $str;
    }

    public function radioGroup($name, $data, $value = null, array $options = array())
    {
        $str = '';
        foreach ($data as $k => $v) {
            $checked = ($k == $this->_value($value)) ? ' checked' : '';
            $str .= '<label>' . $this->radio($name, $k, $checked, $options) . $v . '</label>';
        }
        return $str;
    }

    protected function _value($value)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } elseif ($value === null) {
            return '';
        } else {
            return $value;
        }
    }

    protected function _options($options)
    {
        $str = '';
        if (!empty($options)) {
            foreach ($options as $k => $v) {
                $str .= ' ' . $k . '="' . $v . '"';
            }
        }
        return $str;
    }

}
