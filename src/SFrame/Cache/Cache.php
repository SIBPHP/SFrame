<?php namespace SFrame\Cache;

class Cache
{
    protected $_default_expire = 36000;
    /**
     * @var CacheInterface 
     */
    protected $_store = null;


    public function __construct(CacheInterface $store)
    {
        $this->_store = $store;
    }
    
    
    /**
     * 获取缓存
     * 如果缓存不存在，可以通过回调函数获取数据源设置到缓存里
     *
     * @param string $key
     * @param mixed $data_source 回调或默认
     * @param int $expire 设置缓存过期时间
     * @return mixed
     */
    public function get($key, $data_source = null, $expire = null, $compressed = false)
    {
        $key = strtolower($key);
        $data = $this->_store->get($key);
        if (null === $data) {
            $data = $this->set($key, $data_source, $expire, $compressed);
        }
        return $data;
    }

    /**
     * 设置缓存
     *
     * @param string $key
     * @param mixed $data_source 回调或默认
     * @param int $expire 设置缓存过期时间
     * @return mixed
     */
    public function set($key, $data_source, $expire = null, $compressed = false)
    {
        if (is_callable($data_source)) {
            $data = $data_source();
        } else {
            $data = $data_source;
        }
        if (!empty($data)) {
            $key = strtolower($key);
            $expire = $expire === null ? $this->_default_expire : $expire;
            $this->_store->set($key, $data, $expire, $compressed);
        }
        return $data;
    }

    /**
     * 删除缓存
     */
    public function del($key)
    {
        $this->_store->del($key);
    }

}
