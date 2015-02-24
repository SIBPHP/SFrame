<?php namespace SFrame\Cache;

interface CacheInterface
{
    public function set($key, $value, $expire = null, $compressed = false);

    public function get($key, $default = null);

    public function del($key);
}
