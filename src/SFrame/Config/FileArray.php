<?php namespace SFrame\Config;

use Illuminate\Filesystem\FileNotFoundException;

class FileArray
{
    protected $_base_path = '';
    protected $_data = array();


    public function __construct($base_path)
    {
        if (!is_dir($base_path)) {
            throw new FileNotFoundException($base_path .' is not exists.');
        }
        $this->_base_path = trim($base_path);
    }
    
    /**
     * Get the config
     * Config->get('app.test.aaa')
     */
    public function get($key, $default = null)
    {
        if (strpos($key, '.')) {
            $ks = explode('.', $key);
            $config = $this->_load($ks[0]);
            foreach ($ks as $k) {
                if (isset($config[$k])) {
                    $config = $config[$k];
                } else {
                    $config = $default;
                    break;
                }
            }
        } else {
            $config = $this->_load($key);
        }
        return $config;
    }
    
    
    /**
     * 
     * @param type $key
     */
    public function has($key)
    {
        $has = false;
        try {
            if (null !== $this->get($key)) {
                $has = true;
            }
        } catch (FileNotFoundException $e) {
        }
        return $has;
    }
    
    
    /**
     * Load the config file
     * 
     * @param string $file_name the config file name
     * @return mixed the config
     * @throws Exception\FileNotFound
     */
    protected function _load($file_name)
    {
        if (!isset($this->_data[$file_name])) {
            $file = $this->_base_path .'/'. $file_name .'.php';
            if (!is_file($file)) {
                throw new FileNotFoundException($file .' is not exists.');
            }
            $this->_data[$file_name] = include $file;
        }
        return $this->_data[$file_name];
    }
}
