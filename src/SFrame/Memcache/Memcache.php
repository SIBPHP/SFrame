<?php namespace SFrame\Memcache;

/**
 * Memcache
 *
 * @author fredyang<shuky2000@163.com>
 */
class Memcache
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 11211;
    const DEFAULT_PERSISTENT = false;
    const DEFAULT_WEIGHT = 0;
    const DEFAULT_EXPIRE = 1200;
    
    protected $_mc = null;
    protected $_config = array(
        'servers' => array(array(
                'host' => self::DEFAULT_HOST,
                'port' => self::DEFAULT_PORT,
                'persistent' => self::DEFAULT_PERSISTENT,
                'weight' => null
            )),
        'expire' => self::DEFAULT_EXPIRE
    );

    /**
     * Create a instance based on the given configuration
     * 
     * @param array $config the configuration
     */
    public function __construct(array $config = array())
    {
        if (!extension_loaded('memcache')) {
            throw new Exception\MemcacheExtensionNotFound();
        }
        $this->_mc = new \Memcache;
        $this->_config = array_merge($this->_config, $config);
        foreach ($this->_config['servers'] as $server) {
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
            $expire = $this->_config['expire'];
        }
        return $this->_mc->set($key, $value, 0, $expire);
    }
    
    /**
     * Set KV data (compressed)
     */
    public function setCompressed($key, $value, $expire = null)
    {
        if (null === $expire) {
            $expire = $this->_config['expire'];
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
