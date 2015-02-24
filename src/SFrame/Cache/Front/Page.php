<?php namespace SFrame\Cache\Front;

use SFrame\Cache\CacheInterface;

class Page
{
    protected $_default_expire = 1200;
    protected $_store = null;
    
    
    public function __construct(CacheInterface $store)
    {
        $this->_store = $store;
    }
    
    public function start($index, $expire = null, $callback = null)
    {
        if (!$expire) {
            $expire = $this->_default_expire;
        }
        if (filter_input(INPUT_GET, 'cleancache') == 1) {
            $this->remove($index);
        }
        $content = $this->_store->get($index);
        if (!empty($content)) {
            header('Pragma: public');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT');
            header('Cache-Control: public, max-age=' . $expire);
            echo $content . '<!--cached-->';
            if ($callback) {
                $callback();
            }
            exit;
        }
        $self = $this;
        ob_start(function($content) use($index, $expire, $self) {
            if ($content) {
                $self->_store->set($index, $content, $expire);
            }
            return $content;
        });
        ob_implicit_flush(false);
    }

    
    public function remove($index)
    {
        $this->_store->del($index);
    }
}
