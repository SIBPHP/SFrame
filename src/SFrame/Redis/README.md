# Redis
Redis manager based on php_redis extension
Support master/slave mode through configuration

# Installation
```php
composer require "sframe/redis:*"
```

# Configuration
1 Server
```php
array(
    'host' => '',       // optional, default 127.0.0.1
    'port' => '',       // optional, default 6379
    'timeout' => 0,     // optional, default 0
    'persistent' => false, // optional, default false
)
```

1 Master and 1 Slave
```php
array(
    'master' => array(
        ...
    ),
    'slave' => array(
        ...
    )
)
```

1 Master and Multi slaves
```php
array(
    'master' => array(
        ...
    ),
    'slaves' => array(
        array(...),
        array(...),
        ...
    )
)
```



demo.php
```php
$redis = new \SFrame\Redis\Redis();
$redis->set('hello', 1);
$redis->get('hello');
$redis->delete('hello');
...
```