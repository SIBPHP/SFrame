# Memcache
Memcache manager based on php_memcache extension
Support multi memcache servers easier through configuration

# Installation
```php
composer require "sframe/memcache:*"
```

# Configuration
1 Server
```php
array(
    'host' => '',       // optional, default 127.0.0.1
    'port' => '',       // optional, default 11211
    'persistent' => false, // optional, default false
    'weight' => 1,      // optional, default 1
    'expire' => 86400,  // optional, default 86400 (1 day)
)
```

Multi Servers
```php
array(
    'servers' => array(
        array(...),
        array(...),
        ...
    ),
    'expire' => 86400
)
```


demo.php
```php
$mc = new \SFrame\Memcache\Memcache();
$mc->set('hello', 1);
$mc->get('hello');
$mc->delete('hello');
```