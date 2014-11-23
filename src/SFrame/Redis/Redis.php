<?php namespace SFrame\Redis;

/**
 * Redis
 * phpredis: https://github.com/nicolasff/phpredis
 * It can support master/slave mode through configuration
 */
class Redis
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 6379;
    const DEFAULT_PERSISTENT = false;
    const DEFAULT_TIMEOUT = 0;
    
    protected $_master = null;
    protected $_slave = null;
    
    protected $_config_master = array();
    protected $_config_slaves = array();
    
    
    /**
     * Create a new redis instance based on the given configuration
     * Based on the php redis extension
     * 
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('Redis extension not loaded.');
        }
        
        // config master
        $this->_config_master = empty($config['master']) ? $config : $config['master'];
        
        // config slave
        $slaves = empty($config['slaves']) ? array() : $config['slaves'];
        if (!empty($config['slave'])) {
            $slaves[] = $config['slave'];
        }
        $this->_config_slaves = $slaves;
    }
    
    
    /**
     * Set data (to master)
     * 
     * @param string $key The key
     * @param mixed $value The value (support array)
     */
    public function set($key, $value, $expire = null)
    {
        return $this->_master()->set($key, json_encode($value), $expire);
    }
    
    
    /**
     * Get data (from slave if setted)
     * 
     * @param string $key The key
     */
    public function get($key)
    {
        $value = $this->_slave()->get($key);
        return json_decode($value);
    }
    
    
    /**
     * Delete the key
     */
    public function delete($key)
    {
        return $this->_master()->delete($key);
    }
    
    
    /**
     * Others call the origin methods
     */
    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->_master(), $method), $args);
    }
    
    
    
    /**
     * Redis connect and return the redis instance
     * 
     * @param array $config The config
     * @return \Redis
     */
    protected function _connection(array $config)
    {
        $redis = new \Redis;
        $host = empty($config['host']) ? self::DEFAULT_HOST : $config['host'];
        $port = empty($config['port']) ? self::DEFAULT_PORT : $config['port'];
        $timeout = empty($config['timeout']) ? self::DEFAULT_TIMEOUT : $config['timeout'];
        $connect = empty($config['persistent']) ? 'connect' : 'pconnect';
        $redis->$connect($host, $port, $timeout);
        return $redis;
    }
    
    
    /**
     * Get the master connection
     * 
     * @return \Redis
     */
    protected function _master()
    {
        if (null === $this->_master) {
            $this->_master = $this->_connection($this->_config_master);
        }
        return $this->_master;
    }

    /**
     * Get the slave connection
     * 
     * @return \Redis
     */
    protected function _slave()
    {
        if (empty($this->_config_slaves)) {
            return $this->_master();
        }
        if (null === $this->_slave) {
            $count = count($this->_config_slaves);
            $index = mt_rand(0, $count-1);
            $config = $this->_config_slaves[$index];
            $this->_slave = $this->_connection($config);
        }
        return $this->_slave;
    }
}
