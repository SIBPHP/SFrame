<?php namespace SFrame\View;

/**
 * View container
 */
class View
{

    protected $_view_path = '';
    protected $_vals = array();

    public function __construct($view_path)
    {
        $this->_view_path = $view_path;
    }

    /**
     * K/V values
     * @param array $vals
     */
    public function setVals($vals)
    {
        foreach ($vals as $k => $v) {
            $this->_vals[$k] = $v;
        }
    }

    /**
     * Get values
     */
    public function getVals()
    {
        return $this->_vals;
    }

    /**
     * Set value
     * Example: $view->test = 1;
     */
    public function __set($key, $val)
    {
        $this->_vals[$key] = $val;
    }

    /**
     * Get value
     * Example: echo $view->test;
     */
    public function __get($key)
    {
        return isset($this->_vals[$key]) ? $this->_vals[$key] : null;
    }

    /**
     * Check if key exists
     */
    public function __isset($key)
    {
        return isset($this->_vals[$key]);
    }

    /**
     * Render the template
     * @param string $template the template file
     * @param array $vals
     */
    public function render($template, $vals = array())
    {
        if (!empty($vals)) {
            $this->setVals($vals);
        }
        $template_file = $this->_view_path . '/' . $template . '.php';
        if (!is_file($template_file)) {
            throw new TemplateNotExistsException('The template "' . $template . '" is not exists.');
        }
        foreach ($this->getVals() as $key => $val) {
            eval("$\$key=\$val;");
        }
        include $template_file;
    }

    /**
     * Fetch view
     */
    public function fetch($template, $vals = array())
    {
        ob_start();
        $this->render($template, $vals);
        return ob_get_clean();
    }

    /**
     * Use blocks
     */
    public function blocks($template, $vals = array())
    {
        $this->render('blocks/' . $template, $vals);
    }

}
