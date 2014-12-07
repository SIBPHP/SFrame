# Router
Simple router.
You can specify the route
Or if not specified it'll take controller/action as default
example: http://hello.com/controller/action?name=test

# Installation
```php
composer require "sframe/router:dev-master"
```


# Usage
```php
$route = new SFrame\Router\Route(__DIR__ .'/Controllers');

// groups
$route->group(['prefix'=>'v1', 'before'=>function(){
    echo 'hello';
}], function($route){
    $route->get('test', 'TestController@tt');
    $route->post('hello', 'TestController@hello');
});

$route->put('test', 'TestController@pp');
$route->delete('test', 'TestController@dd');

try {
    $route->dispatch();
} catch (SFrame\Router\Exception\NotFound $e) {
    redirect('/404.html');
}
```