<?php namespace SFrame\Session\Storage;
use SFrame\Memcache\Memcache as MemcacheInstance;

/**
 * Save Handler Memcache
 * use SFrame\Memcache component
 */

class Memcache implements StorageInterface
{
    protected $_mc = null;
    protected $_lifetime = 1440;

    public function __construct(MemcacheInstance $memcache)
    {
        if (null === $this->_mc) {
            $this->_mc = $memcache;
        }
        $this->_lifetime = (int)ini_get('session.gc_maxlifetime');
    }

    public function open($save_path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        return $this->_mc->get($id);
    }

    public function write($id, $data)
    {
        $this->_mc->set($id, $data, $this->_lifetime);
        return true;
    }

    public function destroy($id)
    {
        $this->_mc->delete($id);
        return true;
    }

    public function gc($maxlifetime)
    {
        return true;
    }
}