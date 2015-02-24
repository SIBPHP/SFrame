<?php namespace SFrame\Config;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FileArray extends Container
{
    protected $_base_path = '';

    public function __construct($base_path)
    {
        if (!is_dir($base_path)) {
            throw new \FileNotFoundException($base_path .' is not exists.');
        }
        $this->_base_path = trim($base_path);
    }
    
    /**
     * Get the config
     * Config->get('app.test.aaa')
     */
    public function get($key, $default = null)
    {
        $poz = strpos($key, '.');
        $file_name = $poz ? substr($key, 0, $poz) : $key;
        $file_key = '_'. $file_name;
        if (!$this->has($file_key)) {
            $file = $this->_base_path .'/'. $file_name .'.php';
            if (!is_file($file)) {
                throw new \FileNotFoundException($file .' is not exists.');
            }
            $config = include $file;
            $this->set($file_key, $config);
        }
        return $this->get('_'.$key, $default);
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
        } catch (\FileNotFoundException $e) {
        }
        return $has;
    }
}
