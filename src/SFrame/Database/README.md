# Database
A simple database manager based on PDO, support master/slaves mode

# Installation
```php
composer require "sframe/database:dev-master"
```

# Configuration
One database
```php
array(
    'driver' => '',         // optional, default mysql
    'host' => '',           // optional, default 127.0.0.1
    'port' => '',           // optional, default PDO default port
    'charset' => 'utf8',    // optional, default utf8
    'persistent' => false,  // optional, default false
    'dbname' => '',         // required, the name of the database
    'username' => '',       // required, username of the database
    'password' => '',       // required, password of the database
)
```

1 master and 1 slave
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

1 master and multi slaves
```php
array(
    'master' => array(
        ...
    ),
    'slaves' => array(
        array(
            ...
        ),
        array(
            ...
        ),
        ...
    )
)
```



# Usage
demo.php
```php
$config = '...'; // load the config
$DB = new SFrame\Database\DB($config);
$sql = 'SELECT * FROM test LIMIT 10';
$data = $DB->fetchAll();
$DB->insert('test', ['a'=>'aa', 'b'=>'bb']);
```
