<?php namespace SFrame\Session;
/**
 * Session
 */

class Session
{
    /**
     * options
     * see: http://php.net/manual/en/session.configuration.php 
     */
    private static $_valid_options = array(
        'save_path', 'name', 'save_handler', 'gc_probability', 'gc_divisor', 'gc_maxlifetime', 'serialize_handler',
        'cookie_lifetime', 'cookie_path', 'cookie_domain', 'cookie_secure', 'cookie_httponly', 'use_cookies',
        'use_only_cookies', 'referer_check', 'entropy_file', 'entropy_length', 'cache_limiter', 'cache_expire', 'use_trans_sid'
    );
    
    // check if started
    protected static $_started = 0;

    /**
     * session start
     * @param array $options
     * @param string|Storage\StorageInterface $handler session save handler
     * @param mix $handler_options handler construct options
     */
    public static function start(array $options = array(), $handler = '', $handler_options = null)
    {
        if (self::$_started) {
            return;
        }
        if (!empty($options)) {
            self::setOptions($options);
        }
        if ($handler) {
            self::setSaveHandler($handler, $handler_options);
        }
        self::$_started = session_start();
    }
    
    
    /**
     * Set options
     * @param array $options
     */
    public static function setOptions(array $options)
    {
        foreach ($options as $k=>$v) {
            if (!in_array($k, self::$_valid_options)) {
                throw new \RuntimeException('Invalid session option:'. $k);
            }
            ini_set("session.$k", $v);
        }
    }
    
    
    /**
     * Set session save handler
     * buildin storage:
     * redis: use SFrame\Redis component
     * memcache: use SFrame\Memcache component
     * 
     * @param Storage\StorageInterface|string $handler
     */
    public static function setSaveHandler($handler, $options = null)
    {
        if (!$handler instanceof Storage\StorageInterface) {
            if (is_string($handler)) {
                $class_name = 'Storage\\'. ucfirst(strtolower($handler));
                $handler = new $class_name($options);
            } else {
                throw new \RuntimeException('Invalid handler');
            }
        }
        session_set_save_handler(
            array(&$handler, 'open'),
            array(&$handler, 'close'),
            array(&$handler, 'read'),
            array(&$handler, 'write'),
            array(&$handler, 'destroy'),
            array(&$handler, 'gc')
        );
    }
    
    
    /**
     * Get session id
     * @return string
     */
    public static function getId()
    {
        return session_id();
    }
    
    
    /**
     * Close session write
     */
    public static function close()
    {
        session_write_close();
    }
    
    
    /**
     * Session destroy
     */
    public static function destroy()
    {
        session_unset();
        session_destroy();
    }
}
