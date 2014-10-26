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
    /**
     * Config example:
     * 
     * One server:
     * array(
     *  host => '',
     *  ...
     * )
     * 
     * Multi servers:
     * write to master, read from slaves
     * 
     * (1 master and 1 slave)
     * array(
     *  master => array(
     *      host => '',
     *      ....
     *  ),
     *  slave => array(
     *      host => '',
     *      ...
     *  )
     * )
     * 
     * (1 master and multi slaves)
     * array(
     *  master => array(
     *      host => '',
     *      ....
     *  ),
     *  slaves => array(
     *      array(
     *          host => '',
     *          ...
     *      ),
     *      array(
     *          host => '',
     *          ...
     *      ),
     *      ...
     *  )
     * )
     */
    protected $_config = array(
        'host' => self::DEFAULT_HOST,
        'port' => self::DEFAULT_PORT,
        'persistent' => self::DEFAULT_PERSISTENT,
        'timeout' => self::DEFAULT_TIMEOUT
    );
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
            throw new Exception\RedisExtensionNotExists;
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
     * Redis connect and return the redis instance
     * 
     * @param array $config The config
     * @return \Redis
     */
    public function getConnection(array $config)
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
    public function getMaster()
    {
        if (null === $this->_master) {
            $this->_master = $this->getConnection($this->_config_master);
        }
        return $this->_master;
    }

    /**
     * Get the slave connection
     * 
     * @return \Redis
     */
    public function getSlave()
    {
        if (empty($this->_config_slaves)) {
            return $this->getMaster();
        }
        if (null === $this->_slave) {
            $count = count($this->_config_slaves);
            $index = mt_rand(0, $count-1);
            $config = $this->_config_slaves[$index];
            $this->_slave = $this->getConnection($config);
        }
        return $this->_slave;
    }
    
    
    /**
     * Set data (to master)
     * 
     * @param string $key The key
     * @param mixed $value The value (support array)
     */
    public function set($key, $value, $expire = null)
    {
        return $this->getMaster()->set($key, json_encode($value), $expire);
    }
    
    
    /**
     * Get data (from slave if setted)
     * 
     * @param string $key The key
     */
    public function get($key)
    {
        $value = $this->getSlave()->get($key);
        return json_decode($value);
    }
    
    
    /**
     * Delete the key
     */
    public function delete($key)
    {
        return $this->getMaster()->delete($key);
    }
    
    
    /**
     * Others call the origin methods
     */
    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->getMaster(), $method), $args);
    }
}
