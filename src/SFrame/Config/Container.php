<?php namespace SFrame\Config;

class Container
{
    protected $_configs = array();
    
    public function sets($configs = array())
    {
        $this->_configs = array_merge($this->_configs, $configs);
    }
    
    public function set($key, $config)
    {
        $this->_configs[$key] = $config;
    }
    
    public function get($key, $default = null)
    {
        if (strpos($key, '.')) {
            $config = $this->_configs;
            $ks = explode('.', $key);
            foreach ($ks as $k) {
                if (isset($config[$k])) {
                    $config = $config[$k];
                } else {
                    $config = $default;
                    break;
                }
            }
        } else {
            $config = isset($this->_configs[$key]) ? $this->_configs[$key] : $default;
        }
        
        return $config;
    }
    
    public function has($key)
    {
        return isset($this->_configs[$key]);
    }

}
