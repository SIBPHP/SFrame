# Router
Simple session component


# Installation
```php
composer require "sframe/session:dev-master"
```

#Options
Same as original php session options
See:
http://php.net/manual/en/session.configuration.php


# Usage
```php
use \SFrame\Session\Session;
Session::start();
$_SESSION['test'] = 'hello';
$session_id = Session::getId();
Session::destroy();
```

Use redis storage
```php
$options = array();
$redis = new \SFrame\Redis\Redis();
Session::start($options, 'redis', $redis);
```

Use memcache storage
```php
$options = array();
$memcache = new \SFrame\Memcache\Memcache();
Session::start($options, 'memcache', $memcache);
```

