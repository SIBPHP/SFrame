<?php
define('SF_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
define('D_TIMESTAMP', time());
define('D_DATETIME', date('Y-m-d H:i:s', D_TIMESTAMP));

require_once __DIR__ .'/helper.php';


// Autoload
require_once SF_ROOT .'/Framework/ClassLoader.php';
SFrame\Framework\ClassLoader::register();
SFrame\Framework\ClassLoader::addDirectories(array(
    dirname(SF_ROOT)
));
