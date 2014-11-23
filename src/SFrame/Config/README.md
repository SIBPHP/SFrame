# Config
A simple way to load array config in file.

# Installation
```php
composer require "sframe/config:*"
```

# Usage

config_path/test.php
```php
return array(
    'a' => 'aaa',
    'b' => array(
        'b1' => '111',
        'b2' => '222'
    )
);
```
config_path/tt.php
```php
return array(
    'hello' => 'hhhhh'
);
```


demo.php
```php
$config = new SFrame\Config\FileArray('config_path');
$a = $config->get('test.a');
$b1 = $config->get('test.b.b1');
$hl = $config->get('tt.hello', 'default');
$is_exists = $config->has('test.d');
```