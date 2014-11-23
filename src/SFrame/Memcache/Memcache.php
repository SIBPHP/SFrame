<?php namespace SFrame\Memcache;

/**
 * Memcache
 * Support multi memcache servers easier through configuration
 */
class Memcache
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 11211;
    const DEFAULT_PERSISTENT = false;
    const DEFAULT_WEIGHT = 1;
    const DEFAULT_EXPIRE = 86400;
    
    protected $_mc = null;
    protected $_expire = self::DEFAULT_EXPIRE;
    
    /**
     * Create a instance based on the given configuration
     * Based on php memcache extension
     * 
     * @param array $config the configuration
     */
    public function __construct(array $config = array())
    {
        if (!extension_loaded('memcache')) {
            throw new \BadFunctionCallException('Memcache extension not loaded');
        }
        
        $this->_mc = new \Memcache;
        
        // config
        if (empty($config['servers'])) {
            $servers = array(
                $config
            );
        } else {
            $servers = $config['servers'];
        }
        
        if (isset($config['expire'])) {
            $this->_expire = (int)$config['expire'];
        }
        
        foreach ($servers as $server) {
            $host = empty($server['host']) ? self::DEFAULT_HOST : $server['host'];
            $port = empty($server['port']) ? self::DEFAULT_PORT : $server['port'];
            $persistent = isset($server['persistent']) ? (bool)$server['persistent'] : self::DEFAULT_PERSISTENT;
            $weight = isset($server['weight']) ? (int)$server['weight'] : self::DEFAULT_WEIGHT;
            $this->_mc->addServer($host, $port, $persistent, $weight);
        }
    }
    
    /**
     * Set KV data (not compressed)
     * 
     * @param string $key the key
     * @param mixed $value the value
     * @param int $expire Expiration time of the item
     * @return bool
     */
    public function set($key, $value, $expire = null)
    {
        if (null === $expire) {
            $expire = $this->_expire;
        }
        return $this->_mc->set($key, $value, 0, $expire);
    }
    
    /**
     * Set KV data (compressed)
     */
    public function setCompressed($key, $value, $expire = null)
    {
        if (null === $expire) {
            $expire = $this->_expire;
        }
        return $this->_mc->set($key, $value, MEMCACHE_COMPRESSED, $expire);
    }
    
    
    /**
     * Retrieve one item
     * 
     * @param string $key
     * @return mixed the value
     */
    public function get($key)
    {
        return $this->_mc->get($key);
    }
    
    /**
     * Retrieve multi items
     * 
     * @param array $keys
     * @return array values
     */
    public function getMulti($keys)
    {
        return $this->_mc->get($keys);
    }
    
    
    /**
     * DElete a key
     * 
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return $this->_mc->delete($key);
    }
    
    
    /**
     * Use other methods of the origin memcache class
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->_mc, $method), $args);
    }
}
