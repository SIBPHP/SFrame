<?php namespace SFrame\Cache;

use SFrame\Memcache\Memcache as MemcacheObject;

class Memcache implements CacheInterface
{
    /**
     * @var MemcacheObject
     */
    protected $_memcache = null;
    
    /**
     * @param mixed $memcache instance of MemcacheObject or Memcache config
     */
    public function __construct($memcache)
    {
        if ($memcache instanceof MemcacheObject) {
            $this->_memcache = $memcache;
        } else {
            $this->_memcache = new MemcacheObject($memcache);
        }
    }
    
    /**
     * Set
     */
    public function set($key, $value, $expire = null, $compressed = false)
    {
        if ($compressed) {
            return $this->_memcache->setCompressed($key, $value, $expire);
        } else {
            return $this->_memcache->set($key, $value, $expire);
        }
    }
    
    /**
     * Get
     */
    public function get($key, $default = null)
    {
        $value = $this->_memcache->get($key);
        if (empty($value)) {
            $value = $default;
        }
        return $value;
    }
    
    /**
     * Delete
     */
    public function del($key)
    {
        $this->_memcache->set($key, '');
        $this->_memcache->delete($key);
    }
}
