<?php namespace SFrame\Cache;

use SFrame\Redis\Redis as RedisObject;

class Redis implements CacheInterface
{
    public $_redis = null;
    
    public function __construct($redis)
    {
        if ($redis instanceof RedisObject) {
            $this->_redis = $redis;
        } else {
            $this->_redis = new RedisObject($redis);
        }
    }
    
    /**
     * Set
     */
    public function set($key, $value, $expire = null, $compressed = false)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        if ($compressed) {
            $value = gzcompress($value);
        }
        return $this->_redis->set($key, $value, $expire);
    }
    
    /**
     * Get
     */
    public function get($key, $default = null)
    {
        $value = $this->_redis->get($key);
        $value_de = @gzuncompress($value);
        if ($value_de !== false) {
            $value = $value_de;
        }
        $value_jd = json_decode($value, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            $value = $value_jd;
        }
        return $value;
    }
    
    /**
     * Delete
     */
    public function del($key)
    {
        $this->_redis->set($key, '');
        $this->_redis->delete($key);
    }
}
